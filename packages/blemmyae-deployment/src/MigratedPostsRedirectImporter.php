<?php

declare(strict_types=1);

namespace Cra\BlemmyaeDeployment;

use Red_Item;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

/**
 * Class which imports redirects for migrated posts.
 */
class MigratedPostsRedirectImporter
{
    /**
     * @var array[]
     */
    private array $sources;

    /**
     * @var object{id: string, post_type: string, slug: string, permalink: string}
     */
    private object $source;

    /**
     * @var object[]
     */
    private array $targets;

    /**
     * @var object{import_id: int, post_id: int, unique_key: string}
     */
    private object $target;

    private string $targetUrl;

    private int $count;

    private int $countAll;

    /**
     * Import redirects for migrated posts.
     *
     * @param string $sourcesFilePath
     * @param string $separator
     */
    public function execute(string $sourcesFilePath, string $separator = ','): void
    {
        $this->loadSources($sourcesFilePath, $separator);
        $this->loadTargets();
        $this->countAll = $this->count = count($this->targets);

        foreach ($this->targets as $item) {
            $this->updateRedirects($item);
        }

        wp_cache_flush();
    }

    /**
     * Load redirects.
     *
     * @param string $sourcesFilePath
     * @param string $separator
     */
    private function loadSources(string $sourcesFilePath, string $separator): void
    {
        $redirects = array_map(
            static fn(array $row) => [
                'id' => $row[0] ?? '',
                'post_type' => $row[1] ?? '',
                'slug' => $row[3] ?? '',
                'permalink' => $row[4] ?? '',
            ],
            Utils::importCsv($sourcesFilePath, $separator)
        );
        $this->sources = array_combine(
            array_column($redirects, 'id'),
            $redirects
        );
    }

    /**
     * Load migrated items.
     */
    private function loadTargets(): void
    {
        global $wpdb;
        $this->targets = $wpdb->get_results(
            'SELECT import_id, post_id, unique_key FROM wp_pmxi_posts'
        );
    }

    /**
     * Import redirects for the migrated item.
     *
     * @param object $target
     */
    private function updateRedirects(object $target): void
    {
        $this->target = $target;
        $this->count--;

        if (!in_array((int)$this->target->import_id, [2, 3, 4, 5, 6, 9, 10, 11], true)) {
            $this->log('Not mappable due to import ID.', 'skip');

            return;
        }

        if (!$this->determineTargetUrl()) {
            return;
        }

        $this->log(
            sprintf(
                'ImportID: %s %s ~> %s',
                $this->target->import_id,
                $this->target->unique_key,
                $this->target->post_id
            ),
            'info'
        );

        $this->deleteExistingSourceRedirects();
        $this->createNewSourceRedirects();
    }

    /**
     * Log message.
     *
     * @param string $message
     * @param string $type
     */
    private function log(string $message, string $type): void
    {
        Logger::log("[$this->countAll/$this->count]\t$message", $type);
    }

    /**
     * Determine target URL.
     *
     * @return bool Returns TRUE if source and target are workable.
     */
    private function determineTargetUrl(): bool
    {
        $this->source = (object)($this->sources[$this->target->unique_key] ?? []);
        if (empty($this->source->permalink)) {
            $this->log('Not mappable due to empty source URL.', 'skip');

            return false;
        }

        if (get_post_status($this->target->post_id) !== 'publish') {
            $this->log('Not mappable due to not published post.', 'skip');

            return false;
        }

        $targetUrl = get_permalink($this->target->post_id);
        if (is_wp_error($targetUrl)) {
            $this->log($targetUrl->get_error_message(), 'warning');

            return false;
        }
        if (!$targetUrl || str_contains($targetUrl, '/%editorial_type%/')) {
            $this->log("Invalid target URL: $targetUrl", 'warning');

            return false;
        }
        $this->targetUrl = $this->forceRelativeUrl($targetUrl);

        return true;
    }

    /**
     * Force relative URL.
     *
     * @param string $url
     *
     * @return string
     */
    private function forceRelativeUrl(string $url): string
    {
        return preg_replace('/^(http)?s?:?\/\/[^\/]*(\/?.*)$/i', '$2', $url);
    }

    /**
     * Delete existing redirects from the source.
     */
    private function deleteExistingSourceRedirects(): void
    {
        /** @phpstan-ignore-next-line */
        $redirects = Red_Item::get_for_matched_url($this->source->permalink);
        foreach ($redirects as $redirect) {
            if ($redirect->get_match($this->source->permalink, $this->source->permalink)) {
                $redirect->delete();
                $this->log("Deleted redirect for {$this->source->permalink}", 'delete');
            }
        }
    }

    /**
     * Create new redirects from the source.
     */
    private function createNewSourceRedirects(): void
    {
        $title = 'Generated redirect for migrated content. See BLEM-20 and BLEM-364 for details.';
        /** @phpstan-ignore-next-line */
        $redItem = Red_Item::create(
            [
                'url' => $this->source->permalink,
                'action_data' => ['url' => $this->targetUrl],
                'regex' => false,
                'group_id' => 1,
                'match_type' => 'url',
                'action_type' => 'url',
                'action_code' => 301,
                'title' => $title,
            ]
        );
        if (is_wp_error($redItem)) {
            $this->log($redItem->get_error_message(), 'warning');
        } else {
            $this->log(sprintf('%s ~> %s', $this->source->permalink, $this->targetUrl), 'info');
        }

        // Keeping this code as an example how to add regex redirects.
        //
        //        if ($this->source->post_type === 'post') {
        //            // e.g. /home/security-news/security-threats-of-pervasive-computing/
        //            $sourceParts = explode('/', untrailingslashit($this->source->permalink));
        //            if (count($sourceParts) <= 1) {
        //                return;
        //            }
        //
        //            // e.g. ^\/home\b.*?\/security-threats-of-pervasive-computing\/?$
        //            $sourceRegexUrl = '^\/' . $sourceParts[1] . '\b.*\/' . $this->source->slug . '\/?$';
        //            $redItem = Red_Item::create(
        //                [
        //                    'url' => $sourceRegexUrl,
        //                    'action_data' => ['url' => $this->targetUrl],
        //                    'regex' => true,
        //                    'match_data' => ['source' => ['flag_regex' => true]],
        //                    'group_id' => 1,
        //                    'match_type' => 'url',
        //                    'action_type' => 'url',
        //                    'action_code' => 301,
        //                    'title' => $title,
        //                ]
        //            );
        //            if (is_wp_error($redItem)) {
        //                $this->log($redItem->get_error_message(), 'warning');
        //            } else {
        //                $this->log(sprintf('%s ~> %s', $sourceRegexUrl, $this->targetUrl), 'info');
        //            }
        //        }
    }
}
