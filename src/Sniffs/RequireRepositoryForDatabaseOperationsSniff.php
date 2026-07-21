<?php

declare(strict_types = 1);

namespace DigitalCreative\ECS\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

final class RequireRepositoryForDatabaseOperationsSniff implements Sniff
{
    private const array BUILDER_METHODS = [
        'addselect',
        'avg',
        'chunk',
        'count',
        'create',
        'cursor',
        'decrement',
        'delete',
        'doesntexist',
        'exists',
        'find',
        'findorfail',
        'first',
        'firstorcreate',
        'firstorfail',
        'firstornew',
        'get',
        'groupby',
        'having',
        'increment',
        'insert',
        'join',
        'lazy',
        'limit',
        'lockforupdate',
        'max',
        'min',
        'orderby',
        'orwhere',
        'orwherehas',
        'paginate',
        'pluck',
        'select',
        'sum',
        'update',
        'updateorcreate',
        'upsert',
        'value',
        'where',
        'wherehas',
        'wherein',
        'wherenotnull',
        'wherenull',
        'with',
        'withcount',
    ];

    private const array DB_FACADE_METHODS = [
        'affectingstatement',
        'begintransaction',
        'commit',
        'connection',
        'delete',
        'insert',
        'insertusing',
        'rollback',
        'scalar',
        'select',
        'selectfromwriteconnection',
        'selectone',
        'statement',
        'table',
        'transaction',
        'unprepared',
        'update',
    ];

    private const string ERROR = 'Database operation `%s()` is outside a repository. DB queries MUST be done via a repository class. Create a class whose filename ends in `Repository.php` and move all database interactions there. A repository MUST handle only its own Eloquent model; delegate interactions with other models to their respective repositories.';

    private const array MODEL_STATIC_METHODS = [
        'all',
        'create',
        'destroy',
        'find',
        'findmany',
        'findorfail',
        'firstorcreate',
        'firstornew',
        'forcecreate',
        'query',
        'truncate',
        'updateorcreate',
        'upsert',
        'where',
        'wherehas',
        'wherein',
        'with',
    ];

    private const array MODEL_WRITE_METHODS = [
        'decrement',
        'delete',
        'deletequietly',
        'forcedelete',
        'increment',
        'push',
        'pushquietly',
        'restore',
        'touch',
        'update',
        'updatequietly',
    ];

    private const array SAVE_METHODS = [
        'save',
        'saveorfail',
        'savequietly',
    ];

    /**
     * @var array<string, array{
     *     builderVariables: array<string, true>,
     *     isModelFile: bool,
     *     modelClasses: array<string, true>,
     *     modelVariables: array<string, true>
     * }>
     */
    private array $analysisByFile = [];

    private readonly bool $laravelInstalled;

    public function __construct()
    {
        $this->laravelInstalled = class_exists('Illuminate\Foundation\Application');
    }

    public function register(): array
    {
        if (!$this->laravelInstalled) {
            return [];
        }

        return [ T_STRING ];
    }

    public function process(File $phpcsFile, int $stackPtr): void
    {
        if ($this->isRepositoryFile($phpcsFile)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();
        $next = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $stackPtr + 1, null, true);

        if ($next === false || $tokens[ $next ][ 'code' ] !== T_OPEN_PARENTHESIS) {
            return;
        }

        $method = strtolower($tokens[ $stackPtr ][ 'content' ]);
        $previous = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $stackPtr - 1, null, true);

        if ($previous === false) {
            return;
        }

        $operation = false;

        if ($tokens[ $previous ][ 'code' ] === T_DOUBLE_COLON) {

            $operation = $this->isStaticDatabaseOperation($phpcsFile, $previous, $method);

        } else if (in_array($tokens[ $previous ][ 'code' ], [ T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR ], true)) {

            $operation = $this->isObjectDatabaseOperation($phpcsFile, $previous, $method);

        }

        if (!$operation) {
            return;
        }

        $phpcsFile->addError(
            error: self::ERROR,
            stackPtr: $stackPtr,
            code: 'OutsideRepository',
            data: [ $tokens[ $stackPtr ][ 'content' ] ],
        );
    }

    private function analyseFile(File $phpcsFile): array
    {
        $filename = $phpcsFile->getFilename();

        if (isset($this->analysisByFile[ $filename ])) {
            return $this->analysisByFile[ $filename ];
        }

        $tokens = $phpcsFile->getTokens();
        $modelClasses = [];
        $builderVariables = [];
        $modelVariables = [];
        $isModelFile = false;
        $namespace = '';

        foreach ($tokens as $index => $token) {

            if ($token[ 'code' ] === T_NAMESPACE) {

                $namespace = $this->collectName($tokens, $index + 1);
                $isModelFile = str_contains(strtolower($namespace), '\\models');

                continue;

            }

            if ($token[ 'code' ] !== T_USE || $token[ 'conditions' ] !== []) {
                continue;
            }

            $import = $this->collectName($tokens, $index + 1);

            if (!str_contains(strtolower($import), '\\models\\')) {
                continue;
            }

            $parts = preg_split('/\s+as\s+/i', $import);

            if (!is_array($parts)) {
                continue;
            }

            $class = $parts[ 1 ] ?? substr($parts[ 0 ], (int) strrpos($parts[ 0 ], '\\') + 1);
            $modelClasses[ strtolower($class) ] = true;

        }

        foreach ($tokens as $index => $token) {

            if ($token[ 'code' ] !== T_VARIABLE) {
                continue;
            }

            $variable = $token[ 'content' ];
            $previous = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $index - 1, null, true);

            if ($previous !== false && str_ends_with(strtolower($tokens[ $previous ][ 'content' ]), 'builder')) {
                $builderVariables[ $variable ] = true;
            }

            if ($previous !== false && $this->isModelClassName($tokens[ $previous ][ 'content' ], $modelClasses, $isModelFile)) {
                $modelVariables[ $variable ] = true;
            }

            $assignment = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $index + 1, null, true);

            if ($assignment === false || $tokens[ $assignment ][ 'code' ] !== T_EQUAL) {
                continue;
            }

            $value = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $assignment + 1, null, true);

            if ($value === false) {
                continue;
            }

            if ($tokens[ $value ][ 'code' ] === T_NEW) {
                $value = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $value + 1, null, true);
            }

            if (
                $value !== false
                && $this->isModelClassName($tokens[ $value ][ 'content' ], $modelClasses, $isModelFile)
            ) {
                $modelVariables[ $variable ] = true;
            }

        }

        return $this->analysisByFile[ $filename ] = [
            'builderVariables' => $builderVariables,
            'isModelFile' => $isModelFile,
            'modelClasses' => $modelClasses,
            'modelVariables' => $modelVariables,
        ];
    }

    private function collectName(array $tokens, int $start): string
    {
        $name = '';

        for ($index = $start, $count = count($tokens); $index < $count; $index++) {

            if (in_array($tokens[ $index ][ 'code' ], [ T_SEMICOLON, T_OPEN_CURLY_BRACKET ], true)) {
                break;
            }

            $name = sprintf('%s%s', $name, $tokens[ $index ][ 'content' ]);

        }

        return trim($name);
    }

    private function isModelClassName(string $class, array $modelClasses, bool $isModelFile): bool
    {
        $class = strtolower(ltrim($class, '\\'));

        return isset($modelClasses[ $class ])
            || str_contains($class, '\\models\\')
            || ($isModelFile && !str_contains($class, '\\'))
            || ($isModelFile && in_array($class, [ 'self', 'static' ], true));
    }

    private function isObjectDatabaseOperation(File $phpcsFile, int $operator, string $method): bool
    {
        if (in_array($method, self::SAVE_METHODS, true)) {
            return true;
        }

        $tokens = $phpcsFile->getTokens();
        $receiver = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $operator - 1, null, true);

        if ($receiver === false || $tokens[ $receiver ][ 'code' ] !== T_VARIABLE) {
            return false;
        }

        $analysis = $this->analyseFile($phpcsFile);
        $variable = $tokens[ $receiver ][ 'content' ];

        return (
            isset($analysis[ 'builderVariables' ][ $variable ])
            && in_array($method, self::BUILDER_METHODS, true)
        ) || (
            isset($analysis[ 'modelVariables' ][ $variable ])
            && in_array($method, self::MODEL_WRITE_METHODS, true)
        );
    }

    private function isRepositoryFile(File $phpcsFile): bool
    {
        return str_ends_with(pathinfo($phpcsFile->getFilename(), PATHINFO_FILENAME), 'Repository');
    }

    private function isStaticDatabaseOperation(File $phpcsFile, int $operator, string $method): bool
    {
        $tokens = $phpcsFile->getTokens();
        $classIndex = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $operator - 1, null, true);

        if ($classIndex === false) {
            return false;
        }

        $class = $tokens[ $classIndex ][ 'content' ];
        $normalizedClass = strtolower(ltrim($class, '\\'));

        if (
            ($normalizedClass === 'db' || str_ends_with($normalizedClass, '\\facades\\db'))
            && in_array($method, self::DB_FACADE_METHODS, true)
        ) {
            return true;
        }

        $analysis = $this->analyseFile($phpcsFile);

        return $this->isModelClassName(
            class: $class,
            modelClasses: $analysis[ 'modelClasses' ],
            isModelFile: $analysis[ 'isModelFile' ],
        ) && in_array($method, self::MODEL_STATIC_METHODS, true);
    }
}
