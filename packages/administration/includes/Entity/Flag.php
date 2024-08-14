<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Scm\Entity;

/**
 * Class which handles everything related to Utility taxonomy.
 */
class Flag
{
    public const TAXONOMY = 'flag';

    public const FIELD_FLAGS = 'field_625e3ee9b4a20';

    public const TAXONOMY_TERM__SHOW_IN_FEEDS = 'Show in Feeds';

    public function __construct()
    {
        $this->initHooks();
    }

    private function initHooks()
    {
        // Extend here.
    }
}
