<?php

/**
 * @author  Konstantin Gusev <konstantin.gusev@cyberriskalliance.com>
 * @license proprietary
 */

namespace Cra\WebhookConsumer\Mapper;

use Exception;
use Red_Item;
use Scm\Tools\WpCore;

/**
 * Trait which provides redirection related methods.
 */
trait RedirectTrait
{
    /**
     * Upsert redirect.
     *
     * @param string $sourceUri
     * @param string $targetUri
     *
     * @return void
     * @throws Exception
     */
    protected function upsertRedirect(string $sourceUri, string $targetUri): void
    {
        if ($this->redirectionMissing()) {
            return;
        }
        $this->requireRedirectionFiles();

        $redItem = $this->findRedirect($sourceUri);
        // @phpstan-ignore-next-line
        if ($redItem && ($redItem->get_action_data() !== $targetUri || $targetUri === $sourceUri)) {
            $redItem->delete();  // @phpstan-ignore-line
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
                throw new Exception('upsertShortUrlRedirect - ' . $redItem->get_error_message());
            }
        }
    }

    /**
     * Remove redirects from the post URI.
     *
     * @param int $postId
     *
     * @return void
     * @throws Exception
     */
    protected function removeRedirectsFromPostUri(int $postId): void
    {
        if ($this->redirectionMissing()) {
            return;
        }
        $this->requireRedirectionFiles();

        $link = WpCore::getPostRelativePermalink($postId);
        if ($redItem = $this->findRedirect($link)) {
            $redItem->delete();  // @phpstan-ignore-line
        }
    }

    /**
     * Is Redirection plugin missing?
     *
     * @return bool
     */
    private function redirectionMissing(): bool
    {
        return !defined('REDIRECTION_FILE') && !REDIRECTION_FILE;  // @phpstan-ignore-line
    }

    /**
     * Require necessary Redirection files.
     *
     * @return void
     */
    private function requireRedirectionFiles(): void
    {
        // Thanks, WordPress.
        // @phpstan-ignore-next-line
        require_once plugin_dir_path(REDIRECTION_FILE) . 'models/group.php';
    }

    /**
     * Find redirect for the specified URI.
     *
     * @param string $uri
     *
     * @return Red_Item|null
     */
    /** @phpstan-ignore-next-line */
    private function findRedirect(string $uri): ?Red_Item
    {
        /** @phpstan-ignore-next-line */
        foreach (Red_Item::get_for_matched_url($uri) as $redItem) {
            if ($redItem->get_action_type() === 'url' && $redItem->get_match($uri)) {
                return $redItem;
            }
        }

        return null;
    }
}
