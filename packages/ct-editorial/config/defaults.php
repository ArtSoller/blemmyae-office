<?php

/**
 * Plugin configuration file
 *
 * @package   Cra\CtEditorial
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\CtEditorial;

$ctEditorialPlugin = [
    'textdomain' => 'ct-editorial',
    'languages_dir' => 'languages',
];

return [
    'Cra' => [
        'CtEditorial' => [
            'Plugin' => $ctEditorialPlugin,
        ],
    ],
];
