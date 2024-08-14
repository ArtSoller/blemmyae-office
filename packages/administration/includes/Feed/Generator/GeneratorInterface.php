<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Scm\Feed\Generator;

use WP_Term;

/**
 * Feed generator interface.
 */
interface GeneratorInterface
{
    /**
     * Generates Atom feed for the specified topic.
     *
     * @param WP_Term $term
     * @param string $app
     * @param int $daysOld Filter out editorials older than this value in days.
     *
     * @return string
     */
    public function generateFeedForTerm(WP_Term $term, string $app, int $daysOld = 2): string;
}
