<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Scm\Feed\Entity;

use Cra\BlemmyaeApplications\Entity\Permalink;
use Cra\BlemmyaePpworks\PpworksSegmentCT;
use Cra\CtEditorial\EditorialCT;
use WP_Post;
use WP_Term;

/**
 * Wrapper around post entity for feed purposes.
 */
class Post
{
    use PostTrait;

    /**
     * Get authors of the post.
     *
     * @param string $app
     *
     * @return array{name: string, uri: string}
     */
    public function authors(string $app): array
    {
        $field_name = match ($this->post->post_type) {
            'editorial' => EditorialCT::GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR,
            'ppworks_segment' => PpworksSegmentCT::GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_HOSTS,
        };

        return array_map(
            static function (WP_Post $author) use ($app) {
                $permalink = get_post_permalink($author);
                $permalinkBasePath = parse_url($permalink, PHP_URL_HOST);
                $frontendPath = Permalink::buildFrontendPathByApp($app);
                $permalink = str_replace($permalinkBasePath, parse_url($frontendPath, PHP_URL_HOST), $permalink);

                return [
                    'name' => $author->post_title,
                    'uri' => $permalink,
                ];
            },
            $this->field($field_name, [])
        );
    }

    /**
     * Get topics of the post.
     *
     * @return array|WP_Term[]
     */
    public function topics(): array
    {
        $field_name = match ($this->post->post_type) {
            'editorial' => EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC,
            'ppworks_segment' => PpworksSegmentCT::GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_TOPICS,
        };

        return $this->field($field_name, []);
    }

    /**
     * Get deck of the post.
     *
     * @return string
     */
    public function deck(): string
    {
        return $this->field('deck', '');
    }

    /**
     * Get featured image of the post.
     *
     * @param string $imageField
     * @param string|null $imageCaptionField
     *
     * @return array|null
     */
    public function featuredImage(string $imageField, string $imageCaptionField = null): ?array
    {
        $image = $this->field($imageField, null);
        if ($image && $imageCaptionField) {
            $image['caption'] = $this->field($imageCaptionField, '');
        }

        return $image;
    }
}
