<?php

// src/Twig/AppExtension.php
// https://ourcodeworld.com/articles/read/1177/how-to-retrieve-env-variables-directly-from-a-twig-view-in-symfony-5

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_env', [$this, 'getEnvironmentVariable']),
        ];
    }
    
    /**
     * Return the value of the requested environment variable.
     * 
     * @param string $varname
     * @return string
     */
    public function getEnvironmentVariable(string $varname) : string
    {
        return $_ENV[$varname];
    }
}