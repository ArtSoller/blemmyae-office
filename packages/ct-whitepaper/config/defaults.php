<?php

/**
 * Plugin configuration file
 *
 * @package   Cra\CtWhitepaper
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\CtWhitepaper;

$ctWhitepaperPlugin = [
    'textdomain' => 'ct-whitepaper',
    'languages_dir' => 'languages',
];

return [
    'Cra' => [
        'CtWhitepaper' => [
            'Plugin' => $ctWhitepaperPlugin,
        ],
    ],
];
