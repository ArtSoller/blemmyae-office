<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Scm\Tools;

use Exception;
use Scm\Entity\Media;
use WP_Error;
use WP_Post;
use WP_Query;
use WP_Term;

use function get_post;
use function get_terms;
use function update_field;
use function wp_insert_post;
use function wp_set_post_terms;

/**
 * WordPress core functions with a bit of syntax sugar.
 */
class WpCore
{
    public const POST_STATUS_DRAFT = 'draft';
    public const POST_STATUS_PUBLISH = 'publish';
    // @phpstan-ignore-next-line
    public const MINUTE_IN_SECONDS = MINUTE_IN_SECONDS;
    // @phpstan-ignore-next-line
    public const HOUR_IN_SECONDS = HOUR_IN_SECONDS;
    // @phpstan-ignore-next-line
    public const DAY_IN_SECONDS = DAY_IN_SECONDS;
    // @phpstan-ignore-next-line
    public const ARRAY_N = ARRAY_N;
    // @phpstan-ignore-next-line
    public const OBJECT = OBJECT;
    // @phpstan-ignore-next-line
    public const ABSPATH = ABSPATH;

    /**
     * Search WP post ID.
     *
     * @param array $args
     *
     * @return int
     * @see WP_Query
     * @phpstan-ignore-next-line
     */
    public static function searchPostId(array $args): int
    {
        $args['posts_per_page'] = 1;
        $query = new WP_Query($args);
        $post = $query->have_posts() ? $query->next_post() : null;

        /** @noinspection PhpCastIsUnnecessaryInspection */
        return $post ? (int)$post->ID : 0;
    }

    /**
     * Retrieves post data given a post ID or post object.
     *
     * See sanitize_post() for optional $filter values. Also, the parameter
     * `$post`, must be given as a variable, since it is passed by reference.
     *
     * @param int|WP_Post|null $id Optional. Post ID or post object. `null`, `false`, `0` and other PHP falsey values
     *                             return the current global post inside the loop. A numerically valid post ID that.
     * @param string $filter Optional. Type of filter to apply. Accepts 'raw', 'edit', 'db',
     *                       or 'display'. Default 'raw'.
     *
     * @return WP_Post
     * @throws Exception
     * @see get_post()
     */
    public static function getPost(int|WP_Post|null $id, string $filter = 'raw'): WP_Post
    {
        $post = get_post($id, OBJECT, $filter);  // @phpstan-ignore-line
        if (!($post instanceof WP_Post)) {
            $postId = $id instanceof WP_Post ? $id->ID : $id;
            throw new Exception("WpCore – Post not found with id: $postId!");
        }
        return $post;
    }

    /**
     * Insert WP post.
     *
     * Do not use this for updating posts!
     *
     * @param array{
     *    'ID'?: int,
     *    'post_author'?: int,
     *    'post_date'?: string,
     *    'post_date_gmt'?: string,
     *    'post_content'?: string,
     *    'post_content_filtered'?: string,
     *    'post_title'?: string,
     *    'post_excerpt'?: string,
     *    'post_status'?: string,
     *    'post_type'?: string,
     *    'post_name'?: string,
     * } $data
     *
     * @param bool $fireAfterHooks
     *
     * @return WP_Post
     * @throws Exception
     * @see wp_insert_post
     */
    public static function insertPost(array $data, bool $fireAfterHooks = true): WP_Post
    {
        $postId = wp_insert_post($data, true, $fireAfterHooks);
        if ($postId instanceof WP_Error) {
            throw new Exception("WpCore - {$postId->get_error_message()}");
        }
        return self::getPost($postId);
    }

    /**
     * Update WP post.
     *
     * @param array{
     *    'ID'?: int,
     *    'post_author'?: int,
     *    'post_date'?: string,
     *    'post_date_gmt'?: string,
     *    'post_content'?: string,
     *    'post_content_filtered'?: string,
     *    'post_title'?: string,
     *    'post_excerpt'?: string,
     *    'post_status'?: string,
     *    'post_type'?: string,
     *    'post_name'?: string,
     * } $data
     *
     * @param bool $fireAfterHooks
     *
     * @return WP_Post
     * @throws Exception
     * @see wp_update_post
     */
    public static function updatePost(array $data, bool $fireAfterHooks = true): WP_Post
    {
        $postId = wp_update_post($data, true, $fireAfterHooks);
        if ($postId instanceof WP_Error) {
            throw new Exception("WpCore - {$postId->get_error_message()}");
        }
        return self::getPost($postId);
    }

    /**
     * Update WP post status.
     *
     * @param int $postId
     * @param string $status
     *
     * @return WP_Post
     * @throws Exception
     */
    public static function updatePostStatus(int $postId, string $status): WP_Post
    {
        return self::updatePost(['ID' => $postId, 'post_status' => $status]);
    }

    /**
     * Delete WP post.
     *
     * @param int $postId
     * @param bool $forceDelete
     *
     * @throws Exception
     */
    public static function deletePost(int $postId = 0, bool $forceDelete = false): void
    {
        if (!wp_delete_post($postId, $forceDelete)) {
            throw new Exception("WpCore - $postId could not be deleted");
        }
    }

    /**
     * Get term by ID.
     *
     * Throws an exception if missing.
     *
     * @param string $taxonomy
     * @param int $termId
     *
     * @return WP_Term
     * @throws Exception
     */
    public static function getTermById(string $taxonomy, int $termId): WP_Term
    {
        $term = get_term($termId, $taxonomy, OBJECT, 'db');  // @phpstan-ignore-line
        if (is_wp_error($term)) {
            throw new Exception("WpCore - {$term->get_error_message()}");
        }
        if (!($term instanceof WP_Term)) {
            throw new Exception("WpCore - Unable to get term by ID ($termId, $taxonomy).");
        }
        return $term;
    }

    /**
     * Get term by name.
     *
     * @param string $taxonomy
     * @param string $name
     * @param bool $createIfMissing If term doesn't exist create a new one. Otherwise, it throws an error.
     *
     * @return WP_Term
     * @throws Exception
     */
    public static function getTermByName(string $taxonomy, string $name, bool $createIfMissing = false): WP_Term
    {
        $term = get_term_by('name', $name, $taxonomy);
        if ($term instanceof WP_Term) {
            return $term;
        }

        if (!$createIfMissing) {
            throw new Exception("WpCore - Taxonomy term $name could not be created!");
        }

        $result = wp_insert_term($name, $taxonomy);
        if (is_wp_error($result)) {
            $termId = $result->get_error_data('term_exists');
            if ($termId) {
                return self::getTermById($taxonomy, (int)$termId);
            }
            throw new Exception($result->get_error_message());
        }

        $termId = $result['term_id'];
        if (!$termId) {
            throw new Exception("Unable to create taxonomy term $name for $taxonomy!");
        }

        return self::getTermById($taxonomy, $termId);
    }

    /**
     * Get all terms for the taxonomy.
     *
     * @return WP_Term[]
     * @throws Exception
     */
    public static function getTerms(string $taxonomy): array
    {
        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
        if (is_wp_error($terms)) {
            throw new Exception("WpCore - {$terms->get_error_message()}");
        }
        // @phpstan-ignore-next-line
        return $terms;
    }

    /**
     * Set terms on post. Optionally set ACF field as well.
     *
     * @param string $taxonomy Taxonomy name.
     * @param int|string|string[]|int[]|null $terms Term ID or IDs.
     *                          Pass an empty string to remove $taxonomy terms from the post.
     * @param int $postId
     * @param string|null $acfField ACF field machine name.
     *
     * @return int[]
     * @throws Exception
     * @see wp_set_post_terms()
     */
    public static function setPostTerms(
        string $taxonomy,
        int|string|array|null $terms,
        int $postId,
        ?string $acfField = null
    ): array {
        // Update acf field.
        if ($acfField && $fieldSettings = get_field_object($acfField)) {
            // If field is single-value => we need to save Term instead of Array of terms.
            $isMultipleValueField = !empty($fieldSettings['multiple']) ||
                $fieldSettings['field_type'] === 'checkbox';

            if (!$isMultipleValueField && is_array($terms)) {
                $terms = !empty($terms) ? reset($terms) : null;
            }

            update_field($acfField, $terms, $postId);
        }

        if (!is_taxonomy_hierarchical($taxonomy) && !empty($terms)) {
            // @phpstan-ignore-next-line
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'include' => $terms,
                'fields' => 'slugs',
            ]);
            if (is_wp_error($terms)) {
                throw new Exception('WpCore - Error retrieving non-hierarchical terms.');
            }
        }

        // wp_set_post_terms does not support null => use '' instead.
        // @phpstan-ignore-next-line
        $affectedTermIds = wp_set_post_terms($postId, $terms ?? '', $taxonomy);
        if (is_wp_error($affectedTermIds)) {
            throw new Exception("WpCore - {$affectedTermIds->get_error_message()}");
        }

        return $affectedTermIds ?: [];
    }

    /**
     * @param int $term
     * @param string $taxonomy
     * @param array{'default'?: int, 'force_default'?: bool}|string $args
     *
     * @return void
     * @throws Exception
     */
    public static function deleteTerm(int $term, string $taxonomy, array|string $args = []): void
    {
        // @phpstan-ignore-next-line
        $response = wp_delete_term($term, $taxonomy, $args);
        if (is_wp_error($response)) {
            $error = $response->get_error_message();
            throw new Exception("WpCore - unable to delete term $term (from $taxonomy). $error");
        }
    }

    /**
     * Upsert media by URL.
     *
     * @param string $url
     * @param string $description
     *
     * @return int
     * @throws Exception
     */
    public static function upsertMediaByUrl(string $url, string $description = ''): int
    {
        $query = new WP_Query([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'original_source',
                    'value' => $url,
                ],
            ],
        ]);
        $attachment = $query->have_posts() ? $query->next_post() : null;
        if ($attachment) {
            /** @noinspection PhpCastIsUnnecessaryInspection */
            return (int)$attachment->ID;
        }

        $attachmentId = WpCore::mediaHandleSideload($url, $description);
        update_field(Media::FIELD__ORIGINAL_SOURCE, $url, $attachmentId);

        return $attachmentId;
    }

    /**
     * Create image attachment from a local file or a URL.
     *
     * @param string $filePath
     * @param string $description
     * @param int $postId Optional. The post ID the media is associated with.
     * @param array $postData Optional. Post data to override. Default empty array.
     *
     * @return int
     * @throws Exception
     * @see \media_handle_sideload()
     * @phpstan-ignore-next-line
     */
    public static function mediaHandleSideload(
        string $filePath,
        string $description = '',
        int $postId = 0,
        array $postData = []
    ): int {
        require_once ABSPATH . 'wp-admin/includes/file.php';  // @phpstan-ignore-line
        require_once ABSPATH . 'wp-admin/includes/media.php';  // @phpstan-ignore-line
        require_once ABSPATH . 'wp-admin/includes/image.php';  // @phpstan-ignore-line

        if (empty($filePath)) {
            throw new Exception('WpCore - Missing file path/URL for the media.');
        }

        $file = [
            'name' => wp_basename($filePath),
            'tmp_name' => file_exists($filePath) ? $filePath : download_url($filePath),
        ];

        if (is_wp_error($file['tmp_name'])) {
            throw new Exception("WpCore - Failed to create tmp file: {$file['tmp_name']->get_error_message()}");
        }

        $id = media_handle_sideload($file, $postId, $description, $postData);

        if (is_wp_error($id)) {
            unlink($file['tmp_name']);

            throw new Exception("WpCore - Failed to sideload: {$id->get_error_message()}");
        }

        return (int)$id;
    }

    /**
     * Get relative post permalink.
     *
     * @param int $postId
     *
     * @return string
     * @throws Exception
     */
    public static function getPostRelativePermalink(int $postId): string
    {
        $link = get_permalink($postId);
        if (!$link) {
            throw new Exception("WpCore - Cannot find permalink for post $postId");
        }
        return untrailingslashit(wp_parse_url($link, PHP_URL_PATH));
    }

    /**
     * Throw an exception with class' prefix.
     *
     * @param string $message
     *
     * @throws Exception
     * @deprecated Use just "throw" instead because this method isn't properly understood by phpstan.
     * @phpstan-ignore-next-line
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private static function exception(string $message): void
    {
        throw new Exception("WpCore – $message");
    }

    /**
     * Get WP Post by path/slug.
     *
     * @param string $path
     * @param string $postType
     *
     * @return WP_Post
     * @throws Exception
     * @see get_page_by_path()
     */
    public static function getPostBySlug(string $path, string $postType): WP_Post
    {
        // @phpstan-ignore-next-line
        $post = get_page_by_path($path, OBJECT, $postType);
        if (!$post) {
            throw new Exception("No $postType with specified slug found");
        }
        // @phpstan-ignore-next-line
        return $post;
    }
}
