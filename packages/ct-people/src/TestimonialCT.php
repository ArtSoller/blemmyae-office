<?php

namespace Cra\CtPeople;

use Scm\Entity\CustomPostType;

/**
 * Testimonial class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class TestimonialCT extends CustomPostType
{
    public const POST_TYPE = 'testimonial';
    public const GROUP_TESTIMONIAL__FIELD_PERSON = 'field_665439a038bf8';
    public const GROUP_TESTIMONIAL__FIELD_TEXT = 'field_66543b8838bf9';
    public const GROUP_TESTIMONIAL__FIELD_CISO_COMMUNITY_REGION = 'field_6661c890793f8';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const GRAPHQL_NAME = 'Testimonial';
    public const GRAPHQL_PLURAL_NAME = 'Testimonials';

    /**
     * Initialize hooks.
     */
    public function __construct()
    {
        parent::__construct();
        add_filter('register_post_type_args', static function ($args, $postType) {
            if (self::POST_TYPE === $postType) {
                $args['show_in_graphql'] = true;
                $args['graphql_single_name'] = self::GRAPHQL_NAME;
                $args['graphql_plural_name'] = self::GRAPHQL_PLURAL_NAME;
            }
            return $args;
        }, 10, 2);
    }
}
