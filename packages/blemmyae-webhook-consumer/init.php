<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

/**
 * Initialise the plugin
 *
 * This file can use syntax from the required level of PHP or later.
 *
 * @package   Cra\WebhookConsumer
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2020 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   GPL-2.0-or-later
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer;

use BrightNucleus\Config\ConfigFactory;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!defined('WEBHOOK_CONSUMER_DIR')) {
    define('WEBHOOK_CONSUMER_DIR', plugin_dir_path(__FILE__));
}

if (!defined('WEBHOOK_CONSUMER_URL')) {
    define('WEBHOOK_CONSUMER_URL', plugin_dir_url(__FILE__));
}

/*
 * Initialize the plugin.
 */
$GLOBALS['webhook_consumer'] = new Plugin(
    ConfigFactory::create(__DIR__ . '/config/defaults.php')->getSubConfig('Cra\WebhookConsumer')
);
$GLOBALS['webhook_consumer']->run();
