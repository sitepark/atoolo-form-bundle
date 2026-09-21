<?php

declare(strict_types=1);

$finder = (new \PhpCsFixer\Finder())
    ->in(__DIR__ . '/src');

// the tests arrive with feature/initial-implementation; without this the
// finder throws on main and fails the whole check
if (is_dir(__DIR__ . '/test')) {
    $finder->in(__DIR__ . '/test');
}

return (new \PhpCsFixer\Config())
    ->setCacheFile('var/cache/php-cs-fixer')
    ->setFinder($finder)
    ->setRules(['@PER-CS' => true]);
