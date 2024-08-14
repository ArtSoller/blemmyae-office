<?php

/**
 * Collection class, defines Ad Slot Gutenberg block
 *
 * @package   Cra\BlemmyaeAds
 * @author    Konstantin Gusev <guvkon.net@icloud.com>
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeAds;

use Scm\Entity\CustomGutenbergBlock;
use Scm\Entity\CustomImport;

/**
 * Collection class.
 *
 * @psalm-suppress UndefinedClass
 */
class Ads extends CustomGutenbergBlock
{
    public function registerOptionsPage(bool $unregister = false): bool
    {
        if (!class_exists(CustomImport::class)) {
            return false;
        }

        return CustomImport::process(
            $this->pluginDirPath . '/config/options_page',
            'options_page',
            $unregister
        );
    }
}
