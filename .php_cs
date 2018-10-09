<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        '@PHPUnit60Migration:risky' => true,
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => true,
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'increment_style' => ['style' => 'post'],
        'is_null' => ['use_yoda_style' => false],
        'list_syntax' => ['syntax' => 'long'],
        'method_argument_space' => ['ensure_fully_multiline' => true],
        'method_chaining_indentation' => true,
        'modernize_types_casting' => true,
        'no_multiline_whitespace_before_semicolons' => true,
        'no_superfluous_elseif' => true,
        'no_superfluous_phpdoc_tags' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => true,
        'phpdoc_align' => false,
        'phpdoc_order' => true,
        'php_unit_construct' => true,
        'php_unit_dedicate_assert' => true,
        'return_assignment' => true,
        'single_line_comment_style' => true,
        'ternary_to_null_coalescing' => true,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'void_return' => false,
    ])
    ->setFinder($finder)
    ->setUsingCache(true)
    ->setRiskyAllowed(true);
