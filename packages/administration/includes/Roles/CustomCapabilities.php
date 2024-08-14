<?php

/**
 * Roles – Custom Capabilities.
 *
 * @author Alexander Kucherov (avdkucherov@gmail.com)
 */

namespace Scm\Roles;

use WP_User;

class CustomCapabilities
{
    /**
     * Initialize hooks.
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'editorAllowMenuEditAccess'], 10);

        // Hook these up to 100 so that it overrides the original register_taxonomy function.
        add_action('init', [$this, 'editorAllowModifyTaxonomy'], 100);
        add_action('init', [$this, 'restrictTaxonomiesEdit'], 100);
    }

    /**
     * Allow editors to see access the Menus page under Appearance, but hide other options.
     * @note: Users who know the correct path to the hidden options can still access them. Not sure, why this is
     *  a part of theme configuration.
     */
    public function editorAllowMenuEditAccess(): void
    {
        $user = wp_get_current_user();
        $type = 'editor';
        $capability = 'edit_theme_options';

        // Check if the current user is an Editor.
        if (($user instanceof WP_User) && in_array($type, (array)$user->roles, true)) {
            // They're an editor, so grant the edit_theme_options capability if they don't have it.
            if (!current_user_can($capability)) {
                $role = get_role($type);
                if ($role instanceof \WP_Role) {
                    // Since no `edit_nav_menus` capabilities granting greater `edit_theme_options'`.
                    $role->add_cap($capability);
                }
            }

            // Hiding sub-menus from dashboard, still accessible via direct uri.
            // Sub-menu slug.
            $slug = 'themes.php';

            // Hide the Themes page.
            remove_submenu_page($slug, $slug);
            // Hide the Widgets page.
            remove_submenu_page($slug, 'widgets.php');
            // Hide the Customize page.
            remove_submenu_page($slug, 'customize.php');

            // Remove Customize from the Appearance submenu.
            // @note: Since removing `customize` won't help as `edit_theme_options` is a greater permission.
            global $submenu;
            unset($submenu[$slug][6]);
        }
    }

    /**
     * Allows Editors to manage taxonomies.
     *
     * @return void
     */
    public function editorAllowModifyTaxonomy(): void
    {
        $editor = get_role('editor');
        if (!$editor) {
            return;
        }
        $caps = [
            'manage_categories'
        ];
        foreach ($caps as $cap) {
            $editor->add_cap($cap);
        }
    }

    /**
     * Restricts taxonomies edit for every role except administrator.
     * Need to call this method inside an 'init' action to register all custom taxonomies before.
     *
     * @return void
     */
    public function restrictTaxonomiesEdit(): void
    {
        if (current_user_can('administrator')) {
            return;
        }
        foreach (get_taxonomies() as $taxonomy) {
            add_action($taxonomy . '_edit_form', [$this, 'disableTermDefaultInputs'], 100);
            add_filter($taxonomy . '_row_actions', [$this, 'disableTermQuickEdit'], 100);
        }
    }

    /**
     * Disables 'Name', 'Slug' and 'Parent' inputs on term edit page.
     * Taxonomy is defined in a hook that calls this method
     *
     * @return void
     */
    public function disableTermDefaultInputs(): void
    {
        wp_register_script(
            'disable-default-term-inputs',
            plugins_url('src/editor/disableDefaultTaxonomyInputs.js', dirname(__DIR__)),
            [],
            ADMINISTRATION_PLUGIN_VERSION
        );
        wp_enqueue_script('disable-default-term-inputs');
    }

    /**
     * Disables 'Quick Edit' and 'Delete' actions for a taxonomy on its page.
     * Taxonomy is defined in a hook that calls this method.
     *
     * @param array $actions ['edit', 'view', 'delete', 'inline hide-if-no-js'] (Last one is a 'quick edit').
     *
     * @return array         Filtered array of actions.
     */
    public function disableTermQuickEdit(array $actions = []): array
    {
        unset($actions['inline hide-if-no-js']);
        unset($actions['delete']);

        return $actions;
    }
}
