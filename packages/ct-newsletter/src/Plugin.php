<?php

/**
 * Main plugin file
 *
 * @package   Cra\CtNewsletter
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\CtNewsletter;

use BrightNucleus\Config\ConfigInterface;
use BrightNucleus\Config\ConfigTrait;
use BrightNucleus\Config\Exception\FailedToProcessConfigException;
use BrightNucleus\Settings\Settings;
use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\CerberusApps;
use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Scm\Tools\Logger;
use Scm\Tools\PostByQueryResolver;
use Scm\WP_GraphQL\Utils;
use WP_Error;
use WP_Post;
use WP_Query;
use WP_Term;

/**
 * Main plugin class.
 *
 * @since 0.1.0
 *
 * @package Cra\CtNewsletter
 * @author  Gary Jones
 */
class Plugin
{
    use ConfigTrait;

    /**
     * Static instance of the plugin.
     *
     * @since 0.1.0
     *
     * @var self
     */
    protected static Plugin $instance;

    /**
     * Instance of NewsletterAd.
     *
     * @var NewsletterAd
     */
    protected NewsletterAd $adCe2e;

    /**
     * Instance of NewsletterAd.
     *
     * @var NewsletterAd
     */
    protected NewsletterAd $adMssp;

    /**
     * Instance of NewsletterAdGraphQL.
     *
     * @var NewsletterAdGraphQL
     */
    protected NewsletterAdGraphQL $adGraphQL;

    /**
     * Instantiate a Plugin object.
     *
     * Don't call the constructor directly, use the `Plugin::get_instance()`
     * static method instead.
     *
     * @param ConfigInterface $config Config to parametrize the object.
     *
     * @throws FailedToProcessConfigException If the Config could not be parsed correctly.
     *
     * @since 0.1.0
     *
     */
    public function __construct(ConfigInterface $config)
    {
        $this->processConfig($config);
    }

    /**
     * Launch the initialization process.
     *
     * @since 0.1.0
     */
    public function run(): void
    {
        add_action('plugins_loaded', [$this, 'loadTextDomain']);
        $this->addOptionsPage();

        // @todo: make it dynamic, based on app list.
        $this->adCe2e = new NewsletterAd(BlemmyaeApplications::CE2E);
        $this->adMssp = new NewsletterAd(BlemmyaeApplications::MSSP);
        $this->adGraphQL = new NewsletterAdGraphQL();

        add_filter(
        /** @phpstan-ignore-next-line */
            'acf/fields/post_object/query/key=' . Newsletter::FIELD_REPEATER_POST,
            [$this, 'acfFieldsPostObjectQuery'],
            10,
            3
        );

        add_action('acf/input/admin_enqueue_scripts', [$this, 'acfAdminEnqueueScripts']);
        add_action('acf/save_post', [$this, 'acfSavePost']);
        add_action('acf/save_post', [$this, 'pushToMarketo'], 20);

        // Applications.
        // @todo remove filter with APPLICATION_FIELD_META_KEY, when we remove it.
        $applicationFields = [
            CerberusApps::APPLICATION_FIELD_META_KEY,
            CerberusApps::APPLICATIONS_FIELD_META_KEY,
        ];

        foreach ($applicationFields as $field) {
            add_filter(
                'acf/load_value/name=' . $field,
                [$this, 'addDefaultNewsletterApplication'],
                3,
                10
            );
        }

        new PostByQueryResolver(NewsletterCT::POST_TYPE);

        new NewsletterCT();
    }

    /**
     * Load the plugin text domain.
     *
     * @since 0.1.0
     */
    public function loadTextDomain(): void
    {
        /**
         * Plugin text domain.
         *
         * @var string $textDomain
         */
        $textDomain = $this->config->getKey('Plugin.textdomain');
        $languagesDir = 'languages';
        if ($this->config->hasKey('Plugin/languages_dir')) {
            /**
             * Directory path.
             *
             * @var string $languagesDir
             */
            $languagesDir = $this->config->getKey('Plugin.languages_dir');
        }

        load_plugin_textdomain($textDomain, false, $textDomain . '/' . $languagesDir);
    }

    /**
     * Adds options page to admin menu.
     */
    private function addOptionsPage(): void
    {
        acf_add_options_sub_page(
            [
                'page_title' => __('Newsletter Options', 'ct-newsletter'),
                'menu_title' => __('Newsletter Options', 'ct-newsletter'),
                'parent_slug' => 'edit.php?post_type=newsletter',
            ]
        );
        acf_add_options_sub_page(
            [
                'page_title' => __('Generate Newsletter', 'ct-newsletter'),
                'menu_title' => __('Generate Newsletter', 'ct-newsletter'),
                'parent_slug' => 'edit.php?post_type=newsletter',
            ]
        );
    }

    /**
     * Callback for 'acf/fields/post_object/query/key=field_606edda277a9a' filter.
     *
     * Limits query to the selected Newsletter Type taxonomy.
     *
     * @param array $args
     *
     * @return array
     */
    public function acfFieldsPostObjectQuery(array $args): array
    {
        // Get search string.
        // Search string pattern `{Newsletter_id}---{Search_string/Title}`.
        $search = explode('----', $args['s']);

        // Search only by title.
        $args['s'] = $search[1];

        if (empty($args['s'])) {
            unset($args['s']);
        }

        // Additional options.
        $args += [
            // Load 5 elements only at first time.
            'posts_per_page' => 5,
            // Sort options.
            'orderby' => 'relevance',
            'order' => 'DESC',
        ];

        return $args;
    }

    private function getAvailableTopicList()
    {
        return get_field('available_topics', NewsletterCT::GENERATE_NEWSLETTER_POST_ID);
    }

    /**
     * Alters query arguments to limit posts only to allowed topics.
     *
     * @param array $args
     */
    private function alterQueryByAllowedTopics(array &$args): void
    {
        // Get allowed topics from field.
        $allowedTopics = $this->getAvailableTopicList();

        if ($allowedTopics) {
            // Load only specific topics.
            $args['meta_query'] = array_map(
                static fn($topicId) => ['key' => 'topic', 'value' => $topicId, 'compare' => 'LIKE'],
                $allowedTopics
            );

            $args['meta_query']['relation'] = 'OR';
        }
    }

    /**
     * Callback for 'acf/field_group/admin_enqueue_scripts' action.
     */
    public function acfAdminEnqueueScripts(): void
    {
        // @todo remove in future, if we do not need to load available posts
        // with available topic list. Need discussion with CRA team.
        wp_enqueue_script(
            'ctn-repeater-autofill',
            CT_NEWSLETTER_URL . 'assets/js/repeater-autofill.js',
            false,
            '1.0.0'
        );
    }

    /**
     * Callback for 'acf/save_post' action.
     *
     * @param int|string $postId
     */
    public function acfSavePost(int|string $postId): void
    {
        if ($postId !== NewsletterCT::GENERATE_NEWSLETTER_POST_ID) {
            return;
        }

        // Generate Newsletter only for acf-options-generate-newsletter page.
        $acfLocation = filter_input(
            INPUT_POST,
            '_acf_location',
            FILTER_DEFAULT,
            FILTER_REQUIRE_ARRAY
        ) ?: [];

        $isGenerateNewsletterPage = !empty($acfLocation['options_page']) &&
            $acfLocation['options_page'] === 'acf-options-generate-newsletter';

        // If it's generate newsletter page => generate newsletter.
        if ($isGenerateNewsletterPage) {
            $this->generateNewsletter(
                get_field('ctn_generate_newsletter_type', $postId),
                (int) get_field('ctn_generate_number_of_posts', $postId),
                get_field('ctn_generate_publish_status', $postId),
                get_field('subject', $postId),
                get_field('schedule_date', $postId, false),
            );
        }
    }

    /**
     * Generates newsletter based on the provided newsletter type.
     *
     * @param WP_Term $newsletterType
     * @param int     $postsNumber
     * @param string  $publishStatus
     * @param string  $subject
     * @param string  $date
     *
     * @return int
     */
    private function generateNewsletter(
        WP_Term $newsletterType,
        int $postsNumber,
        string $publishStatus,
        string $subject,
        string $date
    ): int {
        $postId = wp_insert_post(
            [
                'post_type' => NewsletterCT::POST_TYPE,
                'post_title' => $newsletterType->name . $date,
                'post_status' => $publishStatus,
            ]
        );

        // Map fields with value.
        // field_name => value.
        $fieldMap = [
            NewsletterCT::GROUP_NEWSLETTER_ADVANCED__FIELD_ITEM => array_map(
                static fn(WP_Post $post) => ['post' => $post->ID],
                $this->getEditorialsByNewsletterType($postsNumber)
            ),
            NewsletterCT::GROUP_NEWSLETTER_TAXONOMY__FIELD_TYPE => $newsletterType->term_id,
            NewsletterCT::GROUP_NEWSLETTER_ADVANCED__FIELD_SCHEDULE_DATE => $date,
            NewsletterCT::GROUP_NEWSLETTER_ADVANCED__FIELD_SUBJECT => $subject,
        ];

        // Map fields.
        foreach ($fieldMap as $field => $value) {
            update_field($field, $value, $postId);
        }

        wp_set_post_terms($postId, [$newsletterType->term_id], 'newsletter_type');

        return (int) $postId;
    }

    /**
     * Get Editorials based on the provided newsletter type.
     *
     * @param int $postsNumber
     *
     * @return WP_Post[]
     * @todo rename, because newsletter type is not used in this method
     *
     */
    private function getEditorialsByNewsletterType(int $postsNumber): array
    {
        // Init posts array.
        $posts = [];

        // Search posts, only if editor select topics.
        if (!empty($this->getAvailableTopicList())) {
            $args = [
                'post_type' => 'editorial',
                'post_status' => 'publish',
                'posts_per_page' => $postsNumber,
            ];

            $this->alterQueryByAllowedTopics($args);
            $query = new WP_Query($args);

            while ($query->have_posts()) {
                $query->the_post();
                $posts[] = $query->post;
            }

            wp_reset_postdata();
        }

        return $posts;
    }

    /**
     * Converts smart curly quotes into a straight.
     *
     * @param string $text
     *
     * @return string
     */
    public function convertSmartQuotes(string $text): string
    {
        $chrMap = [
            // Windows codepage 1252
            "\xC2\x82" => "'", // U+0082⇒U+201A single low-9 quotation mark
            "\xC2\x84" => '"', // U+0084⇒U+201E double low-9 quotation mark
            "\xC2\x8B" => "'", // U+008B⇒U+2039 single left-pointing angle quotation mark
            "\xC2\x91" => "'", // U+0091⇒U+2018 left single quotation mark
            "\xC2\x92" => "'", // U+0092⇒U+2019 right single quotation mark
            "\xC2\x93" => '"', // U+0093⇒U+201C left double quotation mark
            "\xC2\x94" => '"', // U+0094⇒U+201D right double quotation mark
            "\xC2\x9B" => "'", // U+009B⇒U+203A single right-pointing angle quotation mark

            // Regular Unicode     // U+0022 quotation mark (")
            // U+0027 apostrophe     (')
            "\xC2\xAB" => '"', // U+00AB left-pointing double angle quotation mark
            "\xC2\xBB" => '"', // U+00BB right-pointing double angle quotation mark
            "\xE2\x80\x98" => "'", // U+2018 left single quotation mark
            "\xE2\x80\x99" => "'", // U+2019 right single quotation mark
            "\xE2\x80\x9A" => "'", // U+201A single low-9 quotation mark
            "\xE2\x80\x9B" => "'", // U+201B single high-reversed-9 quotation mark
            "\xE2\x80\x9C" => '"', // U+201C left double quotation mark
            "\xE2\x80\x9D" => '"', // U+201D right double quotation mark
            "\xE2\x80\x9E" => '"', // U+201E double low-9 quotation mark
            "\xE2\x80\x9F" => '"', // U+201F double high-reversed-9 quotation mark
            "\xE2\x80\xB9" => "'", // U+2039 single left-pointing angle quotation mark
            "\xE2\x80\xBA" => "'", // U+203A single right-pointing angle quotation mark
        ];
        $chr = array_keys($chrMap);
        $rpl = array_values($chrMap);
        return str_replace($chr, $rpl, html_entity_decode($text, ENT_QUOTES, "UTF-8"));
    }

    /**
     * Push newsletter email to Marketo.
     *
     * @param int|string $postId
     */
    public function pushToMarketo(mixed $postId): void
    {
        if (
            !defined('MARKETO_CLIENT_SECRET')
            || !is_numeric($postId)
            || empty(Utils::frontendUri())
        ) {
            return;
        }
        $post = get_post($postId);
        if (empty($post) || $post->post_type !== NewsletterCT::POST_TYPE) {
            return;
        }

        try {
            /** @var WP_Term|null|false|WP_Error $type */
            $type = get_field('type', $postId);
            if (!($type instanceof WP_Term)) {
                return;
            }
            /** @var string|null|false $subject */
            $subject = get_field('subject', $postId) ?: '';

            // @todo: Check if we can import styles from <head> in similar way, if yes -> do that.
            $body = strtr(
                $this->pullNewsletterBody($post, $type->slug),
                [
                    '/wp-content/uploads/sites/2/' => '/wp-content/uploads/',
                ]
            );

            // Sanitize email body. All non-printable chars are removed.
            $body = $this->convertSmartQuotes($body);
            $body = preg_replace('/[[:^print:]]/u', '', $body);

            $date = get_field('schedule_date', $postId);
            if (empty($date)) {
                return;
            }
            $date = new DateTime($date);

            $app = get_field(NewsletterCT::GROUP_APPLICATION__FIELD_APPLICATION, $postId)->slug;
            if (empty($app)) {
                Logger::log('Unable to get Application name.', 'warning');
                return;
            }

            $environment = defined('MARKETO_ENVIRONMENT') ? MARKETO_ENVIRONMENT : 'dev';
            $marketo = (new Marketo($environment, $type->name))->setup();
            $marketo->push(
                $this->convertSmartQuotes($subject),
                $body,
                $date,
                [] // @todo: Add support for test emails.
            );
        } catch (Exception | GuzzleException $exception) {
            Logger::log($exception->getMessage(), 'error');
        }
    }

    /**
     * Pull newsletter body from frontend.
     *
     * @param WP_Post $post
     * @param string  $type
     *
     * @return string
     * @throws Exception
     */
    private function pullNewsletterBody(WP_Post $post, string $type): string
    {
        if (!defined('FRONTEND_URI_NL')) {
            throw new Exception(
                'Frontend URL for newsletter app is not specified, FRONTEND_URI_NL is not set in the wp-config.php'
            );
        }

        $html = file_get_contents(FRONTEND_URI_NL . "/api/$type/$post->post_name");

        if (empty($html)) {
            throw new Exception('Empty newsletter pulled from frontend!');
        }

        return $html;
    }

    /**
     * Use nlt application as default app for all newsletters.
     *
     * @param mixed $value
     * @param int|string|null $postId
     * @param array $field
     *
     * @return mixed
     */
    public function addDefaultNewsletterApplication(
        mixed $value,
        mixed $postId,
        array $field
    ): mixed {
        # Looks like a bad value.
        if (is_null($postId)) {
            return $value;
        }

        // If it's not a newsletter page => nothing to do.
        $postType = get_post_type($postId);
        if ($postType !== NewsletterCT::POST_TYPE) {
            return $value;
        }

        // Use nlt term as default value.
        if (empty($value)) {
            $app = get_term_by('slug', BlemmyaeApplications::NLT, BlemmyaeApplications::TAXONOMY);
            $value = empty($field['multiple']) ? $app->term_id : [$app->term_id];
        }

        return $value;
    }
}
