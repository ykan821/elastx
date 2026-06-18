<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder($finder)
    ->setUnsupportedPhpVersionAllowed(true);
