<?php

/**
 * WPGraphQL – Options.
 *
 * @author Alexander Kucherov (avdkucherov@gmail.com)
 */

namespace Scm\WP_GraphQL;

use Amazon_S3_And_CloudFront;
use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\Entity\Permalink;
use Cra\BlemmyaePpworks\Ppworks;
use Cra\CtNewsletter\NewsletterCT;
use Cra\CtWhitepaper\WhitepaperCT;
use DateTime;
use Exception;
use GraphQL\Type\Definition\AbstractType;
use Scm\Acf_Extended\ConfigStorage;
use Scm\Archived_Post_Status\Options as ArchivedOptions;
use Scm\Tools\Logger;
use WP_Post;
use WP_Query;
use WP_User;
use WPGraphQL\AppContext;

class Options
{
    public const PUBLIC_POST_STATUSES_FILTER = 'public_post_statuses';
    private const NEWSLETTER_COLLECTION_ITEM_TYPENAME = 'Newsletter_Newslettercollection_item';
    private const MAX_QUERY_ITEMS_AMOUNT = 200;

    /**
     * @param object{ID: int} $object
     * @param string $key
     * @param array<string, mixed> $fieldArgs
     *
     * @return mixed
     */
    private static function resolveAdvancedAds(object $object, string $key, array $fieldArgs): mixed
    {
        $options = get_post_meta($object->ID, 'advanced_ads_ad_options', $fieldArgs['single']);
        $isExpired = !empty($options['expiry_date']) &&
            (new DateTime('now')) > (new DateTime())->setTimestamp($options['expiry_date']);

        switch ($key) {
            case 'expired':
                return $isExpired;
            case 'content':
                if ('image' === $options['type']) {
                    $image = wp_get_attachment_image_src(
                        $options['output']['image_id'],
                        'large'
                    );
                    $url = $options['url'];
                    $imgSrc = $image[0] ?? '';

                    return !$imgSrc ? '' : <<<HTML
<a href="$url" style="max-width: 100%; display: block;"><img src="$imgSrc" alt="Ad" style="max-width: 100%;" /></a>
HTML;
                } else {
                    $post = get_post($object->ID);
                    if ($post instanceof WP_Post) {
                        return $post->post_content;
                    }
                    return '';
                }
            default:
                return get_post_meta($object->ID, $key, $fieldArgs['single']);
        }
    }

    /**
     * Get list of public post statuses.
     *
     * @return string[]
     */
    public static function getPublicPostStatuses(): array
    {
        return apply_filters(
            self::PUBLIC_POST_STATUSES_FILTER,
            [
                'publish',
                ArchivedOptions::ARCHIVE_STATUS,
            ]
        );
    }

    /**
     * Initialize hooks.
     *
     * @throws Exception
     */
    public function __construct()
    {
        if (!get_option('wp_graphql_version')) {
            return;
        }

        add_action('graphql_register_types', [$this, 'personPostsCount'], 10);
        add_action('graphql_register_types', [$this, 'personPosts'], 10);

        add_filter(
            'graphql_data_is_private',
            [$this, 'privateData'],
            10,
            6
        );

        add_action(
            'graphql_init',
            [$this, 'metaFields']
        );

        add_action('graphql_register_types', static fn() => new Taxonomy(), 10);
        add_action('graphql_register_types', static fn() => new Redirects(), 10);
        add_action('graphql_register_types', static fn() => new TablePress(), 10);
        add_filter('graphql_data_is_private', [$this, 'graphqlDataIsPrivate'], 10, 3);

        // WPEs links update legacy.
        $this->previewHooks();

        add_filter('the_content', [$this, 'contentReplacement']);
        add_filter('the_content_block', [$this, 'contentReplacement']);
        add_filter('wp_calculate_image_srcset', [$this, 'imageSourceSrcsetReplacement'], 10, 5);
        add_filter('post_link', [$this, 'postLink'], 10, 2);
        add_filter('post_type_link', [$this, 'postLink'], 10, 2);
        add_filter('term_link', [$this, 'termLink']);
        add_filter('wp_insert_post_data', [$this, 'filterPostData'], 10, 2);
        add_filter('wp_unique_post_slug', [$this, 'uniquePostSlug'], 10);

        // WP Graphql JWT Auth.
        add_filter('graphql_jwt_auth_iss_allowed_domains', [$this, 'addAllowedDomainsForJwtAuth']);
        add_filter('graphql_pre_resolve_field', [$this, 'graphqlPreResolveField'], 10, 6);

        // WPGraphql and tablepress integration hooks
        add_filter('register_post_type_args', static function ($args, $postType) {
            if ('tablepress_table' === $postType) {
                $args['show_in_graphql'] = true;
                $args['graphql_single_name'] = TablePress::GRAPHQL_NAME;
                $args['graphql_plural_name'] = TablePress::GRAPHQL_NAME . 's';
            }
            return $args;
        }, 10, 2);
        add_filter('tablepress_post_type_args', static function ($postTypeConfig) {
            $postTypeConfig['public'] = true;
            return $postTypeConfig;
        });

        $this->limitQueryItemsAmount();

        add_filter('acf/taxonomy/registration_args', [$this, 'addTaxonomiesToGraphql'], 10, 2);
    }

    /**
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    public function addTaxonomiesToGraphql(array $args): array
    {
        $graphqlName = str_replace(' ', '', $args['labels']['singular_name'] ?? $args['labels']['name']);

        $args['show_in_graphql'] = true;
        $args['graphql_single_name'] = $graphqlName;
        $args['graphql_plural_name'] = $graphqlName . 's';

        return $args;
    }

    /**
     * Hooks related to preview.
     *
     * @return void
     */
    private function previewHooks(): void
    {
        // Load all available post types for ACF.
        $postTypes = get_post_types(array('public' => true, '_builtin' => false));

        // For all drafts replace view link via preview link.
        foreach ($postTypes as $postType) {
            add_filter('rest_prepare_' . $postType, [$this, 'postPreviewLinkDraftFix'], 10, 2);
        }

        // Generate preview link for our post.
        add_filter('preview_post_link', [$this, 'postPreviewLink'], 10, 2);
        add_filter('rest_prepare_autosave', [$this, 'postPreviewLinkAutosave'], 10, 2);

        // Hide preview links for non-supported types via CSS.
        add_action('admin_enqueue_scripts', [$this, 'hideAdminBlocks']);
    }

    /**
     * Allow multiple domains for JWT Auth.
     *
     * @param string[] $allowedDomains
     *  Array of allowed domains.
     *
     * @return string[]
     *  Modified array of allowed domains.
     */
    public function addAllowedDomainsForJwtAuth(array $allowedDomains): array
    {
        // Add API url for JWT allowed token.
        if (defined('WP_APIURL')) {
            $allowedDomains[] = WP_APIURL;
        }

        // Add CMS url.
        if (defined('WP_CMSURL')) {
            $allowedDomains[] = WP_CMSURL;
        }

        // Remove duplicates, if it exists.
        return array_unique($allowedDomains);
    }

    /**
     * Callback for wp_insert_post_data WordPress filter.
     * Filters out unicode special characters from post_title and forces
     * wp_insert_post to update post_name on each post_title change
     *
     * @param array<string, mixed> $postData
     * @param array<string, mixed> $post
     *
     * @return array<string, mixed>
     */
    public function filterPostData(array $postData, array $post): array
    {
        $postData['post_title'] = preg_replace(
            '/[\x{FFF0}-\x{FFFF}]/mu',
            '',
            $postData['post_title']
        );

        // Currently specific to whitepaper post
        if ($post['post_type'] === WhitepaperCT::POST_TYPE) {
            /**
             * On post save field value is not yet saved, therefore can not access it
             * via get_field acf method, but can get value from POST field
             */
            $postStatus = $_POST['acf'][WhitepaperCT::GROUP_POST_STATUS__FIELD_POST_STATUS] ?? null;
            $postData['post_status'] = $postStatus ?? $postData['post_status'];
        }

        return $postData;
    }

    /**
     * Post is not private if its post status is unfinished or archive.
     * unfinished and archive - custom public post statuses
     *
     * @param bool $is_private
     * @param string $modelName
     * @param mixed $data
     *
     * @return bool
     */
    public function graphqlDataIsPrivate(bool $is_private, string $modelName, mixed $data): bool
    {
        if (!($data instanceof WP_Post)) {
            return $is_private;
        }
        if (
            $data->post_status === WhitepaperCT::HIDDEN_FROM_FEEDS_POST_STATUS ||
            $data->post_status === Ppworks::POST_STATUS__UNFINISHED ||
            $data->post_status === Ppworks::POST_STATUS__TO_BE_PUBLISHED ||
            $data->post_status === 'advanced_ads_expired' ||
            $data->post_status === "archive"
        ) {
            return false;
        }
        return $is_private;
    }

    /**
     * Pre-resolve field filter, explicitly sets a resolver to posts in
     * newsletterCollectionItem fields, silences 500 errors on draft posts
     *
     * @param $nil
     * @param $source
     * @param $args
     * @param AppContext $context
     * @param $info
     *
     * @return bool
     * @throws Exception
     * @todo rework if some other solution will be found
     * @phpstan-ignore-next-line TODO figure out arg types.
     */
    public function graphqlPreResolveField($nil, $source, $args, AppContext $context, $info): mixed
    {
        if (
            // Do nothing for every field which parent is not newsletterCollectionItem
            $info->parentType->name !== self::NEWSLETTER_COLLECTION_ITEM_TYPENAME ||
            // Do nothing for fields other than post in newsletterCollectionItem
            (
                $info->parentType->name === self::NEWSLETTER_COLLECTION_ITEM_TYPENAME &&
                $info->fieldName !== "post"
            )
        ) {
            // WpGraphQL will do nothing if nil arg is returned
            return $nil;
        }
        // Get postId of attached post from raw acf data
        $postId = $source[NewsletterCT::GROUP_NEWSLETTER_ADVANCED__FIELD_ITEM__SUBFIELD_POST] ?? null;
        // Use post loader to load this post, this way private posts do not return 500 error
        return $context->get_loader('post')->load_deferred($postId);
    }

    /**
     * Adds output of meta fields to graphQL.
     *
     * @throws Exception
     */
    public function metaFields(): void
    {
        $allTypes = array_merge(
            ConfigStorage::getCustomPostTypes(),
            [
                'advanced_ads' => [
                    'graphql_single_name' => 'AdvancedAd',
                ],
            ]
        );

        foreach ($allTypes as $typeKey => $type) {
            add_filter(
                "graphql_{$type['graphql_single_name']}_fields",
                function ($fields) use ($typeKey) {
                    return $this->addMetaFields($fields, $typeKey);
                }
            );
        }
    }

    /**
     * Makes all menus and menuItems accessible.
     *
     * @param bool $isPrivate
     * @param string $modelName
     * @param mixed $data
     * @param string|null $visibility
     * @param int|null $owner
     * @param WP_User $currentUser
     *
     * @return bool
     */
    public function privateData(
        bool $isPrivate,
        string $modelName,
        mixed $data,
        ?string $visibility,
        ?int $owner,
        WP_User $currentUser
    ): bool {
        return ('MenuObject' === $modelName || 'MenuItemObject' === $modelName)
            ? false
            : $isPrivate;
    }

    /**
     * @todo: move to person plugin
     * @param array<string, mixed> $args
     *
     * @return WP_Post[]
     */
    private function queryPersonPosts(array $args): array
    {
        $slug = $args['slug'] ?? '';
        if (!$slug) {
            return [];
        }

        // @phpstan-ignore-next-line Cannot find OBJECT.
        $person = get_page_by_path($slug, OBJECT, 'people');
        if (!($person instanceof WP_Post)) {
            return [];
        }

        $personId = $person->ID;
        // @phpstan-ignore-next-line Cannot find MINUTE_IN_SECONDS.
        $cacheExpirationTime = 5 * MINUTE_IN_SECONDS;
        $group = 'person_posts';
        $result = wp_cache_get($personId, $group);
        if ($result === false) {
            $result = get_field('post', $personId, false);
            wp_cache_set($personId, $result, $group, $cacheExpirationTime);
        }

        if (!$result) {
            return [];
        }

        $orderBy = $args['orderBy'] ?? 'DESC';

        if (in_array($orderBy, ['ASC', 'DESC'], true)) {
            $postType = $args['postType'] ?? 'any';
            $postsPerPage = $args['postsPerPage'] ?? '-1';
            $paged = $args['paged'] ?? '1';
            $include = array_map('intval', $result);
            $key = $personId . '_' . md5(serialize($args));
            $result = wp_cache_get($key, $group);
            if ($result === false) {
                $args = [
                    'post_type' => $postType,
                    'post__in' => $include,
                    'orderby' => 'publish_date',
                    'order' => $orderBy,
                    'paged' => $paged,
                    'posts_per_page' => $postsPerPage,
                    'fields' => 'ids',
                    'post_status' => 'publish',
                ];
                $query = new WP_Query($args);
                $result = $query->posts;
                wp_cache_set($key, $result, $group, $cacheExpirationTime);
            }
        }

        return $result;
    }

    /**
     * Registers graphQL personPostsCount field.
     * @throws Exception
     */
    public function personPostsCount(): void
    {
        $postTypes = array_merge(
            array_keys(ConfigStorage::getCustomPostTypes()),
            ['advanced_ads']
        );
        $config = [
            'type' => 'Integer',
            'description' => __('A count of person posts.', 'administration'),
            'args' => [
                'slug' => [
                    'type' => 'ID',
                    'description' => __('Person SLUG only.', 'administration'),
                ],
                'postType' => [
                    'type' => 'String',
                    'description' => __(
                        'A list by selected post type: ' . implode(
                            ', ',
                            $postTypes
                        ),
                        'administration'
                    ),
                ],
            ],

            'resolve' => fn(
                $source,
                $args,
                $context,
                $info
            ): int => count($this->queryPersonPosts($args)),
        ];
        register_graphql_field('RootQuery', 'personPostsCount', $config);
    }

    /**
     * Registers graphQL personPosts field.
     * @throws Exception
     */
    public function personPosts(): void
    {
        $postTypes = array_merge(
            array_keys(ConfigStorage::getCustomPostTypes()),
            ['advanced_ads']
        );
        $config = [
            'type' => ['list_of' => 'String'],
            'description' => __(
                'A list of person posts Ids. Helps with authored by feed.',
                'administration'
            ),
            'args' => [
                'slug' => [
                    'type' => 'ID',
                    'description' => __('Person SLUG only.', 'administration'),
                ],
                'orderBy' => [
                    'type' => 'String',
                    'description' => __(
                        'Publish date: ASC or DESC',
                        'administration'
                    ),
                ],
                'postsPerPage' => [
                    'type' => 'Integer',
                    'description' => __(
                        'Posts per request | post type only argument.',
                        'administration'
                    ),
                ],
                'paged' => [
                    'type' => 'Integer',
                    'description' => __(
                        'Posts per request (offset) | post type only argument.',
                        'administration'
                    ),
                ],
                'postType' => [
                    'type' => 'String',
                    'description' => __(
                        'A list by selected post type: ' . implode(
                            ', ',
                            $postTypes
                        ),
                        'administration'
                    ),
                ],
            ],
            'resolve' => fn(
                $source,
                $args,
                $context,
                $info
            ): array => $this->queryPersonPosts($args),
        ];
        register_graphql_field('RootQuery', 'personPosts', $config);
    }

    /**
     * Strips home domain and protocol.
     *
     * @param string $url
     *
     * @return string
     */
    public static function forceRelativeUrl(string $url): string
    {
        $response = preg_replace('/^(http)?s?:?\/\/[^\/]*(\/?.*)$/i', '$2', $url);
        if ($response === null) {
            Logger::log("forceRelativeUrl preg_replace error on $url", 'warning');
            return $url;
        }
        return $response;
    }

    /**
     * Adds the meta fields for this $objectType registered using
     * register_meta().
     *
     * @param array $fields
     * @param string $objectType
     *
     * @return array
     * @throws Exception If a meta key is the same as a default key warn the dev.
     */
    public function addMetaFields(array $fields, string $objectType): array
    {
        /** @var array $metaKeys */
        $metaKeys = get_registered_meta_keys($objectType);

        if (!empty($metaKeys)) {
            foreach ($metaKeys as $key => $fieldArgs) {
                if (isset($fields[$key])) {
                    throw new Exception(sprintf('Post meta key "%s" is a reserved word.', $key));
                }

                if (!$fieldArgs['show_in_rest']) {
                    continue;
                }

                $machineKey = lcfirst(str_replace(
                    ' ',
                    '',
                    ucwords(preg_replace('/[^A-Za-z0-9]/', ' ', $key) ?? '')
                ));
                $fields[$machineKey] = [
                    'type' => $this->resolveMetaType($fieldArgs['type'], $fieldArgs['single']),
                    'description' => $fieldArgs['description'],
                    'resolve' => static function ($object) use ($objectType, $key, $fieldArgs) {
                        if ('advanced_ads' === $objectType) {
                            return self::resolveAdvancedAds($object, $key, $fieldArgs);
                        }
                        if (
                            'post' === $objectType || in_array(
                                $objectType,
                                get_post_types(),
                                true
                            )
                        ) {
                            return get_post_meta($object->ID, $key, $fieldArgs['single']);
                        }
                        if (
                            'term' === $objectType || in_array(
                                $objectType,
                                get_taxonomies(),
                                true
                            )
                        ) {
                            return get_term_meta($object->term_id, $key, $fieldArgs['single']);
                        }
                        if ('user' === $objectType) {
                            return get_user_meta($object->ID, $key, $fieldArgs['single']);
                        }

                        return '';
                    },
                ];
            }
        }

        return $fields;
    }

    /**
     * Resolves REST API types to meta data types.
     *
     * @param mixed $type
     * @param bool $single
     *
     * @return mixed
     */
    private function resolveMetaType(mixed $type, bool $single = true): mixed
    {
        if ($type instanceof AbstractType) {
            return $type;
        }

        $type = match ($type) {
            'integer' => 'Integer',
            'number' => 'Float',
            'boolean' => 'Boolean',
            default => apply_filters("graphql_{$type}_type", 'String', $type),
        };

        return $single ? $type : ['list_of' => $type];
    }

    /**
     * Callback for WordPress 'the_content' filter.
     *
     * @param string $content The post content.
     *
     * @return string The post content.
     */
    public function contentReplacement(string $content): string
    {
        $frontendUri = Utils::frontendUri() ?? '/';
        $filesCdnUrl = Utils::filesCdnHost();
        $siteUrl = site_url();
        // Need to double up cms and cdn subdomains here because otherwise the logic is wrong on production.
        // Also, must first replace URLs which should go to files CDN.
        $replacements = [
            "$siteUrl/wp-content/uploads/" => "$filesCdnUrl/wp-content/uploads/",
            'https://cms.scmagazine.com/wp-content/uploads/' => "$filesCdnUrl/wp-content/uploads/",
            'https://cdn.scmagazine.com/wp-content/uploads/' => "$filesCdnUrl/wp-content/uploads/",
            "$frontendUri/wp-content/uploads/" => "$filesCdnUrl/wp-content/uploads/",
            $siteUrl => $frontendUri,
            'https://cms.scmagazine.com' => $frontendUri,
            'https://cdn.scmagazine.com' => $frontendUri,
            '//' => '/',
        ];

        foreach ($replacements as $source => $target) {
            $content = str_replace("href=\"$source", "href=\"$target", $content);
        }

        return $content;
    }

    /**
     * Callback for WordPress 'the_content' filter to replace paths to media.
     *
     * @param array $sources {
     *     One or more arrays of source data to include in the 'srcset'.
     *
     * @type array $width {
     * @type string $url The URL of an image source.
     * @type string $descriptor The descriptor type used in the image candidate string,
     *                                  either 'w' or 'x'.
     * @type int $value The source width if paired with a 'w' descriptor, or a
     *                                  pixel density value if paired with an 'x' descriptor.
     *     }
     * }
     *
     * @param array $sizeArray {
     *     An array of requested width and height values.
     *
     * @type int $0 The width in pixels.
     * @type int $1 The height in pixels.
     * }
     *
     * @param string $imageSrc The 'src' of the image.
     * @param array $imageMeta The image meta data as returned by 'wp_get_attachment_metadata()'.
     * @param int $attachmentId Image attachment ID or 0.
     *
     * @return array One or more arrays of source data.
     */
    public function imageSourceSrcsetReplacement(
        array $sources,
        array $sizeArray,
        string $imageSrc,
        array $imageMeta,
        int $attachmentId
    ): array {
        $frontendUri = Utils::frontendUri();
        $siteUrl = site_url();
        global $as3cf;
        $isProviderS3 = class_exists('Amazon_S3_And_CloudFront') && $as3cf instanceof Amazon_S3_And_CloudFront;

        if ($sources) {
            // For urls with no domain or the frontend domain, replace with the wp site_url.
            $patterns = [
                "#^$frontendUri/#",
                '#^/#',
            ];
            foreach ($sources as $width => $source) {
                // S3 Offload doesn't populate image src set by default.
                if ($isProviderS3 && $as3cf->is_attachment_served_by_provider($attachmentId)) {
                    $providerFragments = explode('/', $imageSrc);
                    array_pop($providerFragments);
                    $filePath = explode('/', $source['url']);
                    $providerFragments[] = array_pop($filePath);
                    $sources[$width]['url'] = implode('/', $providerFragments);
                    continue;
                }
                $sources[$width]['url'] = preg_replace($patterns, "$siteUrl/", $source['url']);
            }
        }

        return $sources;
    }

    /**
     * Callback for WordPress 'preview_post_link' filter.
     *
     * Swap the post preview link for headless front-end and to use the API entry to support Next.js preview mode.
     *
     * @param string $link URL used for the post preview.
     * @param WP_Post $post Post object.
     *
     * @return string URL used for the post preview.
     */
    public function postPreviewLink(string $link, WP_Post $post): string
    {
        // Load apps.
        $app = BlemmyaeApplications::getAppByPostObject($post);

        // Load apps field, if it exists.
        if ($frontendUri = Permalink::buildFrontendPathByApp($app)) {
            /**
             * This should already be handled by postLink, but it's here for verbosity's sake and if the
             * other filter changes for any reason.
             */
            $link = Permalink::updateFrontendLinkForApps($app, $link);
        }

        $args = wp_parse_args(wp_parse_url($link, PHP_URL_QUERY));
        $args['wp_headless_secret'] = Utils::secret();
        $args['post_type'] = $post->post_type;
        // Add p=xx if it's missing, which is the case for published posts.

        // Get preview ID only for published posts.
        // For draft posts WP saves data as post instead of revision.
        if (
            ($autosavedPost = wp_get_post_autosave(
                $post->ID,
                get_current_user_id()
            )) && $post->post_status === 'publish'
        ) {
            $args['preview_id'] = $autosavedPost->ID;
        }

        $args['post_id'] = $args['p'] ?? $post->ID;
        $link = untrailingslashit($frontendUri) . '/api/preview';

        // Add ?p=xx&preview=true to link again.
        return add_query_arg(
            [
                $args,
            ],
            $link
        );
    }

    /**
     * Hack Function that changes the preview link for draft articles.
     *
     * This must be removed when wordpress do the properly fix https://github.com/WordPress/gutenberg/issues/13998
     *
     *
     * @param $response
     * @param $post
     *
     * @return mixed
     */
    public function postPreviewLinkDraftFix($response, $post): mixed
    {
        if ('draft' === $post->post_status) {
            $response->data['link'] = get_preview_post_link($post);
        }

        return $response;
    }

    /**
     * Add preview link into the response for autosave.
     *
     * Generate preview link for rest autosave to fix guttenberg preview button.
     *
     * @param $response
     * @param $post
     * @return mixed
     */
    public function postPreviewLinkAutosave($response, $post): mixed
    {
        if ($post && self::doesPostSupportPreview($post->ID)) {
            $response->data['preview_link'] = get_preview_post_link($post);
        }

        return $response;
    }

    /**
     * Callback for WordPress 'preview_post_link' filter and 'post_link' filter.
     *
     * Callback for WordPress  'post_link' filter.
     *
     * Swap post links in admin for headless front-end.
     *
     * @param string $link URL used for the post.
     * @param WP_Post $post post object.
     *
     * @return string URL used for the post.
     */
    public function postLink(string $link, WP_Post $post): string
    {
        if ((function_exists('is_graphql_request') && is_graphql_request())) {
            return $link;
        }

        // @todo move to separate CerberusApps plugin.
        // Load app, SCM by default (for people, for example).
        $app = BlemmyaeApplications::doesPostSupportApplication($post->ID)
            ? BlemmyaeApplications::getAppIdByPostId($post->ID)
            : BlemmyaeApplications::SCM;

        return Permalink::updateFrontendLinkForApps($app, $link);
    }

    /**
     * Rewrites term links to point to the specified front-end URL.
     *
     * @param string $termLink Term link URL.
     *
     * @return string
     */
    public function termLink(string $termLink): string
    {
        $frontendUri = Utils::frontendUri();

        if (empty($frontendUri)) {
            return $termLink;
        }

        $frontendUri = trailingslashit($frontendUri);
        $siteUrl = trailingslashit(site_url());

        return str_replace($siteUrl, $frontendUri, $termLink);
    }

    /**
     * Callback for wp_unique_post_slug WordPress filter.
     * Filters out unicode special characters from slug and forces
     * wp_insert_post to update slug on each slug change.
     *
     * @param string $slug
     *
     * @return string
     */
    public function uniquePostSlug(string $slug): string
    {
        // Pattern '/[\x{FFF0}-\x{FFFF}]/mu' is not working here, because $slug contains URL encoded string.
        return preg_replace('/%[a-f][a-f]/mui', '', $slug);
    }

    /**
     * Check if post supports review.
     *
     * @param string|int $postId
     * @return bool
     */
    public static function doesPostSupportPreview(string|int $postId): bool
    {
        // List of post types, which supports preview.
        $supportedPostTypes = [
            'landing',
            'editorial',
        ];

        $postType = get_post_type($postId);

        return $postType && in_array($postType, $supportedPostTypes);
    }

    /**
     * Override styles for admin ui.
     *
     * Hide default preview button for non-supported post types.
     *
     * @return void
     */
    public function hideAdminBlocks(): void
    {
        wp_register_style(
            'hide-admin-blocks',
            plugins_url('src/applications/hideAdminBlocks.css', dirname(__DIR__)),
            [],
            null
        );

        wp_enqueue_style('hide-admin-blocks');
    }

    /**
     * Filters the maximum number of items that should be queried.
     *
     * @todo This solution should be reconsidered in favor of creating separate wpgraphql field to serve all topics or
     * implementing the pagination on FE.
     * @link See ticket for details: https://cra.myjetbrains.com/youtrack/issue/PORT-2484
     */
    private function limitQueryItemsAmount(): void
    {
        add_filter(
            'graphql_connection_max_query_amount',
            function (int $max_amount, $source, array $args, $context, $info) {
                if ($info->fieldName === 'topics') {
                    return self::MAX_QUERY_ITEMS_AMOUNT;
                }

                return $max_amount;
            },
            10,
            5
        );
    }
}
