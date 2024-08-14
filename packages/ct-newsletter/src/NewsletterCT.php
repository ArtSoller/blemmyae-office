<?php

namespace Cra\CtNewsletter;

use Scm\Entity\CustomPostType;

/**
 * Newsletter class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class NewsletterCT extends CustomPostType
{
    public const POST_TYPE = 'newsletter';
    public const GROUP_APPLICATION__FIELD_APPLICATION = 'field_634fcdce7572f';
    public const GROUP_APPLICATION__FIELD_APPLICATIONS = 'field_646c59e467bbf';
    public const GROUP_APPLICATION__FIELD_SLUG = 'field_6475be6ffc7a9';
    public const GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR = 'field_608f8eda9ccf7';
    public const GROUP_NEWSLETTER_TAXONOMY__FIELD_TYPE = 'field_606edbd026195';
    public const GROUP_PUBLISHING_OPTIONS__FIELD_UNPUBLISH_DATE = 'field_61af47d3a014d';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_SUBJECT = 'field_6156a99a2f396';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_SCHEDULE_DATE = 'field_6156aa142f397';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_NEWSLETTER_WYSIWYG = 'field_649c599b3a04d';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_ITEM = 'field_606edc8077a94';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_ITEM__SUBFIELD_POST = 'field_606edda277a9a';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_ITEM__SUBFIELD_TITLE = 'field_606edcbc77a95';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_ITEM__SUBFIELD_DATE = 'field_606edcf177a96';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_ITEM__SUBFIELD_AUTHOR = 'field_606edd2577a97';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_ITEM__SUBFIELD_DECK = 'field_606edd5377a98';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_ITEM__SUBFIELD_LINK = 'field_606edd8d77a99';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_BRIEFS = 'field_60f8042f020bc';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_BRIEFS__SUBFIELD_POST = 'field_60f8042f020bd';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_BRIEFS__SUBFIELD_TITLE = 'field_60f8042f020be';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_BRIEFS__SUBFIELD_SOURCE = 'field_60f80443020c3';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_ADDITIONAL_CONTENT = 'field_642a65e1616cb';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_ADDITIONAL_CONTENT__SUBFIELD_SECTION_TITLE = 'field_642a6635616cc';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_ADDITIONAL_CONTENT__SUBFIELD_POSTS = 'field_642296bc4da4d';
    public const GROUP_NEWSLETTER_ADVANCED__FIELD_LIST_OF_BLOCKS = 'field_649c5af63a04f';
    public const GROUP_META__FIELD_TITLE = 'field_61168e54ff82e';
    public const GROUP_META__FIELD_DESCRIPTION = 'field_61168e6aff82f';
    public const GROUP_META__FIELD_IMAGE = 'field_628b5738d6c15';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const GENERATE_NEWSLETTER_POST_ID = 'options';
    public const GRAPHQL_NAME = 'Newsletter';
    public const GRAPHQL_PLURAL_NAME = 'Newsletters';

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
