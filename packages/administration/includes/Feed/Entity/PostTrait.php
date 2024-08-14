<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Scm\Feed\Entity;

use Cra\BlemmyaeApplications\Entity\Permalink;
use WP_Post;

/**
 * Wrapper around basic WP post for feed purposes.
 */
trait PostTrait
{
    public WP_Post $post;

    /**
     * Class constructor.
     *
     * @param WP_Post $post
     */
    public function __construct(WP_Post $post)
    {
        $this->post = $post;
    }

    /**
     * Globally unique ID.
     *
     * @param string $app
     *
     * @return string
     */
    public function guid(string $app): string
    {
        $frontendPath = parse_url(Permalink::buildFrontendPathByApp($app));

        return "tag:{$frontendPath['host']}:post,{$this->post->ID}";
    }

    /**
     * Get ACF field value.
     *
     * @param string $selector
     * @param mixed $default
     *
     * @return mixed
     */
    protected function field(string $selector, $default)
    {
        return get_field($selector, $this->post->ID) ?: $default;
    }
}
