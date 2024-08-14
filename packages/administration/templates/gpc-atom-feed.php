<?php // phpcs:ignore PSR1.Files.SideEffects.FoundWithSymbols

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

use Scm\Feed\Generator\PublisherCenterAtom;

if (!defined('ABSPATH')) {
    exit;
}

if (!isset($args['term']) || !($args['term'] instanceof \WP_Term)) {
    exit;
}

header('Content-Type: application/atom+xml; charset=UTF-8');

if (defined('WP_RSS_FEED_CACHE') && WP_RSS_FEED_CACHE) {
    header('Expires: Thu, 20 Dec 1990 14:00:00 GMT');
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    define('DONOTCACHEPAGE', true);
}

$daysOld = defined('CRA_FEED_DAYS_OLD') && CRA_FEED_DAYS_OLD ?
    CRA_FEED_DAYS_OLD : 10;

echo (new PublisherCenterAtom())->generateFeedForTerm($args['term'], $args['app'], $daysOld);
