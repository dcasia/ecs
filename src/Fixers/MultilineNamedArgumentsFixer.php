<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use SplFileInfo;
use Throwable;

final class MultilineNamedArgumentsFixer extends AbstractFixer
{
    /**
     * @var list<array{
     *     start: int,
     *     end: int,
     *     name: string,
     *     classImports: array<string, string>,
     *     functionImports: array<string, string>
     * }>
     */
    private array $namespaceContexts = [];

    /**
     * @var list<array{
     *     start: int,
     *     end: int,
     *     shortName: string|null,
     *     name: string|null,
     *     rawParent: string|null,
     *     parent: string|null,
     *     methods: array<string, list<array{name: string, variadic: bool}>>,
     *     methodReturnTypes: array<string, string|null>
     * }>
     */
    private array $classes = [];

    /**
     * @var array<string, list<array{name: string, variadic: bool}>>
     */
    private array $functions = [];

    /**
     * @var array<string, array{type: string|null, position: int}>
     */
    private array $functionReturnTypes = [];

    /**
     * @var list<array{start: int, end: int, openParenthesis: int, closeParenthesis: int}>
     */
    private array $callableScopes = [];

    /**
     * @var list<array{start: int, end: int}>
     */
    private array $curlyScopes = [];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            summary: 'Arguments in expanded multiline calls are named when there are at least two arguments and the callable parameter names can be resolved safely.',
            codeSamples: [
                new CodeSample("<?php\n\njson_decode(\n    \$json,\n    true,\n    flags: JSON_THROW_ON_ERROR,\n);\n"),
            ],
        );
    }

    public function getPriority(): int
    {
        return -50;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([
            T_STRING,
            T_NAME_QUALIFIED,
            T_NAME_FULLY_QUALIFIED,
            T_NAME_RELATIVE,
        ]);
    }

    protected function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $this->namespaceContexts = $this->collectNamespaceContexts($tokens);
        $this->classes = $this->collectClassScopes($tokens);
        $this->functions = [];
        $this->functionReturnTypes = [];
        $this->callableScopes = $this->collectCallableScopes($tokens);
        $this->curlyScopes = $this->collectCurlyScopes($tokens);

        $this->collectImports($tokens);
        $this->resolveClassNames();
        $this->collectCallableDeclarations($tokens);

        $openParentheses = [];

        for ($index = 0; $index < $tokens->count(); $index++) {

            if ($tokens[ $index ]->equals('(')) {
                $openParentheses[] = $index;
            }

        }

        for ($index = count($openParentheses) - 1; $index >= 0; $index--) {

            $openParenthesis = $openParentheses[ $index ];
            $closeParenthesis = $tokens->findBlockEnd(
                type: Tokens::BLOCK_TYPE_PARENTHESIS_BRACE,
                searchIndex: $openParenthesis,
            );

            if ($tokens->isPartialCodeMultiline($openParenthesis, $closeParenthesis) === false) {
                continue;
            }

            $arguments = $this->inspectArguments($tokens, $openParenthesis, $closeParenthesis);

            if ($this->hasExpandedArgumentList($tokens, $openParenthesis, $arguments) === false
                || $this->hasPositionalArgument($arguments) === false) {
                continue;
            }

            $parameters = $this->resolveCallParameters($tokens, $openParenthesis);

            if ($parameters === null) {
                continue;
            }

            $names = $this->matchArgumentNames($arguments, $parameters);

            if ($names === null) {
                continue;
            }

            for ($nameIndex = count($names) - 1; $nameIndex >= 0; $nameIndex--) {

                $name = $names[ $nameIndex ];

                $tokens->insertAt($name[ 'index' ], [
                    new Token([ T_STRING, $name[ 'name' ] ]),
                    new Token(':'),
                    new Token([ T_WHITESPACE, ' ' ]),
                ]);

            }

        }
    }

    /**
     * @return list<array{
     *     start: int,
     *     end: int,
     *     name: string,
     *     classImports: array<string, string>,
     *     functionImports: array<string, string>
     * }>
     */
    private function collectNamespaceContexts(Tokens $tokens): array
    {
        $namespaceIndexes = [];

        for ($index = 0; $index < $tokens->count(); $index++) {

            if ($tokens[ $index ]->isGivenKind(T_NAMESPACE)) {
                $namespaceIndexes[] = $index;
            }

        }

        if ($namespaceIndexes === []) {

            return [ [
                'start' => 0,
                'end' => $tokens->count() - 1,
                'name' => '',
                'classImports' => [],
                'functionImports' => [],
            ] ];

        }

        $contexts = [];

        foreach ($namespaceIndexes as $offset => $namespaceIndex) {

            $delimiter = $tokens->getNextTokenOfKind($namespaceIndex, [ ';', '{' ]);

            if ($delimiter === null) {
                continue;
            }

            $name = '';

            for ($index = $namespaceIndex + 1; $index < $delimiter; $index++) {

                if ($tokens[ $index ]->isWhitespace() === false && $tokens[ $index ]->isComment() === false) {
                    $name = sprintf('%s%s', $name, $tokens[ $index ]->getContent());
                }

            }

            if ($tokens[ $delimiter ]->equals('{')) {

                $end = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $delimiter) - 1;

            } else {

                $nextNamespace = $namespaceIndexes[ $offset + 1 ] ?? $tokens->count();
                $end = $nextNamespace - 1;

            }

            $contexts[] = [
                'start' => $delimiter + 1,
                'end' => $end,
                'name' => ltrim($name, '\\'),
                'classImports' => [],
                'functionImports' => [],
            ];

        }

        return $contexts;
    }

    /**
     * @return list<array{
     *     start: int,
     *     end: int,
     *     shortName: string|null,
     *     name: string|null,
     *     rawParent: string|null,
     *     parent: string|null,
     *     methods: array<string, list<array{name: string, variadic: bool}>>,
     *     methodReturnTypes: array<string, string|null>
     * }>
     */
    private function collectClassScopes(Tokens $tokens): array
    {
        $classes = [];

        for ($index = 0; $index < $tokens->count(); $index++) {

            if ($tokens[ $index ]->isGivenKind([ T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM ]) === false) {
                continue;
            }

            $previous = $tokens->getPrevMeaningfulToken($index);

            if ($previous !== null && $tokens[ $previous ]->isGivenKind(T_DOUBLE_COLON)) {
                continue;
            }

            $openBrace = $tokens->getNextTokenOfKind($index, [ '{' ]);

            if ($openBrace === null) {
                continue;
            }

            $nameIndex = $tokens->getNextMeaningfulToken($index);
            $shortName = $nameIndex !== null && $tokens[ $nameIndex ]->isGivenKind(T_STRING)
                ? $tokens[ $nameIndex ]->getContent()
                : null;

            $rawParent = null;

            for ($headerIndex = $index + 1; $headerIndex < $openBrace; $headerIndex++) {

                if ($tokens[ $headerIndex ]->isGivenKind(T_EXTENDS) === false) {
                    continue;
                }

                $parentIndex = $tokens->getNextMeaningfulToken($headerIndex);

                if ($parentIndex !== null) {
                    $rawParent = $this->readQualifiedNameStartingAt($tokens, $parentIndex);
                }

                break;

            }

            $classes[] = [
                'start' => $openBrace,
                'end' => $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $openBrace),
                'shortName' => $shortName,
                'name' => null,
                'rawParent' => $rawParent,
                'parent' => null,
                'methods' => [],
                'methodReturnTypes' => [],
            ];

        }

        return $classes;
    }

    private function collectImports(Tokens $tokens): void
    {
        for ($index = 0; $index < $tokens->count(); $index++) {

            if ($tokens[ $index ]->isGivenKind(T_USE) === false || $this->findContainingClass($index) !== null) {
                continue;
            }

            $previous = $tokens->getPrevMeaningfulToken($index);

            if ($previous !== null && $tokens[ $previous ]->equals(')')) {
                continue;
            }

            $contextIndex = $this->findNamespaceContext($index);
            $semicolon = $tokens->getNextTokenOfKind($index, [ ';' ]);

            if ($contextIndex === null || $semicolon === null) {
                continue;
            }

            $statement = '';

            for ($statementIndex = $index + 1; $statementIndex < $semicolon; $statementIndex++) {
                $statement = sprintf('%s%s', $statement, $tokens[ $statementIndex ]->getContent());
            }

            foreach ($this->parseUseStatement($statement) as $import) {

                if ($import[ 'kind' ] === 'function') {

                    $this->namespaceContexts[ $contextIndex ][ 'functionImports' ][ strtolower($import[ 'alias' ]) ] = $import[ 'name' ];

                } else {

                    $this->namespaceContexts[ $contextIndex ][ 'classImports' ][ strtolower($import[ 'alias' ]) ] = $import[ 'name' ];

                }

            }

            $index = $semicolon;

        }
    }

    /**
     * @return list<array{kind: 'class'|'function', name: string, alias: string}>
     */
    private function parseUseStatement(string $statement): array
    {
        $statement = trim($statement);
        $defaultKind = 'class';

        if (preg_match('/^function\s+/i', $statement) === 1) {

            $defaultKind = 'function';
            $statement = preg_replace('/^function\s+/i', '', $statement) ?? $statement;

        } elseif (preg_match('/^const\s+/i', $statement) === 1) {

            return [];

        }

        $prefix = '';

        if (preg_match('/^(.*)\\\\\{(.*)}$/s', $statement, $matches) === 1) {

            $prefix = sprintf('%s\\', rtrim(trim($matches[ 1 ]), '\\'));
            $statement = $matches[ 2 ];

        }

        $imports = [];

        foreach (explode(',', $statement) as $entry) {

            $entry = trim($entry);
            $kind = $defaultKind;

            if (preg_match('/^function\s+/i', $entry) === 1) {

                $kind = 'function';
                $entry = preg_replace('/^function\s+/i', '', $entry) ?? $entry;

            } elseif (preg_match('/^const\s+/i', $entry) === 1) {

                continue;

            }

            $parts = preg_split('/\s+as\s+/i', $entry, 2);

            if ($parts === false || $parts === []) {
                continue;
            }

            $name = ltrim(sprintf('%s%s', $prefix, trim($parts[ 0 ])), '\\');
            $alias = $parts[ 1 ] ?? basename(str_replace('\\', '/', $name));

            if ($name === '' || $alias === '') {
                continue;
            }

            $imports[] = [
                'kind' => $kind,
                'name' => $name,
                'alias' => trim($alias),
            ];

        }

        return $imports;
    }

    private function resolveClassNames(): void
    {
        foreach ($this->classes as $index => $class) {

            $contextIndex = $this->findNamespaceContext($class[ 'start' ]);
            $namespace = $contextIndex === null ? '' : $this->namespaceContexts[ $contextIndex ][ 'name' ];

            if ($class[ 'shortName' ] !== null) {
                $this->classes[ $index ][ 'name' ] = $this->qualifyName($namespace, $class[ 'shortName' ]);
            }

            if ($class[ 'rawParent' ] !== null) {
                $this->classes[ $index ][ 'parent' ] = $this->resolveClassIdentifier($class[ 'rawParent' ], $class[ 'start' ]);
            }

        }
    }

    private function collectCallableDeclarations(Tokens $tokens): void
    {
        for ($index = 0; $index < $tokens->count(); $index++) {

            if ($tokens[ $index ]->isGivenKind(T_FUNCTION) === false) {
                continue;
            }

            $nameIndex = $this->findFunctionName($tokens, $index);

            if ($nameIndex === null) {
                continue;
            }

            $openParenthesis = $tokens->getNextTokenOfKind($nameIndex, [ '(' ]);

            if ($openParenthesis === null) {
                continue;
            }

            $closeParenthesis = $tokens->findBlockEnd(
                type: Tokens::BLOCK_TYPE_PARENTHESIS_BRACE,
                searchIndex: $openParenthesis,
            );

            $parameters = $this->readDeclaredParameters(
                tokens: $tokens,
                openParenthesis: $openParenthesis,
                closeParenthesis: $closeParenthesis,
            );

            $returnType = $this->readDeclaredReturnType($tokens, $closeParenthesis);

            $name = strtolower($tokens[ $nameIndex ]->getContent());
            $classIndex = $this->findContainingClass($index);

            if ($classIndex !== null) {

                $this->classes[ $classIndex ][ 'methods' ][ $name ] = $parameters;
                $this->classes[ $classIndex ][ 'methodReturnTypes' ][ $name ] = $returnType;

                continue;

            }

            $contextIndex = $this->findNamespaceContext($index);
            $namespace = $contextIndex === null ? '' : $this->namespaceContexts[ $contextIndex ][ 'name' ];
            $functionName = strtolower($this->qualifyName($namespace, $tokens[ $nameIndex ]->getContent()));
            $this->functions[ $functionName ] = $parameters;
            $this->functionReturnTypes[ $functionName ] = [
                'type' => $returnType,
                'position' => $index,
            ];

        }
    }

    /**
     * @return list<array{start: int, end: int, openParenthesis: int, closeParenthesis: int}>
     */
    private function collectCallableScopes(Tokens $tokens): array
    {
        $scopes = [];

        for ($index = 0; $index < $tokens->count(); $index++) {

            if ($tokens[ $index ]->isGivenKind(T_FUNCTION) === false) {
                continue;
            }

            $openParenthesis = $tokens->getNextTokenOfKind($index, [ '(' ]);

            if ($openParenthesis === null) {
                continue;
            }

            $closeParenthesis = $tokens->findBlockEnd(
                type: Tokens::BLOCK_TYPE_PARENTHESIS_BRACE,
                searchIndex: $openParenthesis,
            );

            $bodyStart = $tokens->getNextTokenOfKind($closeParenthesis, [ '{', ';' ]);

            if ($bodyStart === null || $tokens[ $bodyStart ]->equals('{') === false) {
                continue;
            }

            $scopes[] = [
                'start' => $bodyStart,
                'end' => $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $bodyStart),
                'openParenthesis' => $openParenthesis,
                'closeParenthesis' => $closeParenthesis,
            ];

        }

        return $scopes;
    }

    /**
     * @return list<array{start: int, end: int}>
     */
    private function collectCurlyScopes(Tokens $tokens): array
    {
        $scopes = [];

        for ($index = 0; $index < $tokens->count(); $index++) {

            if ($tokens[ $index ]->equals('{') === false) {
                continue;
            }

            $scopes[] = [
                'start' => $index,
                'end' => $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $index),
            ];

        }

        return $scopes;
    }

    private function findFunctionName(Tokens $tokens, int $functionIndex): ?int
    {
        $nameIndex = $tokens->getNextMeaningfulToken($functionIndex);

        if ($nameIndex !== null && $tokens[ $nameIndex ]->equals('&')) {
            $nameIndex = $tokens->getNextMeaningfulToken($nameIndex);
        }

        if ($nameIndex === null || $tokens[ $nameIndex ]->isGivenKind(T_STRING) === false) {
            return null;
        }

        return $nameIndex;
    }

    /**
     * @return list<array{name: string, variadic: bool}>
     */
    private function readDeclaredParameters(Tokens $tokens, int $openParenthesis, int $closeParenthesis): array
    {
        $parameters = [];

        foreach ($this->argumentRanges($tokens, $openParenthesis, $closeParenthesis) as $range) {

            for ($index = $range[ 'start' ]; $index <= $range[ 'end' ]; $index++) {

                if ($tokens[ $index ]->isGivenKind(T_VARIABLE) === false) {
                    continue;
                }

                $variadic = false;

                for ($prefixIndex = $range[ 'start' ]; $prefixIndex < $index; $prefixIndex++) {

                    if ($tokens[ $prefixIndex ]->isGivenKind(T_ELLIPSIS)) {

                        $variadic = true;
                        break;

                    }

                }

                $parameters[] = [
                    'name' => ltrim($tokens[ $index ]->getContent(), '$'),
                    'variadic' => $variadic,
                ];

                break;

            }

        }

        return $parameters;
    }

    private function readDeclaredReturnType(Tokens $tokens, int $closeParenthesis): ?string
    {
        $colon = $tokens->getNextMeaningfulToken($closeParenthesis);

        if ($colon === null || $tokens[ $colon ]->getContent() !== ':') {
            return null;
        }

        $typeIndex = $tokens->getNextMeaningfulToken($colon);

        if ($typeIndex !== null && $tokens[ $typeIndex ]->getContent() === '?') {
            $typeIndex = $tokens->getNextMeaningfulToken($typeIndex);
        }

        if ($typeIndex === null || $this->isNameToken($tokens[ $typeIndex ]) === false) {
            return null;
        }

        $afterType = $tokens->getNextMeaningfulToken($typeIndex);

        if ($afterType !== null && in_array($tokens[ $afterType ]->getContent(), [ '|', '&' ], true)) {
            return null;
        }

        return $tokens[ $typeIndex ]->getContent();
    }

    /**
     * @return list<array{start: int, end: int, name: string|null, unpacked: bool}>
     */
    private function inspectArguments(Tokens $tokens, int $openParenthesis, int $closeParenthesis): array
    {
        $arguments = [];

        foreach ($this->argumentRanges($tokens, $openParenthesis, $closeParenthesis) as $range) {

            $start = $tokens->getNextMeaningfulToken($range[ 'start' ] - 1);

            if ($start === null || $start > $range[ 'end' ]) {
                continue;
            }

            $next = $tokens->getNextMeaningfulToken($start);
            $name = $next !== null
                && $next <= $range[ 'end' ]
                && $tokens[ $next ]->getContent() === ':'
                    ? $tokens[ $start ]->getContent()
                    : null;

            $arguments[] = [
                'start' => $start,
                'end' => $range[ 'end' ],
                'name' => $name,
                'unpacked' => $tokens[ $start ]->isGivenKind(T_ELLIPSIS),
            ];

        }

        return $arguments;
    }

    /**
     * @return list<array{start: int, end: int}>
     */
    private function argumentRanges(Tokens $tokens, int $openParenthesis, int $closeParenthesis): array
    {
        $ranges = [];
        $start = $openParenthesis + 1;

        foreach ($this->findTopLevelCommas($tokens, $openParenthesis, $closeParenthesis) as $comma) {

            if ($tokens->getNextMeaningfulToken($start - 1) !== $comma) {
                $ranges[] = [ 'start' => $start, 'end' => $comma - 1 ];
            }

            $start = $comma + 1;

        }

        $first = $tokens->getNextMeaningfulToken($start - 1);

        if ($first !== null && $first < $closeParenthesis) {
            $ranges[] = [ 'start' => $start, 'end' => $closeParenthesis - 1 ];
        }

        return $ranges;
    }

    /**
     * @return list<int>
     */
    private function findTopLevelCommas(Tokens $tokens, int $openParenthesis, int $closeParenthesis): array
    {
        $commas = [];

        for ($index = $openParenthesis + 1; $index < $closeParenthesis; $index++) {

            $token = $tokens[ $index ];
            $block = Tokens::detectBlockType($token);

            if ($block !== null && $block[ 'isStart' ]) {

                $index = $tokens->findBlockEnd($block[ 'type' ], $index);

                continue;

            }

            if ($token->equals(',')) {
                $commas[] = $index;
            }

        }

        return $commas;
    }

    /**
     * @param list<array{start: int, end: int, name: string|null, unpacked: bool}> $arguments
     */
    private function hasExpandedArgumentList(Tokens $tokens, int $openParenthesis, array $arguments): bool
    {
        if (count($arguments) < 2) {
            return false;
        }

        return $tokens->isPartialCodeMultiline(
            start: $openParenthesis,
            end: $arguments[ 0 ][ 'start' ] - 1,
        );
    }

    /**
     * @param list<array{start: int, end: int, name: string|null, unpacked: bool}> $arguments
     */
    private function hasPositionalArgument(array $arguments): bool
    {
        foreach ($arguments as $argument) {

            if ($argument[ 'name' ] === null) {
                return true;
            }

        }

        return false;
    }

    /**
     * @param list<array{start: int, end: int, name: string|null, unpacked: bool}> $arguments
     * @param list<array{name: string, variadic: bool}> $parameters
     *
     * @return list<array{index: int, name: string}>|null
     */
    private function matchArgumentNames(array $arguments, array $parameters): ?array
    {
        $names = [];
        $usedNames = [];
        $position = 0;
        $encounteredNamedArgument = false;

        foreach ($arguments as $argument) {

            if ($argument[ 'name' ] !== null) {

                $encounteredNamedArgument = true;
                $usedNames[ $argument[ 'name' ] ] = true;

                continue;

            }

            if ($encounteredNamedArgument
                || $argument[ 'unpacked' ]
                || isset($parameters[ $position ]) === false
                || $parameters[ $position ][ 'variadic' ]) {
                return null;
            }

            $parameterName = $parameters[ $position ][ 'name' ];

            if (isset($usedNames[ $parameterName ])) {
                return null;
            }

            $usedNames[ $parameterName ] = true;
            $names[] = [
                'index' => $argument[ 'start' ],
                'name' => $parameterName,
            ];

            $position++;

        }

        return $names;
    }

    /**
     * @return list<array{name: string, variadic: bool}>|null
     */
    private function resolveCallParameters(Tokens $tokens, int $openParenthesis): ?array
    {
        $nameIndex = $tokens->getPrevMeaningfulToken($openParenthesis);
        $classIndex = $this->findContainingClass($openParenthesis);

        if ($nameIndex === null) {
            return null;
        }

        if ($tokens[ $nameIndex ]->isGivenKind(T_VARIABLE)) {

            $className = $this->resolveReceiverClass(
                tokens: $tokens,
                receiver: $nameIndex,
                position: $nameIndex,
                classIndex: $classIndex,
            );

            return $className === null ? null : $this->resolveMethod($className, '__invoke');

        }

        if ($this->isNameToken($tokens[ $nameIndex ]) === false) {
            return null;
        }

        $callableName = $this->readQualifiedNameEndingAt($tokens, $nameIndex);
        $name = $callableName[ 'name' ];
        $beforeName = $tokens->getPrevMeaningfulToken($callableName[ 'start' ]);

        if ($beforeName !== null && $tokens[ $beforeName ]->isGivenKind([ T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR ])) {

            $receiver = $tokens->getPrevMeaningfulToken($beforeName);

            return $receiver === null
                ? null
                : $this->resolveObjectMethod($tokens, $receiver, $openParenthesis, $classIndex, $name);

        }

        if ($beforeName !== null && $tokens[ $beforeName ]->isGivenKind(T_DOUBLE_COLON)) {

            $classNameIndex = $tokens->getPrevMeaningfulToken($beforeName);

            if ($classNameIndex === null || $this->isNameToken($tokens[ $classNameIndex ]) === false) {
                return null;
            }

            $classIdentifier = $this->readQualifiedNameEndingAt($tokens, $classNameIndex)[ 'name' ];
            $normalizedIdentifier = strtolower($classIdentifier);

            if (($normalizedIdentifier === 'self' || $normalizedIdentifier === 'static') && $classIndex !== null) {
                return $this->resolveSourceMethod($classIndex, $name);
            }

            if ($normalizedIdentifier === 'parent' && $classIndex !== null) {

                $parent = $this->classes[ $classIndex ][ 'parent' ];

                return $parent === null ? null : $this->resolveMethod($parent, $name);

            }

            $className = $this->resolveClassIdentifier($classIdentifier, $openParenthesis);

            return $className === null ? null : $this->resolveMethod($className, $name);

        }

        if ($beforeName !== null && $tokens[ $beforeName ]->isGivenKind(T_NEW)) {

            if (in_array(strtolower($name), [ 'self', 'static' ], true) && $classIndex !== null) {
                return $this->resolveSourceMethod($classIndex, '__construct');
            }

            if (strtolower($name) === 'parent' && $classIndex !== null) {

                $parent = $this->classes[ $classIndex ][ 'parent' ];

                return $parent === null ? null : $this->resolveConstructor($parent);

            }

            $className = $this->resolveClassIdentifier($name, $openParenthesis);

            return $className === null ? null : $this->resolveConstructor($className);

        }

        if ($beforeName !== null && $tokens[ $beforeName ]->isGivenKind([ T_FUNCTION, T_FN ])) {
            return null;
        }

        return $this->resolveFunction($name, $openParenthesis);
    }

    /**
     * @return list<array{name: string, variadic: bool}>|null
     */
    private function resolveObjectMethod(Tokens $tokens, int $receiver, int $position, ?int $classIndex, string $method): ?array
    {
        if ($tokens[ $receiver ]->isGivenKind(T_VARIABLE)
            && $tokens[ $receiver ]->getContent() === '$this'
            && $classIndex !== null) {
            return $this->resolveSourceMethod($classIndex, $method);
        }

        $className = $this->resolveReceiverClass($tokens, $receiver, $position, $classIndex);

        return $className === null ? null : $this->resolveMethod($className, $method);
    }

    private function resolveReceiverClass(Tokens $tokens, int $receiver, int $position, ?int $classIndex): ?string
    {
        if ($tokens[ $receiver ]->isGivenKind(T_VARIABLE)) {

            if ($tokens[ $receiver ]->getContent() === '$this') {
                return $classIndex === null ? null : $this->classes[ $classIndex ][ 'name' ];
            }

            $staticOperator = $tokens->getPrevMeaningfulToken($receiver);

            if ($staticOperator !== null && $tokens[ $staticOperator ]->isGivenKind(T_DOUBLE_COLON)) {

                $ownerIndex = $tokens->getPrevMeaningfulToken($staticOperator);

                if ($ownerIndex === null || $this->isNameToken($tokens[ $ownerIndex ]) === false) {
                    return null;
                }

                $owner = $this->readQualifiedNameEndingAt($tokens, $ownerIndex);
                $ownerClass = $this->resolveClassReference($owner[ 'name' ], $receiver, $classIndex);

                return $ownerClass === null
                    ? null
                    : $this->resolvePropertyClass($tokens, $ownerClass, ltrim($tokens[ $receiver ]->getContent(), '$'));

            }

            $assignment = $this->resolveLocalVariableClass(
                tokens: $tokens,
                variableIndex: $receiver,
                classIndex: $classIndex,
            );

            if ($assignment[ 'found' ]) {
                return $assignment[ 'class' ];
            }

            return $this->resolveSourceParameterClass(
                tokens: $tokens,
                position: $receiver,
                variable: $tokens[ $receiver ]->getContent(),
                classIndex: $classIndex,
            );

        }

        if ($this->isNameToken($tokens[ $receiver ])) {
            return $this->resolvePropertyReceiverClass($tokens, $receiver, $classIndex);
        }

        if ($tokens[ $receiver ]->equals(')') === false) {
            return null;
        }

        $callParenthesis = $tokens->findBlockStart(
            type: Tokens::BLOCK_TYPE_PARENTHESIS_BRACE,
            searchIndex: $receiver,
        );

        $nameIndex = $tokens->getPrevMeaningfulToken($callParenthesis);

        if ($nameIndex === null || $this->isNameToken($tokens[ $nameIndex ]) === false) {

            $innerExpression = $tokens->getPrevMeaningfulToken($receiver);

            return $innerExpression === null || $innerExpression <= $callParenthesis
                ? null
                : $this->resolveReceiverClass($tokens, $innerExpression, $position, $classIndex);

        }

        $callable = $this->readQualifiedNameEndingAt($tokens, $nameIndex);
        $beforeName = $tokens->getPrevMeaningfulToken($callable[ 'start' ]);

        if ($beforeName !== null && $tokens[ $beforeName ]->isGivenKind(T_NEW)) {
            return $this->resolveClassReference($callable[ 'name' ], $position, $classIndex);
        }

        if ($beforeName !== null && $tokens[ $beforeName ]->isGivenKind(T_DOUBLE_COLON)) {

            $ownerIndex = $tokens->getPrevMeaningfulToken($beforeName);

            if ($ownerIndex === null || $this->isNameToken($tokens[ $ownerIndex ]) === false) {
                return null;
            }

            $owner = $this->readQualifiedNameEndingAt($tokens, $ownerIndex);
            $ownerClass = $this->resolveClassReference($owner[ 'name' ], $position, $classIndex);

            return $ownerClass === null
                ? null
                : $this->resolveMethodReturnClass($ownerClass, $callable[ 'name' ]);

        }

        if ($beforeName === null
            || $tokens[ $beforeName ]->isGivenKind([ T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR ]) === false) {

            return $this->resolveFunctionReturnClass($callable[ 'name' ], $position)
                ?? $this->resolveClassStringFunctionClass(
                    tokens: $tokens,
                    openParenthesis: $callParenthesis,
                    closeParenthesis: $receiver,
                    classIndex: $classIndex,
                );

        }

        $innerReceiver = $tokens->getPrevMeaningfulToken($beforeName);

        if ($innerReceiver === null) {
            return null;
        }

        $ownerClass = $this->resolveReceiverClass($tokens, $innerReceiver, $position, $classIndex);

        return $ownerClass === null
            ? null
            : $this->resolveMethodReturnClass($ownerClass, $callable[ 'name' ]);
    }

    private function resolveClassStringFunctionClass(Tokens $tokens, int $openParenthesis, int $closeParenthesis, ?int $classIndex): ?string
    {
        $resolvedClass = null;

        foreach ($this->argumentRanges($tokens, $openParenthesis, $closeParenthesis) as $argument) {

            $argumentClass = $this->resolveClassConstantArgument($tokens, $argument, $classIndex);

            if ($argumentClass === null) {
                continue;
            }

            if ($resolvedClass !== null) {
                return null;
            }

            $resolvedClass = $argumentClass;

        }

        return $resolvedClass;
    }

    /**
     * @param array{start: int, end: int} $argument
     */
    private function resolveClassConstantArgument(Tokens $tokens, array $argument, ?int $classIndex): ?string
    {
        $className = $tokens->getNextMeaningfulToken($argument[ 'start' ] - 1);

        if ($className === null || $className > $argument[ 'end' ]) {
            return null;
        }

        $separator = $tokens->getNextMeaningfulToken($className);

        if ($separator !== null && $tokens[ $separator ]->getContent() === ':') {
            $className = $tokens->getNextMeaningfulToken($separator);
        }

        if ($className === null
            || $className > $argument[ 'end' ]
            || $this->isNameToken($tokens[ $className ]) === false) {
            return null;
        }

        $staticOperator = $tokens->getNextMeaningfulToken($className);
        $classConstant = $staticOperator === null ? null : $tokens->getNextMeaningfulToken($staticOperator);

        if ($staticOperator === null
            || $classConstant === null
            || $classConstant > $argument[ 'end' ]
            || $tokens[ $staticOperator ]->isGivenKind(T_DOUBLE_COLON) === false
            || $tokens[ $classConstant ]->isGivenKind(CT::T_CLASS_CONSTANT) === false) {
            return null;
        }

        $afterClassConstant = $tokens->getNextMeaningfulToken($classConstant);

        if ($afterClassConstant !== null && $afterClassConstant <= $argument[ 'end' ]) {
            return null;
        }

        $classReference = $this->readQualifiedNameEndingAt($tokens, $className);

        return $this->resolveClassReference($classReference[ 'name' ], $className, $classIndex);
    }

    /**
     * @return array{found: bool, class: string|null}
     */
    private function resolveLocalVariableClass(Tokens $tokens, int $variableIndex, ?int $classIndex): array
    {
        $scopeIndex = $this->findContainingCallableScope($variableIndex);

        if ($scopeIndex === null) {
            return [ 'found' => false, 'class' => null ];
        }

        $scope = $this->callableScopes[ $scopeIndex ];
        $useBlock = $this->findContainingCurlyBlock($variableIndex);
        $variable = $tokens[ $variableIndex ]->getContent();

        for ($index = $variableIndex - 1; $index > $scope[ 'start' ]; $index--) {

            if ($tokens[ $index ]->isGivenKind(T_VARIABLE) === false
                || $tokens[ $index ]->getContent() !== $variable
                || $this->findContainingCallableScope($index) !== $scopeIndex) {
                continue;
            }

            $operator = $tokens->getNextMeaningfulToken($index);

            if ($operator === null || $tokens[ $operator ]->getContent() !== '=') {
                continue;
            }

            if ($this->findContainingCurlyBlock($index) !== $useBlock
                || $this->isConditionalAssignment($tokens, $index)) {
                return [ 'found' => true, 'class' => null ];
            }

            $expressionEnd = $this->findAssignmentExpressionEnd(
                tokens: $tokens,
                assignmentOperator: $operator,
                boundary: $variableIndex,
            );

            if ($expressionEnd === null) {
                return [ 'found' => true, 'class' => null ];
            }

            return [
                'found' => true,
                'class' => $this->resolveReceiverClass(
                    tokens: $tokens,
                    receiver: $expressionEnd,
                    position: $index,
                    classIndex: $classIndex,
                ),
            ];

        }

        return [ 'found' => false, 'class' => null ];
    }

    private function findAssignmentExpressionEnd(Tokens $tokens, int $assignmentOperator, int $boundary): ?int
    {
        $end = null;

        for ($index = $assignmentOperator + 1; $index < $boundary; $index++) {

            if ($tokens[ $index ]->isWhitespace() || $tokens[ $index ]->isComment()) {
                continue;
            }

            $block = Tokens::detectBlockType($tokens[ $index ]);

            if ($block !== null && $block[ 'isStart' ]) {

                $blockEnd = $tokens->findBlockEnd($block[ 'type' ], $index);

                if ($blockEnd >= $boundary) {
                    return null;
                }

                $end = $end === null && $tokens[ $index ]->getContent() === '('
                    ? $tokens->getPrevMeaningfulToken($blockEnd)
                    : $blockEnd;

                $index = $blockEnd;

                continue;

            }

            if ($tokens[ $index ]->equalsAny([ ';', ',', ')', ']', '}' ])) {
                break;
            }

            if ($tokens[ $index ]->equalsAny([
                '?',
                ':',
                '+',
                '-',
                '*',
                '/',
                '%',
                '.',
                '<',
                '>',
                '<=',
                '>=',
                '==',
                '===',
                '!=',
                '!==',
                '<=>',
                '??',
                '&&',
                '||',
            ])) {
                return null;
            }

            $end = $index;

        }

        return $end;
    }

    private function isConditionalAssignment(Tokens $tokens, int $variableIndex): bool
    {
        for ($index = $variableIndex - 1; $index >= 0; $index--) {

            if ($tokens[ $index ]->equalsAny([ ';', '{', '}' ])) {
                return false;
            }

            if ($tokens[ $index ]->isGivenKind([
                T_DO,
                T_ELSE,
                T_ELSEIF,
                T_FOR,
                T_FOREACH,
                T_IF,
                T_WHILE,
            ])) {
                return true;
            }

        }

        return false;
    }

    private function findContainingCallableScope(int $position): ?int
    {
        $match = null;
        $matchStart = -1;

        foreach ($this->callableScopes as $index => $scope) {

            if ($position > $scope[ 'start' ]
                && $position < $scope[ 'end' ]
                && $scope[ 'start' ] > $matchStart) {

                $match = $index;
                $matchStart = $scope[ 'start' ];

            }

        }

        return $match;
    }

    private function findContainingCurlyBlock(int $position): ?int
    {
        $match = null;
        $matchStart = -1;

        foreach ($this->curlyScopes as $scope) {

            if ($position > $scope[ 'start' ]
                && $position < $scope[ 'end' ]
                && $scope[ 'start' ] > $matchStart) {

                $match = $scope[ 'start' ];
                $matchStart = $scope[ 'start' ];

            }

        }

        return $match;
    }

    private function resolveSourceParameterClass(Tokens $tokens, int $position, string $variable, ?int $classIndex): ?string
    {
        for ($scopeIndex = count($this->callableScopes) - 1; $scopeIndex >= 0; $scopeIndex--) {

            $scope = $this->callableScopes[ $scopeIndex ];

            if ($position < $scope[ 'start' ] || $position > $scope[ 'end' ]) {
                continue;
            }

            foreach ($this->argumentRanges(
                tokens: $tokens,
                openParenthesis: $scope[ 'openParenthesis' ],
                closeParenthesis: $scope[ 'closeParenthesis' ],
            ) as $range) {

                for ($index = $range[ 'start' ]; $index <= $range[ 'end' ]; $index++) {

                    if ($tokens[ $index ]->isGivenKind(T_VARIABLE) === false
                        || $tokens[ $index ]->getContent() !== $variable) {
                        continue;
                    }

                    $typeIndex = $tokens->getPrevMeaningfulToken($index);

                    if ($typeIndex !== null
                        && in_array($tokens[ $typeIndex ]->getContent(), [ '&', '...' ], true)) {
                        $typeIndex = $tokens->getPrevMeaningfulToken($typeIndex);
                    }

                    if ($typeIndex === null || $this->isNameToken($tokens[ $typeIndex ]) === false) {
                        return null;
                    }

                    $beforeType = $tokens->getPrevMeaningfulToken($typeIndex);

                    if ($beforeType !== null
                        && $beforeType >= $range[ 'start' ]
                        && in_array($tokens[ $beforeType ]->getContent(), [ '|', '&' ], true)) {
                        return null;
                    }

                    return $this->resolveClassReference(
                        identifier: $tokens[ $typeIndex ]->getContent(),
                        position: $index,
                        classIndex: $classIndex,
                    );

                }

            }

        }

        return null;
    }

    private function resolvePropertyReceiverClass(Tokens $tokens, int $property, ?int $classIndex): ?string
    {
        $operator = $tokens->getPrevMeaningfulToken($property);
        $owner = $operator === null ? null : $tokens->getPrevMeaningfulToken($operator);

        if ($operator === null
            || $tokens[ $operator ]->isGivenKind([ T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR ]) === false
            || $owner === null) {
            return null;
        }

        $ownerClass = $this->resolveReceiverClass(
            tokens: $tokens,
            receiver: $owner,
            position: $property,
            classIndex: $classIndex,
        );

        return $ownerClass === null
            ? null
            : $this->resolvePropertyClass($tokens, $ownerClass, $tokens[ $property ]->getContent());
    }

    private function resolveSourcePropertyClass(Tokens $tokens, int $classIndex, string $property): ?string
    {
        $expectedVariable = sprintf('$%s', $property);

        for ($index = $this->classes[ $classIndex ][ 'start' ] + 1;
            $index < $this->classes[ $classIndex ][ 'end' ];
            $index++) {

            if ($tokens[ $index ]->isGivenKind(T_VARIABLE) === false
                || $tokens[ $index ]->getContent() !== $expectedVariable) {
                continue;
            }

            $typeIndex = $tokens->getPrevMeaningfulToken($index);

            if ($typeIndex === null || $this->isNameToken($tokens[ $typeIndex ]) === false) {
                continue;
            }

            $hasVisibility = false;
            $ambiguousType = false;
            $cursor = $typeIndex;

            while (($cursor = $tokens->getPrevMeaningfulToken($cursor)) !== null) {

                if ($tokens[ $cursor ]->equalsAny([ ';', '{', '}', '(', ',' ])) {
                    break;
                }

                if (in_array($tokens[ $cursor ]->getContent(), [ 'public', 'protected', 'private' ], true)) {
                    $hasVisibility = true;
                }

                if (in_array($tokens[ $cursor ]->getContent(), [ '|', '&' ], true)) {
                    $ambiguousType = true;
                }

            }

            if ($hasVisibility === false || $ambiguousType) {
                continue;
            }

            return $this->resolveClassReference(
                identifier: $tokens[ $typeIndex ]->getContent(),
                position: $index,
                classIndex: $classIndex,
            );

        }

        return null;
    }

    private function resolvePropertyClass(Tokens $tokens, string $className, string $property): ?string
    {
        $sourceClass = $this->findSourceClass($className);

        if ($sourceClass !== null) {

            $propertyClass = $this->resolveSourcePropertyClass($tokens, $sourceClass, $property);

            if ($propertyClass !== null) {
                return $propertyClass;
            }

            $parent = $this->classes[ $sourceClass ][ 'parent' ];

            return $parent === null ? null : $this->resolvePropertyClass($tokens, $parent, $property);

        }

        try {

            if (class_exists($className) === false
                && interface_exists($className) === false
                && trait_exists($className) === false) {
                return null;
            }

            $class = new ReflectionClass($className);

            if ($class->hasProperty($property) === false) {
                return null;
            }

            $reflectionProperty = $class->getProperty($property);
            $type = $reflectionProperty->getType();

            if ($type instanceof ReflectionNamedType) {
                return $this->resolveReflectedNamedType($type, $reflectionProperty->getDeclaringClass());
            }

            if ($type !== null) {
                return null;
            }

            $docComment = $reflectionProperty->getDocComment();

            if ($docComment === false
                || preg_match('/@var\s+([^\s]+)/', $docComment, $matches) !== 1) {
                return null;
            }

            return $this->resolveReflectedTypeName($matches[ 1 ], $reflectionProperty->getDeclaringClass());

        } catch (Throwable) {

            return null;

        }
    }

    private function resolveClassReference(string $identifier, int $position, ?int $classIndex): ?string
    {
        $normalizedIdentifier = strtolower($identifier);

        if (($normalizedIdentifier === 'self' || $normalizedIdentifier === 'static') && $classIndex !== null) {
            return $this->classes[ $classIndex ][ 'name' ];
        }

        if ($normalizedIdentifier === 'parent' && $classIndex !== null) {
            return $this->classes[ $classIndex ][ 'parent' ];
        }

        return $this->resolveClassIdentifier($identifier, $position);
    }

    /**
     * @return list<array{name: string, variadic: bool}>|null
     */
    private function resolveFunction(string $name, int $position): ?array
    {
        foreach ($this->functionCandidates($name, $position) as $candidate) {

            $localParameters = $this->functions[ strtolower($candidate) ] ?? null;

            if ($localParameters !== null) {
                return $localParameters;
            }

            $parameters = $this->reflectFunction($candidate);

            if ($parameters !== null) {
                return $parameters;
            }

        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function functionCandidates(string $name, int $position): array
    {
        $contextIndex = $this->findNamespaceContext($position);
        $namespace = $contextIndex === null ? '' : $this->namespaceContexts[ $contextIndex ][ 'name' ];
        $candidates = [];

        if (str_starts_with($name, '\\')) {

            $candidates[] = ltrim($name, '\\');

        } elseif (str_starts_with(strtolower($name), 'namespace\\')) {

            $candidates[] = $this->qualifyName($namespace, substr($name, 10));

        } elseif (str_contains($name, '\\')) {

            $candidates[] = $this->qualifyName($namespace, $name);

        } else {

            $import = $contextIndex === null
                ? null
                : $this->namespaceContexts[ $contextIndex ][ 'functionImports' ][ strtolower($name) ] ?? null;

            if ($import !== null) {

                $candidates[] = $import;

            } else {

                $candidates[] = $this->qualifyName($namespace, $name);
                $candidates[] = $name;

            }

        }

        return array_values(array_unique($candidates));
    }

    private function resolveFunctionReturnClass(string $name, int $position): ?string
    {
        foreach ($this->functionCandidates($name, $position) as $candidate) {

            $localReturnType = $this->functionReturnTypes[ strtolower($candidate) ] ?? null;

            if ($localReturnType !== null) {

                return $localReturnType[ 'type' ] === null
                    ? null
                    : $this->resolveClassReference(
                        identifier: $localReturnType[ 'type' ],
                        position: $localReturnType[ 'position' ],
                        classIndex: null,
                    );

            }

            $returnClass = $this->reflectFunctionReturnClass($candidate);

            if ($returnClass !== null) {
                return $returnClass;
            }

        }

        return null;
    }

    /**
     * @return list<array{name: string, variadic: bool}>|null
     */
    private function resolveSourceMethod(int $classIndex, string $method, array $visited = []): ?array
    {
        if (isset($visited[ $classIndex ])) {
            return null;
        }

        $visited[ $classIndex ] = true;
        $parameters = $this->classes[ $classIndex ][ 'methods' ][ strtolower($method) ] ?? null;

        if ($parameters !== null) {
            return $parameters;
        }

        $parent = $this->classes[ $classIndex ][ 'parent' ];

        return $parent === null ? null : $this->resolveMethod($parent, $method, $visited);
    }

    private function resolveSourceMethodReturnClass(int $classIndex, string $method, array $visited = []): ?string
    {
        if (isset($visited[ $classIndex ])) {
            return null;
        }

        $visited[ $classIndex ] = true;
        $methodName = strtolower($method);
        $parameters = $this->classes[ $classIndex ][ 'methods' ][ $methodName ] ?? null;

        if ($parameters !== null) {

            $returnType = $this->classes[ $classIndex ][ 'methodReturnTypes' ][ $methodName ] ?? null;

            return $returnType === null
                ? null
                : $this->resolveClassReference(
                    identifier: $returnType,
                    position: $this->classes[ $classIndex ][ 'start' ],
                    classIndex: $classIndex,
                );

        }

        $parent = $this->classes[ $classIndex ][ 'parent' ];

        return $parent === null ? null : $this->resolveMethodReturnClass($parent, $method, $visited);
    }

    private function resolveMethodReturnClass(string $className, string $method, array $visited = []): ?string
    {
        $sourceClass = $this->findSourceClass($className);

        if ($sourceClass !== null) {
            return $this->resolveSourceMethodReturnClass($sourceClass, $method, $visited);
        }

        $reflectionMethod = $this->reflectMethod($className, $method);

        return $reflectionMethod === null ? null : $this->resolveReflectedMethodReturnClass($reflectionMethod);
    }

    /**
     * @return list<array{name: string, variadic: bool}>|null
     */
    private function resolveMethod(string $className, string $method, array $visited = []): ?array
    {
        $sourceClass = $this->findSourceClass($className);

        if ($sourceClass !== null) {
            return $this->resolveSourceMethod($sourceClass, $method, $visited);
        }

        $reflectionMethod = $this->reflectMethod($className, $method);

        return $reflectionMethod === null
            ? null
            : $this->reflectionParameters($reflectionMethod->getParameters());
    }

    private function reflectMethod(string $className, string $method): ?ReflectionMethod
    {
        try {

            if (class_exists($className) === false && interface_exists($className) === false && trait_exists($className) === false) {
                return null;
            }

            return $this->findReflectionMethod(new ReflectionClass($className), $method);

        } catch (Throwable) {

            return null;

        }
    }

    private function findReflectionMethod(ReflectionClass $class, string $method, array $visited = []): ?ReflectionMethod
    {
        $className = strtolower($class->getName());

        if (isset($visited[ $className ])) {
            return null;
        }

        $visited[ $className ] = true;

        if ($class->hasMethod($method)) {
            return $class->getMethod($method);
        }

        foreach ($this->reflectionMixinClassNames($class) as $mixinClassName) {

            if (class_exists($mixinClassName) === false && interface_exists($mixinClassName) === false) {
                continue;
            }

            $reflectionMethod = $this->findReflectionMethod(
                class: new ReflectionClass($mixinClassName),
                method: $method,
                visited: $visited,
            );

            if ($reflectionMethod !== null) {
                return $reflectionMethod;
            }

        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function reflectionMixinClassNames(ReflectionClass $class): array
    {
        $docComment = $class->getDocComment();

        if ($docComment === false
            || preg_match_all('/@mixin\s+([^\s]+)/', $docComment, $matches) < 1) {
            return [];
        }

        $classNames = [];

        foreach ($matches[ 1 ] as $type) {

            $type = preg_replace('/<.*>$/', '', $type) ?? $type;

            if (str_starts_with($type, '\\')) {

                $classNames[] = ltrim($type, '\\');

                continue;

            }

            $classNames[] = $this->qualifyName($class->getNamespaceName(), $type);

        }

        return $classNames;
    }

    private function resolveReflectedMethodReturnClass(ReflectionMethod $method): ?string
    {
        $returnType = $method->getReturnType();
        $declaringClass = $method->getDeclaringClass();

        if ($returnType instanceof ReflectionNamedType) {
            return $this->resolveReflectedNamedType($returnType, $declaringClass);
        }

        if ($returnType !== null) {
            return null;
        }

        $docComment = $method->getDocComment();

        if ($docComment === false
            || preg_match('/@return\s+([^\s]+)/', $docComment, $matches) !== 1) {
            return null;
        }

        return $this->resolveReflectedTypeName($matches[ 1 ], $declaringClass);
    }

    private function resolveReflectedNamedType(ReflectionNamedType $type, ReflectionClass $declaringClass): ?string
    {
        if ($type->isBuiltin()) {
            return null;
        }

        $name = $type->getName();

        if (in_array(strtolower($name), [ 'self', 'static' ], true)) {
            return $declaringClass->getName();
        }

        if (strtolower($name) === 'parent') {

            $parent = $declaringClass->getParentClass();

            return $parent === false ? null : $parent->getName();

        }

        return $name;
    }

    private function resolveReflectedTypeName(string $type, ReflectionClass $declaringClass): ?string
    {
        $type = ltrim(trim($type), '?');
        $type = preg_replace('/<.*>$/', '', $type) ?? $type;
        $types = array_values(array_filter(
            array: explode('|', $type),
            callback: static fn (string $candidate): bool => strtolower($candidate) !== 'null',
        ));

        if (count($types) !== 1 || str_contains($types[ 0 ], '&')) {
            return null;
        }

        $type = $types[ 0 ];
        $normalizedType = strtolower($type);

        if (in_array($normalizedType, [ '$this', 'self', 'static' ], true)) {
            return $declaringClass->getName();
        }

        if ($normalizedType === 'parent') {

            $parent = $declaringClass->getParentClass();

            return $parent === false ? null : $parent->getName();

        }

        if (in_array($normalizedType, [
            'array',
            'bool',
            'callable',
            'false',
            'float',
            'int',
            'iterable',
            'mixed',
            'never',
            'null',
            'object',
            'string',
            'true',
            'void',
        ], true)) {
            return null;
        }

        return str_starts_with($type, '\\')
            ? ltrim($type, '\\')
            : $this->qualifyName($declaringClass->getNamespaceName(), $type);
    }

    /**
     * @return list<array{name: string, variadic: bool}>|null
     */
    private function resolveConstructor(string $className): ?array
    {
        $sourceClass = $this->findSourceClass($className);

        if ($sourceClass !== null) {
            return $this->resolveSourceMethod($sourceClass, '__construct');
        }

        try {

            if (class_exists($className) === false) {
                return null;
            }

            $constructor = new ReflectionClass($className)->getConstructor();

            return $constructor === null ? null : $this->reflectionParameters($constructor->getParameters());

        } catch (Throwable) {

            return null;

        }
    }

    /**
     * @return list<array{name: string, variadic: bool}>|null
     */
    private function reflectFunction(string $name): ?array
    {
        try {

            if (function_exists($name) === false) {
                return null;
            }

            return $this->reflectionParameters(new ReflectionFunction($name)->getParameters());

        } catch (Throwable) {

            return null;

        }
    }

    private function reflectFunctionReturnClass(string $name): ?string
    {
        try {

            if (function_exists($name) === false) {
                return null;
            }

            $function = new ReflectionFunction($name);
            $returnType = $function->getReturnType();

            if ($returnType instanceof ReflectionNamedType) {
                return $returnType->isBuiltin() ? null : $returnType->getName();
            }

            if ($returnType !== null) {
                return null;
            }

            $docComment = $function->getDocComment();

            if ($docComment === false
                || preg_match('/@return\s+([^\s]+)/', $docComment, $matches) !== 1) {
                return null;
            }

            $type = ltrim(trim($matches[ 1 ]), '?');
            $type = preg_replace('/<.*>$/', '', $type) ?? $type;

            if (str_contains($type, '|') || str_contains($type, '&')) {
                return null;
            }

            return str_starts_with($type, '\\')
                ? ltrim($type, '\\')
                : $this->qualifyName($function->getNamespaceName(), $type);

        } catch (Throwable) {

            return null;

        }
    }

    /**
     * @param list<ReflectionParameter> $parameters
     *
     * @return list<array{name: string, variadic: bool}>
     */
    private function reflectionParameters(array $parameters): array
    {
        return array_map(
            callback: static fn (ReflectionParameter $parameter): array => [
                'name' => $parameter->getName(),
                'variadic' => $parameter->isVariadic(),
            ],
            array: $parameters,
        );
    }

    private function resolveClassIdentifier(string $name, int $position): ?string
    {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        $contextIndex = $this->findNamespaceContext($position);
        $namespace = $contextIndex === null ? '' : $this->namespaceContexts[ $contextIndex ][ 'name' ];

        if (str_starts_with($name, '\\')) {
            return ltrim($name, '\\');
        }

        if (str_starts_with(strtolower($name), 'namespace\\')) {
            return $this->qualifyName($namespace, substr($name, 10));
        }

        $parts = explode('\\', $name);
        $alias = $contextIndex === null
            ? null
            : $this->namespaceContexts[ $contextIndex ][ 'classImports' ][ strtolower($parts[ 0 ]) ] ?? null;

        if ($alias !== null) {

            array_shift($parts);

            return $parts === [] ? $alias : sprintf('%s\\%s', $alias, implode('\\', $parts));

        }

        return $this->qualifyName($namespace, $name);
    }

    private function qualifyName(string $namespace, string $name): string
    {
        $name = ltrim($name, '\\');

        return $namespace === '' ? $name : sprintf('%s\\%s', $namespace, $name);
    }

    private function findNamespaceContext(int $position): ?int
    {
        foreach ($this->namespaceContexts as $index => $context) {

            if ($position >= $context[ 'start' ] && $position <= $context[ 'end' ]) {
                return $index;
            }

        }

        return null;
    }

    private function findContainingClass(int $position): ?int
    {
        $match = null;
        $matchStart = -1;

        foreach ($this->classes as $index => $class) {

            if ($position > $class[ 'start' ] && $position < $class[ 'end' ] && $class[ 'start' ] > $matchStart) {

                $match = $index;
                $matchStart = $class[ 'start' ];

            }

        }

        return $match;
    }

    private function findSourceClass(string $className): ?int
    {
        foreach ($this->classes as $index => $class) {

            if ($class[ 'name' ] !== null && strcasecmp($class[ 'name' ], $className) === 0) {
                return $index;
            }

        }

        return null;
    }

    /**
     * @return array{name: string, start: int}
     */
    private function readQualifiedNameEndingAt(Tokens $tokens, int $end): array
    {
        $start = $end;

        while ($start >= 2
            && $tokens[ $start - 1 ]->isGivenKind(T_NS_SEPARATOR)
            && $tokens[ $start - 2 ]->isGivenKind([ T_STRING, T_NAMESPACE ])) {
            $start -= 2;
        }

        if ($start > 0 && $tokens[ $start - 1 ]->isGivenKind(T_NS_SEPARATOR)) {
            $start--;
        }

        $name = '';

        for ($index = $start; $index <= $end; $index++) {
            $name = sprintf('%s%s', $name, $tokens[ $index ]->getContent());
        }

        return [ 'name' => $name, 'start' => $start ];
    }

    private function readQualifiedNameStartingAt(Tokens $tokens, int $start): string
    {
        $end = $start;

        if ($tokens[ $start ]->isGivenKind(T_NS_SEPARATOR)) {
            $end++;
        }

        while ($end + 2 < $tokens->count()
            && $tokens[ $end + 1 ]->isGivenKind(T_NS_SEPARATOR)
            && $tokens[ $end + 2 ]->isGivenKind(T_STRING)) {
            $end += 2;
        }

        $name = '';

        for ($index = $start; $index <= $end; $index++) {
            $name = sprintf('%s%s', $name, $tokens[ $index ]->getContent());
        }

        return $name;
    }

    private function isNameToken(Token $token): bool
    {
        return $token->isGivenKind([
            T_STRING,
            T_NAME_QUALIFIED,
            T_NAME_FULLY_QUALIFIED,
            T_NAME_RELATIVE,
            T_STATIC,
        ]);
    }
}
