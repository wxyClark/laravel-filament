<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$rules = [
    '@PSR12' => true,
    '@PSR12:risky' => true,
    '@PHP82Migration' => true,
    'array_syntax' => ['syntax' => 'short'],
    'no_unused_imports' => true,
    'ordered_imports' => [
        'sort_algorithm' => 'alpha',
        'imports_order' => ['class', 'function', 'const'],
    ],
    'single_trait_insert_per_statement' => true,
    'declare_strict_types' => true,
    'strict_param' => true,
    'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
    'phpdoc_summary' => false,
    'phpdoc_to_comment' => ['deprecated_by_default' => true],
    'cast_spaces' => ['space' => 'single'],
    'concat_space' => ['spacing' => 'one'],
    'types_spaces' => ['space' => 'single'],
    'binary_operator' => ['default' => 'space'],
    'class_attributes_separation' => [
        'elements' => [
            'const' => 'one',
            'method' => 'one',
            'property' => 'one',
            'trait_import' => 'none',
            'case' => 'none',
        ],
    ],
    'trailing_comma_in_multiline' => ['elements' => ['arrays', 'parameters', 'arguments']],
    'no_empty_statement' => true,
    'no_extra_blank_lines' => true,
    'no_whitespace_in_blank_line' => true,
    'ordered_class_elements' => [
        'order' => [
            'use_trait',
            'case',
            'constant',
            'constant_public',
            'constant_protected',
            'constant_private',
            'property_public',
            'property_protected',
            'property_private',
            'construct',
            'destruct',
            'magic',
            'phpunit',
        ],
        'sort_algorithm' => 'none',
    ],
];

$finder = Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/database',
        __DIR__ . '/tests',
    ])
    ->exclude([
        'vendor',
        'storage',
        'bootstrap/cache',
    ])
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config('laravel-filament'))
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setFinder($finder)
    ->setUsingCache(true);
