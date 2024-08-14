<?php

declare(strict_types=1);

namespace Cra\BlemmyaeApplications\Entity;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\CerberusApps;
use Scm\WP_GraphQL\Options;
use Scm\WP_GraphQL\Utils;
use WP_Term;

class Permalink
{
    /**
     * Build list of application slugs in HTML.
     *
     * @param $postId
     *  Post id.
     *
     * @return string
     *  HTML markup.
     */
    public static function buildApplicationSlugHTML($postId): string
    {
        // Return empty result for new post.
        global $pagenow;

        $output = '';

        if (in_array($pagenow, ['post-new.php'])) {
            return $output;
        }

        // Load application field.
        $apps = get_field(CerberusApps::APPLICATIONS_FIELD, $postId);
        $apps = array_filter($apps ?? []);

        // Preview links for drafts.
        if (empty($apps) && !BlemmyaeApplications::isPublishedPost($postId)) {
            $output .= sprintf(
                "<b>Below you can see preview link for %s. We use this application as default, if 
                        application field is empty. If you want to see link for correct  
                        application, please `Save Draft` and refresh the page</b></br></br>",
                BlemmyaeApplications::SCM
            );

            $apps = [Term::getAppTermBy('slug', BlemmyaeApplications::SCM)];
        }

        // If $apps is null, for ex. on create page => we do not need to render anything
        if (!empty($apps)) {
            // Build permalinks for FE applications.
            $permalinks = array_map(static function ($app) use ($postId) {
                return self::buildPermalinkMarkup($app, $postId);
            }, $apps);

            $permalinks = array_filter($permalinks);

            // Render only non-empty permalinks.
            if (!empty($permalinks)) {
                $output .= implode('</br>', $permalinks);
            }
        }

        return $output;
    }

    /**
     * Build permalink HTML markup for application.
     *
     * @param WP_Term|string $app
     *  Application term.
     * @param int|string $postId
     *
     * @return string
     */
    public static function buildPermalinkMarkup(mixed $app, int|string $postId): string
    {
        $permalink = self::buildPostPermalink($postId);
        $previewLink = get_preview_post_link($postId);

        // Init links array for output.
        $links = [];

        // Use permalink link only for published post or post, which supports preview.
        if (BlemmyaeApplications::isPublishedPost($postId)) {
            $permalinkUrl = $permalink;
        } elseif (Options::doesPostSupportPreview($postId)) {
            $permalinkUrl = $previewLink;
        }

        $actionLink = static fn($title, $link, $classes = '') => sprintf(
            '<a href="%s" class="%s">[%s]</a>',
            $link,
            $classes,
            $title
        );

        // Build link output
        $links[] = sprintf(
            '<b>%s - </b>%s',
            $app->name,
            isset($permalinkUrl) ? $actionLink($permalink, $permalinkUrl) : $permalink
        );

        if (BlemmyaeApplications::isPublishedPost($postId)) {
            $links[] = $actionLink('View', $permalink);
        }

        if (Options::doesPostSupportPreview($postId)) {
            $links[] = self::buildPreviewLinkHtml($postId, $previewLink);
        }

        return implode(" ", $links) . "</br>";
    }

    /**
     * Build preview link markup based on HTML from wp-core.
     *
     * Wp core HTML was located in `wp-admin/includes/meta-boxes.php`.
     *
     * @param string|int $postId
     * @param string $previewLink
     *
     * @return string
     */
    public static function buildPreviewLinkHtml(string|int $postId, string $previewLink): string
    {
        $previewButtonHtml = sprintf(
            '%1$s<span class="screen-reader-text"> %2$s</span>',
            '[Preview]',
            /* translators: Hidden accessibility text. */
            __('(opens in a new tab)')
        );

        return sprintf(
        // phpcs:ignore
            '<a href="%s" target="%s" id="post-preview">%s</a><input type="hidden" name="wp-preview" id="wp-preview" value="" />',
            $previewLink,
            $postId,
            $previewButtonHtml
        );
    }

    /**
     * Build frontend path for post.
     *
     * @param string|int $postId
     *
     * @return string
     */
    public static function buildPostPermalink(string|int $postId): string
    {
        $appTerm = Term::getAppTermByPostId($postId);
        $appSlug = $appTerm->slug;
        $postSlug = get_field(CerberusApps::APPLICATION_SLUG_FIELD, $postId, false);

        // todo cross-app support
        if (is_array($postSlug)) {
            $postSlug = reset($postSlug);
        }

        // If post has non-supported post status => render sample permalink.
        if (!BlemmyaeApplications::isPublishedPost($postId)) {
            [$slugPattern] = get_sample_permalink($postId);

            // Replace pagename token.
            $permalink = str_replace('%pagename%', $postSlug, $slugPattern);

            // Replace base path.
            return self::replaceBasePathByApplicationSlug($permalink, $appSlug);
        }

        // Work with permalink.
        $permalink = get_permalink($postId);

        return self::buildPermalinkByApp($permalink, $appSlug, $postSlug);
    }

    /**
     * Build frontend path for term.
     *
     * @param WP_Term $term
     * @param string $app
     *
     * @return string
     */
    public static function buildTermPermalink(WP_Term $term, string $app): string
    {
        $permalink = get_term_link($term);

        return self::buildPermalinkByApp($permalink, $app, $term->slug);
    }

    /**
     * Build frontend path for given permalink.
     *
     * @param string $permalink
     * @param string $app
     * @param string $slug
     * @param bool $replaceBasePath
     *
     * @return string
     */
    public static function buildPermalinkByApp(
        string $permalink,
        string $app,
        string $slug,
        bool $replaceBasePath = true
    ): string {
        // Replace post slug with our custom slug.
        $slugWithoutPrefix = self::removeAppsPrefixFromSlug(basename($permalink));

        // Update permalink based on app.
        if ($replaceBasePath) {
            $permalink = self::replaceBasePathByApplicationSlug($permalink, $app);
        }

        // Replace slug at the end of the string.
        $permalink = self::removeAppsPrefixFromPath($permalink);

        return substr_replace($permalink, $slug, -strlen($slugWithoutPrefix));
    }

    /**
     * Get slug without apps prefix.
     *
     * @param string $path
     *  Landing path.
     *
     * @return string
     *  Return modified path.
     * @todo research if we still use such logic
     *
     */
    public static function removeAppsPrefixFromPath(string $path): string
    {
        // Parse url and get base name (slug).
        $pathParsed = parse_url($path);

        if (!empty($pathParsed['path'])) {
            $slug = basename($pathParsed['path']);

            // Remove prefix.
            return substr_replace($path, self::removeAppsPrefixFromSlug($slug), -strlen($slug));
        }

        return $path;
    }

    /**
     * Get slug without apps prefix.
     *
     * @param string $slug
     *  Landing path.
     *
     * @return string
     *  Return modified path.
     * @todo research if we still use such logic
     *
     */
    public static function removeAppsPrefixFromSlug(string $slug): string
    {
        $replacement = !empty($slug) && $slug[0] === '/' ? '/' : '';

        return preg_replace("/^(\/|)_(\w+)-/", $replacement, $slug);
    }

    /**
     * Get frontend path for application;
     *
     * @param string $app
     *  App name.
     *
     * @return string
     *  Frontend URL for specific apps. SCM is default value.
     */
    public static function buildFrontendPathByApp(string $app): string
    {
        // Constant for frontend uri.
        $constant = implode('_', ['FRONTEND_URI', strtoupper($app)]);

        // Try to load path from config.
        if (defined($constant)) {
            $path = constant($constant);
        }

        return $path ?? Utils::frontendUri();
    }

    /**
     * Update frontend link for apps.
     *
     * @param string $app
     *  Application.
     *
     * @param string $link
     *  Frontend link.
     *
     * @return string
     *  Return link related to apps.
     */
    public static function updateFrontendLinkForApps(string $app, string $link): string
    {
        $frontendUri = self::buildFrontendPathByApp($app);

        // Replace backend link, if it's backend link.
        $link = str_replace(trailingslashit(get_home_url()), trailingslashit($frontendUri), $link);

        // Remove app prefix, if it exists.
        $link = self::removeAppsPrefixFromPath($link);

        return $link;
    }

    /**
     * Load application base url and replace wp base path in the link.
     *
     * @param $link
     *  Link, which should be updated.
     * @param $app
     *  Application slug
     *
     * @return string
     */
    public static function replaceBasePathByApplicationSlug(string $link, string $app): string
    {
        if (empty($link)) {
            return '';
        }

        // Try to get frontend URL for the app.
        $frontendPath = Permalink::buildFrontendPathByApp($app);

        // Replace base url, if we have frontend path.
        return $frontendPath ? $frontendPath . wp_make_link_relative($link) : $link;
    }
}
