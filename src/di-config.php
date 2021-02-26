<?php

use PhpRemix\Config\Config;

return [
    Config::class => \DI\create()
        ->constructor(),
    
    'config' => \DI\get(Config::class)
];