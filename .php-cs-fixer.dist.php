<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'array_indentation' => true,
        'method_chaining_indentation' => true,
        'phpdoc_align' => false,
        'no_superfluous_phpdoc_tags' => false,
        'array_syntax' => ['syntax' => 'short'],
        'phpdoc_separation' => [
            'skip_unlisted_annotations' => true,
        ]
    ])
    ->setFinder($finder);
