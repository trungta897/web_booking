<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Preset
    |--------------------------------------------------------------------------
    |
    | This option controls the default preset that will be used by PHP Insights
    | to make your code reliable, simple, and clean. However, you can always
    | adjust the insight behavior using the configuration below.
    |
    */

    'preset' => 'laravel',

    /*
    |--------------------------------------------------------------------------
    | IDE
    |--------------------------------------------------------------------------
    |
    | This options allow to add hyperlinks in your terminal to quickly open
    | files in your favorite IDE while browsing your PhpInsights report.
    |
    */

    'ide' => 'vscode',

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may adjust all the various `Insights` that will be used by PHP
    | Insights. This is the place to dig deep into the individual sniffs and
    | customize the exact sniffs that are used.
    |
    */

    'exclude' => [
        'app/helpers.php',
        'vendor',
        'node_modules',
        'bootstrap/cache',
        'storage',
        'resources/views/vendor',
    ],

    'add' => [
        // Add additional insights here
    ],

    'remove' => [
        // Remove specific insights that don't fit your project
        \SlevomatCodingStandard\Sniffs\TypeHints\DisallowMixedTypeHintSniff::class,
        \SlevomatCodingStandard\Sniffs\Classes\ForbiddenPublicPropertySniff::class,
        \SlevomatCodingStandard\Sniffs\TypeHints\DisallowArrayTypeHintSyntaxSniff::class,
        \SlevomatCodingStandard\Sniffs\Functions\StaticClosureSniff::class,
        \NunoMaduro\PhpInsights\Domain\Insights\ForbiddenDefineFunctions::class,
        \NunoMaduro\PhpInsights\Domain\Insights\ForbiddenFinalClasses::class,
        \SlevomatCodingStandard\Sniffs\Classes\SuperfluousAbstractClassNamingSniff::class,
        \SlevomatCodingStandard\Sniffs\Classes\SuperfluousInterfaceNamingSniff::class,
        \PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer::class,
        \PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer::class,
        \PhpCsFixer\Fixer\Operator\IncrementStyleFixer::class,
        \SlevomatCodingStandard\Sniffs\ControlStructures\DisallowShortTernaryOperatorSniff::class,
        \SlevomatCodingStandard\Sniffs\ControlStructures\DisallowYodaComparisonSniff::class,
    ],

    'config' => [
        \PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff::class => [
            'lineLimit' => 120,
            'absoluteLineLimit' => 160,
            'ignoreComments' => false,
        ],

        \SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff::class => [
            'maxLinesLength' => 50,
        ],

        \SlevomatCodingStandard\Sniffs\Files\TypeNameMatchesFileNameSniff::class => [
            'rootNamespaces' => [
                'app' => 'App',
                'tests' => 'Tests',
                'database/factories' => 'Database\Factories',
                'database/seeders' => 'Database\Seeders',
            ],
        ],

        \SlevomatCodingStandard\Sniffs\Namespaces\UnusedUsesSniff::class => [
            'searchAnnotations' => true,
        ],

        \PHP_CodeSniffer\Standards\Generic\Sniffs\Formatting\SpaceAfterCastSniff::class => [
            'spacing' => 1,
        ],

        \SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff::class => [
            'enableNativeTypeHint' => false,
        ],

        \SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff::class => [
            'enableObjectTypeHint' => false,
            'enableMixedTypeHint' => false,
            'enableUnionTypeHint' => false,
        ],

        \SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff::class => [
            'enableObjectTypeHint' => false,
            'enableStaticTypeHint' => false,
            'enableMixedTypeHint' => false,
            'enableUnionTypeHint' => false,
        ],

        \SlevomatCodingStandard\Sniffs\Commenting\InlineDocCommentDeclarationSniff::class => [
            'allowDocCommentAboveReturn' => true,
        ],

        \SlevomatCodingStandard\Sniffs\ControlStructures\RequireYodaComparisonSniff::class => [
            'alwaysVariableOnRight' => false,
        ],

        \NunoMaduro\PhpInsights\Domain\Insights\CyclomaticComplexityIsHigh::class => [
            'maxComplexity' => 10,
        ],

        \NunoMaduro\PhpInsights\Domain\Insights\ForbiddenSecurityIssues::class => [
            'forbiddenFunctions' => [
                'dd',
                'dump',
                'eval',
                'exec',
                'passthru',
                'shell_exec',
                'system',
            ],
        ],

        \PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\UnusedFunctionParameterSniff::class => [
            'ignoreTypeHints' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Requirements
    |--------------------------------------------------------------------------
    |
    | Here you may define a level you want to reach per `Insights` category.
    | When a score is lower than the minimum level defined, then an error
    | code will be returned. This is optional and individually defined.
    |
    */

    'requirements' => [
        'min-quality' => 85,
        'min-complexity' => 85,
        'min-architecture' => 75,
        'min-style' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | Threads
    |--------------------------------------------------------------------------
    |
    | Here you may adjust how many threads (core) PHPInsights can use to run
    | the analysis. This is optional, should be a positive integer and
    | defaults to the number of cores available in your system.
    |
    */

    'threads' => null,

];
