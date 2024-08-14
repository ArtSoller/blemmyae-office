<?php

/**
 * Plugin configuration file
 *
 * @package   Cra\Thumbor
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\Thumbor;

$craThumborPlugin = [
    'textdomain' => 'cra-thumbor',
    'languages_dir' => 'languages',
];

$craThumborSettings = [];

return [
    'Cra' => [
        'Thumbor' => [
            'Plugin' => $craThumborPlugin,
            'Settings' => $craThumborSettings,
        ],
    ],
];
