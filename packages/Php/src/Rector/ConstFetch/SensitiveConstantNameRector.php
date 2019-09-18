<?php declare(strict_types=1);

namespace Rector\Php\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/case_insensitive_constant_deprecation
 * @see \Rector\Php\Tests\Rector\ConstFetch\SensitiveConstantNameRector\SensitiveConstantNameRectorTest
 */
final class SensitiveConstantNameRector extends AbstractRector
{
    /**
     * @see http://php.net/manual/en/reserved.constants.php
     * @var string[]
     */
    private $phpReservedConstants = [
        'PHP_VERSION',
        'PHP_MAJOR_VERSION',
        'PHP_MINOR_VERSION',
        'PHP_RELEASE_VERSION',
        'PHP_VERSION_ID',
        'PHP_EXTRA_VERSION',
        'PHP_ZTS',
        'PHP_DEBUG',
        'PHP_MAXPATHLEN',
        'PHP_OS',
        'PHP_OS_FAMILY',
        'PHP_SAPI',
        'PHP_EOL',
        'PHP_INT_MAX',
        'PHP_INT_MIN',
        'PHP_INT_SIZE',
        'PHP_FLOAT_DIG',
        'PHP_FLOAT_EPSILON',
        'PHP_FLOAT_MIN',
        'PHP_FLOAT_MAX',
        'DEFAULT_INCLUDE_PATH',
        'PEAR_INSTALL_DIR',
        'PEAR_EXTENSION_DIR',
        'PHP_EXTENSION_DIR',
        'PHP_PREFIX',
        'PHP_BINDIR',
        'PHP_BINARY',
        'PHP_MANDIR',
        'PHP_LIBDIR',
        'PHP_DATADIR',
        'PHP_SYSCONFDIR',
        'PHP_LOCALSTATEDIR',
        'PHP_CONFIG_FILE_PATH',
        'PHP_CONFIG_FILE_SCAN_DIR',
        'PHP_SHLIB_SUFFIX',
        'PHP_FD_SETSIZE',
        'E_ERROR',
        'E_WARNING',
        'E_PARSE',
        'E_NOTICE',
        'E_CORE_ERROR',
        'E_CORE_WARNING',
        'E_COMPILE_ERROR',
        'E_COMPILE_WARNING',
        'E_USER_ERROR',
        'E_USER_WARNING',
        'E_USER_NOTICE',
        'E_RECOVERABLE_ERROR',
        'E_DEPRECATED',
        'E_USER_DEPRECATED',
        'E_ALL',
        'E_STRICT',
        '__COMPILER_HALT_OFFSET__',
        'TRUE',
        'FALSE',
        'NULL',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes case insensitive constants to sensitive ones.',
            [
                new CodeSample(
                    <<<'PHP'
define('FOO', 42, true);
var_dump(FOO);
var_dump(foo);
PHP
                    ,
                    <<<'PHP'
define('FOO', 42, true);
var_dump(FOO);
var_dump(FOO);
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ConstFetch::class];
    }

    /**
     * @param ConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        $constantName = $this->getName($node);
        if ($constantName === null) {
            return null;
        }

        $uppercasedConstantName = strtoupper($constantName);

        // is system constant?
        if (in_array($uppercasedConstantName, $this->phpReservedConstants, true)) {
            return null;
        }

        // constant is defined in current lower/upper case
        if (defined($constantName)) {
            return null;
        }

        // is uppercase, all good
        if ($constantName === $uppercasedConstantName) {
            return null;
        }

        $node->name = new FullyQualified($uppercasedConstantName);

        return $node;
    }
}
