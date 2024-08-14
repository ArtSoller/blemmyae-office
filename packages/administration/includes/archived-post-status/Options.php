<?php

/**
 * ACF Extended – Options.
 *
 * @author Alexander Kucherov (avdkucherov@gmail.com)
 */

namespace Scm\Archived_Post_Status;

use Scm\Acf_Extended\ConfigStorage;

class Options
{
    public const ARCHIVE_STATUS = 'archive';

    /**
     * Initialize hooks.
     */
    public function __construct()
    {
        // Hide Archived on All edit tab.
        add_filter('aps_status_arg_public', '__return_false');
        add_filter('aps_status_arg_private', '__return_false');
        add_filter('aps_status_arg_show_in_admin_all_list', '__return_false');

        // Register Archive bulk action.
        $postTypes = array_unique(
            array_merge(
                get_post_types(['public' => true, '_builtin' => true]),
                array_keys(ConfigStorage::getCustomPostTypes()),
                ['advanced_ads']
            )
        );
        // @warning: Haymarket legacy.
        if (wp_get_theme()->get('Name') === 'Haymarket') {
            $postTypes = array_merge($postTypes, [
                'cra_whitepaper',
                'convertr_campaign',
                'newsml_post',
                'hm-section-front',
                'hm-html',
                'hm-pointer-post',
                'hm-slideshow',
                'hm-newsletter-issue',
                'hm-webcast',
                'hm-product-review',
                'hm-group-test',
            ]);
        }
        foreach ($postTypes as $postType) {
            add_filter('bulk_actions-edit-' . $postType, [$this, 'registerBulkActionArchive']);
            add_filter(
                'handle_bulk_actions-edit-' . $postType,
                [$this, 'registerBulkActionArchiveHandler'],
                10,
                3
            );
        }
        add_action('admin_notices', [$this, 'registerBulkActionArchiveAdminNotice']);
    }

    /**
     * Add archive bulk action option.
     *
     * @param array $bulkActions List of actions.
     * @return mixed Updated actions.
     */
    public function registerBulkActionArchive(array $bulkActions): array
    {
        $bulkActions['archive'] = __('Archive', 'archive');
        return $bulkActions;
    }

    /**
     * Archive posts bulk action callback.
     *
     * @param string $redirectTo Redirect to link.
     * @param string $doAction Name of action.
     * @param array $postIds List of posts to process.
     *
     * @return string Redirect to link.
     */
    public function registerBulkActionArchiveHandler(string $redirectTo, string $doAction, array $postIds): string
    {
        if ($doAction !== 'archive') {
            return $redirectTo;
        }
        foreach ($postIds as $postId) {
            wp_update_post(
                [
                    'ID' => $postId,
                    'post_status' => 'archive',
                ]
            );
        }
        $redirectTo = add_query_arg('_wpnonce', wp_create_nonce('archive-action'), $redirectTo);
        return add_query_arg('bulk_archived_posts', count($postIds), $redirectTo);
    }

    /**
     *  Admin notice message of processed posts @see registerBulkActionArchiveHandler().
     */
    public function registerBulkActionArchiveAdminNotice(): void
    {
        $isNonce = isset($_REQUEST['_wpnonce'])
            && wp_verify_nonce($_REQUEST['_wpnonce'], 'archive-action');
        if ($isNonce && !empty($_REQUEST['bulk_archived_posts'])) {
            $count = (int)$_REQUEST['bulk_archived_posts'];
            printf(
                '<div id="message" class="updated fade">' .
                _n(
                    'Archived %s post.',
                    'Archived %s posts.',
                    $count,
                    'archived'
                ) . '</div>',
                $count
            );
        }
    }
}
