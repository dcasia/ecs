<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Fixers;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use ReflectionClass;
use ReflectionFunction;
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
     *     methods: array<string, list<array{name: string, variadic: bool}>>
     * }>
     */
    private array $classes = [];

    /**
     * @var array<string, list<array{name: string, variadic: bool}>>
     */
    private array $functions = [];

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
                Tokens::BLOCK_TYPE_PARENTHESIS_BRACE,
                $openParenthesis,
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
     *     methods: array<string, list<array{name: string, variadic: bool}>>
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

            $parameters = $this->readDeclaredParameters(
                tokens: $tokens,
                openParenthesis: $openParenthesis,
                closeParenthesis: $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParenthesis),
            );

            $name = strtolower($tokens[ $nameIndex ]->getContent());
            $classIndex = $this->findContainingClass($index);

            if ($classIndex !== null) {

                $this->classes[ $classIndex ][ 'methods' ][ $name ] = $parameters;

                continue;

            }

            $contextIndex = $this->findNamespaceContext($index);
            $namespace = $contextIndex === null ? '' : $this->namespaceContexts[ $contextIndex ][ 'name' ];
            $functionName = strtolower($this->qualifyName($namespace, $tokens[ $nameIndex ]->getContent()));
            $this->functions[ $functionName ] = $parameters;

        }
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
            $openParenthesis,
            $arguments[ 0 ][ 'start' ] - 1,
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

        if ($nameIndex === null || $this->isNameToken($tokens[ $nameIndex ]) === false) {
            return null;
        }

        $callableName = $this->readQualifiedNameEndingAt($tokens, $nameIndex);
        $name = $callableName[ 'name' ];
        $beforeName = $tokens->getPrevMeaningfulToken($callableName[ 'start' ]);
        $classIndex = $this->findContainingClass($openParenthesis);

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
        if ($tokens[ $receiver ]->isGivenKind(T_VARIABLE)) {

            if ($tokens[ $receiver ]->getContent() !== '$this' || $classIndex === null) {
                return null;
            }

            return $this->resolveSourceMethod($classIndex, $method);

        }

        if ($tokens[ $receiver ]->equals(')') === false) {
            return null;
        }

        $constructorParenthesis = $tokens->findBlockStart(
            Tokens::BLOCK_TYPE_PARENTHESIS_BRACE,
            $receiver,
        );

        $classNameIndex = $tokens->getPrevMeaningfulToken($constructorParenthesis);

        if ($classNameIndex === null || $this->isNameToken($tokens[ $classNameIndex ]) === false) {
            return null;
        }

        $classIdentifier = $this->readQualifiedNameEndingAt($tokens, $classNameIndex);
        $newIndex = $tokens->getPrevMeaningfulToken($classIdentifier[ 'start' ]);

        if ($newIndex === null || $tokens[ $newIndex ]->isGivenKind(T_NEW) === false) {
            return null;
        }

        $normalizedIdentifier = strtolower($classIdentifier[ 'name' ]);

        if (($normalizedIdentifier === 'self' || $normalizedIdentifier === 'static') && $classIndex !== null) {
            return $this->resolveSourceMethod($classIndex, $method);
        }

        if ($normalizedIdentifier === 'parent' && $classIndex !== null) {

            $parent = $this->classes[ $classIndex ][ 'parent' ];

            return $parent === null ? null : $this->resolveMethod($parent, $method);

        }

        $className = $this->resolveClassIdentifier($classIdentifier[ 'name' ], $position);

        return $className === null ? null : $this->resolveMethod($className, $method);
    }

    /**
     * @return list<array{name: string, variadic: bool}>|null
     */
    private function resolveFunction(string $name, int $position): ?array
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

        foreach (array_unique($candidates) as $candidate) {

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

    /**
     * @return list<array{name: string, variadic: bool}>|null
     */
    private function resolveMethod(string $className, string $method, array $visited = []): ?array
    {
        $sourceClass = $this->findSourceClass($className);

        if ($sourceClass !== null) {
            return $this->resolveSourceMethod($sourceClass, $method, $visited);
        }

        try {

            if (class_exists($className) === false && interface_exists($className) === false && trait_exists($className) === false) {
                return null;
            }

            $reflection = new ReflectionClass($className);

            if ($reflection->hasMethod($method) === false) {
                return null;
            }

            return $this->reflectionParameters($reflection->getMethod($method)->getParameters());

        } catch (Throwable) {

            return null;

        }
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
