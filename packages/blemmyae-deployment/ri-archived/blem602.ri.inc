<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 * @link https://cra.myjetbrains.com/youtrack/issue/BLEM-602/Delete-all-stogiegeeks-ppworks-content-from-Portal
 */

declare(strict_types=1);

use Cra\BlemmyaePpworks\Ppworks;
use Cra\BlemmyaePpworks\PpworksEpisodeCT;
use Cra\BlemmyaePpworks\PpworksSegmentCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class StogieGeeksCleanup
{
    private const STOGIE_GEEKS_SHOW_SLUG = 'stogie-geeks';

    /**
     * Delete all 'Stogie Geeks' content.
     *
     * @return void
     */
    public function deleteAllContent(): void
    {
        // The order matters here. Also, we cannot proceed on an error to the next step.
        try {
            $this->deleteSegments();
            $this->deleteEpisodes();
            $this->deleteShow();
        } catch (Exception $exception) {
            $this->error("==== ERROR ==== {$exception->getMessage()}");
        }
    }

    /**
     * Delete 'Stogie Geeks' segments.
     *
     * @return void
     * @throws Exception
     */
    private function deleteSegments(): void
    {
        $this->deletePostsOfType(PpworksSegmentCT::POST_TYPE);
    }

    /**
     * Delete 'Stogie Geeks' episodes.
     *
     * @return void
     * @throws Exception
     */
    private function deleteEpisodes(): void
    {
        $this->deletePostsOfType(PpworksEpisodeCT::POST_TYPE);
    }

    /**
     * Delete all 'Stogie Geeks' posts of a type.
     *
     * @param string $postType
     *
     * @return void
     * @throws Exception
     */
    private function deletePostsOfType(string $postType): void
    {
        $query = new WP_Query([
            'nopaging' => true,
            'post_type' => $postType,
            'post_status' => ['any', Ppworks::POST_STATUS__UNFINISHED],
            'tax_query' => [
                [
                    'taxonomy' => Ppworks::TAXONOMY__SHOW,
                    'field' => 'slug',
                    'terms' => self::STOGIE_GEEKS_SHOW_SLUG,
                ],
            ],
        ]);
        /** @var WP_Post $post */
        foreach ($query->posts as $post) {
            $link = get_permalink($post);
            $postInfo = "'Stogie Geeks' $postType '$post->post_title' (ID: $post->ID)";
            if (!wp_delete_post($post->ID, true)) {
                throw new Exception("Cannot delete $postInfo");
            }
            $this->success("Deleted $postInfo.");
            $this->createRedirect($link);
        }
    }

    /**
     * Delete 'Stogie Geeks' show term.
     *
     * @return void
     * @throws Exception
     */
    private function deleteShow(): void
    {
        $term = get_term_by('slug', self::STOGIE_GEEKS_SHOW_SLUG, Ppworks::TAXONOMY__SHOW);
        if (!($term instanceof WP_Term)) {
            // This is an acceptable situation. Just need to warn about it.
            $this->success('"Stogie Geeks" Show term is already deleted.');
            return;
        }

        $link = get_term_link($term, Ppworks::TAXONOMY__SHOW);

        $result = wp_delete_term($term->term_id, Ppworks::TAXONOMY__SHOW);
        if ($result instanceof WP_Error) {
            throw new Exception($result->get_error_message());
        }

        $this->success("Deleted 'Stogie Geeks' Show term.");
        $this->createRedirect($link);
    }

    /**
     * Create redirect for 'Stogie Geeks' content.
     *
     * @param string $sourceUrl
     *
     * @throws Exception
     */
    private function createRedirect(string $sourceUrl): void
    {
        $sourceUri = untrailingslashit(wp_parse_url($sourceUrl, PHP_URL_PATH));
        $targetUri = '/security-weekly';
        /** @phpstan-ignore-next-line */
        $result = Red_Item::create([
            'url' => $sourceUri,
            'action_data' => ['url' => $targetUri],
            'group_id' => 1,
            'match_type' => 'url',
            'action_type' => 'url',
            'action_code' => 303,
        ]);
        if ($result instanceof WP_Error) {
            throw new Exception($result->get_error_message());
        }
        $this->success("Created redirect from $sourceUri to $targetUri");
    }

    /**
     * Log error message.
     *
     * @param string $message
     *
     * @return void
     */
    private function error(string $message): void
    {
        Logger::log($message, 'error');
    }

    /**
     * Log success message.
     *
     * @param string $message
     *
     * @return void
     */
    private function success(string $message): void
    {
        Logger::log($message, 'success');
    }
}

/**
 * Set long-running RI as completed so it can be manually triggered after deployment.
 *
 * @return void
 */
function blem602_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('blem602_blemmyae_deployment_ri_002', true);
}

/**
 * Delete "Stogie Geeks" content.
 *
 * It includes Show term, and Segment & Episode posts.
 *
 * @return void
 */
function blem602_blemmyae_deployment_ri_002(): void
{
    (new StogieGeeksCleanup())->deleteAllContent();
}
