<?php

namespace Cra\BlemmyaePpworks;

use Scm\Entity\CustomPostType;

/**
 * PpworksArticle class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class PpworksArticleCT extends CustomPostType
{
    public const POST_TYPE = 'ppworks_article';
    public const GROUP_APPLICATION__FIELD_APPLICATION = 'field_634fcdce7572f';
    public const GROUP_APPLICATION__FIELD_APPLICATIONS = 'field_646c59e467bbf';
    public const GROUP_APPLICATION__FIELD_SLUG = 'field_6475be6ffc7a9';
    public const GROUP_PPWORKS_ARTICLE_ADVANCED__FIELD_HOST = 'field_6278fc7792bc8';
    public const GROUP_PPWORKS_ARTICLE_ADVANCED__FIELD_POSITION = 'field_6257e5d51449c';
    public const GROUP_PPWORKS_ARTICLE_ADVANCED__FIELD_SOURCE_LINK = 'field_61bac42e5ed53';
    public const GROUP_PPWORKS_ARTICLE_ADVANCED__FIELD_DESCRIPTION = 'field_621729c619b7f';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const GRAPHQL_NAME = 'PPWorksArticle';
    public const GRAPHQL_PLURAL_NAME = 'PPWorksArticles';

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
