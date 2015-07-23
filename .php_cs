<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__ . '/src');

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers(['concat_with_spaces', 'short_array_syntax', 'ordered_use', '-pre_increment'])
    ->finder($finder)
    ->setUsingCache(true);
