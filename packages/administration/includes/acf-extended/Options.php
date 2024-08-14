<?php

/**
 * ACF Extended – Options.
 *
 * @author Alexander Kucherov (avdkucherov@gmail.com)
 */

namespace Scm\Acf_Extended;

use Scm\Tools\Utils;

class Options
{
    /**
     * Initialize hooks.
     */
    public function __construct()
    {
        if (!class_exists('ACFE')) {
            return;
        }

        add_action('acf/init', [$this, 'acfeModules'], 10);
        add_filter('acfe/fields/taxonomy_terms/choices', [$this, 'taxonomyTermsChoices'], 10, 3);
        // Still required for ajax results.
        add_filter('acfe/fields/taxonomy_terms/result', [$this, 'taxonomyTermsResult'], 10, 4);
    }

    /**
     * Disables specific ACF Extended Modules.
     */
    public function acfeModules(): void
    {
        // Disable Ajax Author box
        acfe_update_setting('modules/author', false);

        // Disable ACF > Categories
        acfe_update_setting('modules/categories', false);

        // Disable ACF > Forms
        acfe_update_setting('modules/forms', false);

        // Disable Multilingual Compatibility
        acfe_update_setting('modules/multilang', false);
    }

    /**
     * Custom filtering and sorting for available choices.
     *
     * @param array $choices
     * @param array $field
     * @param mixed $postId
     * @return mixed
     */
    public function taxonomyTermsChoices(array $choices, array $field, $postId): array
    {
        foreach ($choices as &$taxonomyTerms) {
            $displayHierarchical = (bool)($field['display_hierarchical'] ?? false);
            $taxonomyTerms = array_map(
                static fn($term) => Utils::filterTermName($term, $displayHierarchical),
                $taxonomyTerms
            );

            // Do not sort terms if they will be displayed hierarchically,
            // sort otherwise
            if (!$displayHierarchical) {
                uasort($taxonomyTerms, static fn($left, $right) => strcmp($left, $right));
            }
        }
        unset($taxonomyTerms);

        return $choices;
    }

    /**
     * Strips '-' term's title prefix output inside admin widgets.
     *
     * @param string $text Term name result
     * @param mixed $term Term object
     * @param array $field Field settings
     * @param mixed $postId Current Post ID
     * @return string
     */
    public function taxonomyTermsResult(string $text, $term, array $field, $postId): string
    {
        $displayHierarchical = (bool)($field['display_hierarchical'] ?? false);
        // Change term name result.
        return Utils::filterTermName($text, $displayHierarchical);
    }
}
