<?php

/**
 * Redirects – Handy Custom Functions.
 */

declare(strict_types=1);

namespace Scm\Tools;

use Exception;
use Red_Item;

class Redirects
{
    /** @phpstan-ignore-next-line */
    public static function findRedirect(string $uri): ?Red_Item
    {
        /** @phpstan-ignore-next-line */
        foreach (Red_Item::get_for_matched_url($uri) as $redItem) {
            if ($redItem->get_action_type() === 'url' && $redItem->get_match($uri)) {
                return $redItem;
            }
        }
        return null;
    }

    /**
     * Upsert redirect.
     *
     * @param string $sourceUri
     * @param string $targetUri
     *
     * @return void
     * @throws Exception
     */
    public static function upsertRedirect(string $sourceUri, string $targetUri): void
    {
        // Find redirects.
        $redItem = self::findRedirect($sourceUri);

        if ($redItem && ($redItem->get_action_data() !== $targetUri || $targetUri === $sourceUri)) {
            $redItem->delete();
            $redItem = null;
        }

        if (!$redItem && $sourceUri !== $targetUri) {
            /** @phpstan-ignore-next-line */
            $redItem = Red_Item::create([
                'url' => $sourceUri,
                'action_data' => ['url' => $targetUri],
                'group_id' => 1,
                'match_type' => 'url',
                'action_type' => 'url',
                'action_code' => 301,
            ]);

            if (is_wp_error($redItem)) {
                throw new Exception('Upsert Redirect - ' . $redItem->get_error_message());
            }
        }
    }
}
