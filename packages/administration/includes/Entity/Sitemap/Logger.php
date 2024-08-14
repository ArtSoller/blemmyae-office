<?php

declare(strict_types=1);

namespace Scm\Entity\Sitemap;

use Scm\Tools\Logger as ScmLogger;

/**
 * {@inheritdoc}
 */
class Logger extends ScmLogger
{
    /**
     * {@inheritdoc}
     */
    public static function log(string $message, string $type)
    {
        parent::log("[sitemap:generator] $message", $type);
    }
}
