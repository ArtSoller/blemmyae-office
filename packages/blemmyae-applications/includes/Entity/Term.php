<?php

declare(strict_types=1);

namespace Cra\BlemmyaeApplications\Entity;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use WP_Term;

class Term
{
    /**
     * Get application as term object by field.
     *
     * @param string $field
     *  Available fields: slug, name, term_id.
     * @param string|int $value
     *  Field value.
     *
     * @return WP_Term|null
     */
    public static function getAppTermBy(string $field, string|int $value): ?WP_Term
    {
        return get_term_by($field, $value, BlemmyaeApplications::TAXONOMY) ?? null;
    }

    /**
     * @param string $termName
     *
     * @return int|null
     */
    public static function getAppTermIdByAppSlug(string $termName): ?int
    {
        return get_term_by('slug', $termName, BlemmyaeApplications::TAXONOMY)->term_id ?? null;
    }

    /**
     * Get application term object for specific post.
     *
     * @param string|int $postId
     *  Post id,
     * @return WP_Term|null
     */
    public static function getAppTermByPostId(string|int $postId): ?WP_Term
    {
        $appSlug = BlemmyaeApplications::getAppIdByPostId($postId);

        return self::getAppTermBy('slug', $appSlug);
    }
}
