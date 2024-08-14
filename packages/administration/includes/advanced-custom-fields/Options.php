<?php

/**
 * Options.
 *
 * @author Alexander Kucherov (avdkucherov@gmail.com)
 */

namespace Scm\Advanced_Custom_Fields;

use Cra\CtEditorial\EditorialCT;
use Cra\CtEditorial\ScAwardNominee;
use Scm\Acf_Extended\ConfigStorage;
use Scm\Tools\Logger;
use Scm\Tools\Utils;
use WP_Term;
use Scm\WP_GraphQL\Utils as WPGraphQLUtils;

use function add_action;
use function add_filter;

class Options
{
    /**
     * Initialize hooks.
     */
    public function __construct()
    {
        add_filter('posts_where', [$this, 'prepareSubFieldsQuery']);
        add_filter('acf/format_value', [$this, 'acfNullifyEmpty'], 100, 3);

        foreach (ConfigStorage::getCustomPostTypes() as $cptPostTypeName => $cptPostType) {
            if ($cptPostType['show_in_rest'] ?? true) {
                add_filter(
                    'rest_prepare_' . $cptPostTypeName,
                    [$this, 'exposeRestApi'],
                    10,
                    3
                );
            }
        }


        // Debug preview with custom fields.
        add_filter('_wp_post_revision_fields', [$this, 'fieldDebugPreview']);
        add_action('edit_form_after_title', [$this, 'inputDebugPreview']);

        add_filter('acf/fields/taxonomy/result', [$this, 'taxonomyResult'], 10, 4);
        // ACF Options on-demand-revalidation page.
        add_action('acf/options_page/save', [$this, 'startPageRevalidation'], 10, 2);
        add_action('admin_notices', [$this, 'handleCustomRevalidationNotices']);

        // Apply to all fields.
        add_filter('acf/update_value/type=taxonomy', [$this, 'updateValue'], 10, 4);
        add_filter('acf/update_value/type=acfe_taxonomy_terms', [$this, 'updateValue'], 10, 4);

        // Work with topics.
        add_filter(
            'acf/update_value/key=' . EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC,
            [$this, 'updateTopicField'],
            10,
            4
        );
        add_filter(
            'acf/prepare_field/key=' . EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_PARENT_TOPIC,
            [$this, 'makeFieldReadonly']
        );
        $this->addUpdateValueParentTopicFilter();

        add_action(
            'acf/render_field_settings/type=taxonomy',
            [$this, 'renderPopulateParentalTermsOption'],
            10,
            4
        );
        add_action(
            'acf/render_field_settings/type=acfe_taxonomy_terms',
            [$this, 'renderPopulateParentalTermsOption'],
            10,
            4
        );
        add_action(
            'acf/render_field_settings/type=text',
            [$this, 'renderCounter'],
            10,
            4
        );
        add_action(
            'acf/render_field_settings/type=textarea',
            [$this, 'renderCounter'],
            10,
            4
        );
        add_action(
            'acf/render_field/type=text',
            [$this, 'renderAcfFieldsWithCharactersCounter'],
            20,
            1
        );
        add_action(
            'acf/render_field/type=textarea',
            [$this, 'renderAcfFieldsWithCharactersCounter'],
            20,
            1
        );
    }

    public function renderAcfFieldsWithCharactersCounter($field)
    {
        $recommendedMin = $field['recommended_lower_limit'] ?? 0;
        $recommendedMax = $field['recommended_upper_limit'] ?? 0;

        if (!$recommendedMax) {
            return;
        }

        $len = strlen($field['value']);

        $display = sprintf(
            "
            <span class=\"char-count\">
            <span class=\"count\">Number of characters: %s. </span>
            Recommended number of characters: 
            <span class=\"recommended-min\" data-recommended-min=\"%s\">%s</span>
            <span class=\"recommended-max\" data-recommended-max=\"%s\">%s</span></span>
            ",
            $len,
            $recommendedMin,
            $recommendedMin ? $recommendedMin . ' - ' : 'up to ',
            $recommendedMax,
            $recommendedMax
        );

        echo $display;
    }

    /**
     * Adds hidden field value, which never gets populated into DB. Ensures that preview mode works.
     *
     * @param array $fields
     *
     * @return mixed
     */
    public function fieldDebugPreview(array $fields = []): array
    {
        $fields['debug_preview'] = 'debug_preview';

        return $fields;
    }

    /**
     * Adds hidden field, which never gets populated into DB. Ensures that preview mode works.
     */
    public function inputDebugPreview(): void
    {
        echo '<input type="hidden" name="debug_preview" value="debug_preview">';
    }

    /**
     * Register acf fields to WordPress API.
     *
     * https://support.advancedcustomfields.com/forums/topic/json-rest-api-and-acf/
     * @param mixed $response
     * @param mixed $post
     * @param mixed $request
     *
     * @return mixed
     */
    public function exposeRestApi(mixed $response, mixed $post, mixed $request): mixed
    {
        if (!function_exists('get_fields')) {
            return $response;
        }

        if (isset($post)) {
            $acf = get_fields($post->id);
            $response->data['acf'] = $acf;
        }

        return $response;
    }

    /**
     * Return `null` if an empty value is returned from ACF.
     *
     * @param mixed $value
     * @param int|string $postId
     * @param array $field
     *
     * @return mixed
     */
    public function acfNullifyEmpty(mixed $value, mixed $postId, mixed $field): mixed
    {
        return empty($value) ? null : $value;
    }

    /**
     * Strips '-' term's title prefix output inside admin widgets.
     *
     * @param string $text Term name result
     * @param mixed $term Term object
     * @param array $field Field settings
     * @param mixed $postId Current Post ID
     *
     * @return string
     */
    public function taxonomyResult(string $text, mixed $term, array $field, mixed $postId): string
    {
        $displayHierarchical = (bool)($field['display_hierarchical'] ?? false);
        // Change term name result.
        return Utils::filterTermName($text, $displayHierarchical);
    }

    /**
     * Adds Recommended upper and lower limits number fields to text and textarea acf fields
     *
     * @param $field
     */
    public function renderCounter($field): void
    {
        acf_render_field_setting($field, [
            'label' => __('Recommended upper limit'),
            'instructions' =>
                "
                Recommended upper limit of characters count for editors. Value is required for
                character counter to render.
                ",
            'name' => 'recommended_upper_limit',
            'type' => 'number',
            'ui' => 1,
            "default_value" => 0,
        ]);
        acf_render_field_setting($field, [
            'label' => __('Recommended lower limit'),
            'instructions' =>
                "
                Recommended lower limit of characters count for editors, value is not required
                if upper limit is set.
                ",
            'name' => 'recommended_lower_limit',
            'type' => 'number',
            'ui' => 1,
            "default_value" => 0,
        ]);
    }

    /**
     * Adds "Populate Parental Terms" option to taxonomies.
     *
     * @param $field
     */
    public function renderPopulateParentalTermsOption($field): void
    {
        acf_render_field_setting($field, [
            'label' => __('Populate Parental Terms?'),
            'instructions' => '',
            'name' => 'populate_parental_terms',
            'type' => 'true_false',
            'ui' => 1,
        ]);
        acf_render_field_setting($field, [
            'label' => __('Display Hierarchical?'),
            'instructions' => '',
            'name' => 'display_hierarchical',
            'type' => 'true_false',
            'ui' => 1,
            "default_value" => 0,
        ]);
    }

    /**
     * Add update value filter for parent topic field.
     *
     * @return void
     */
    public function addUpdateValueParentTopicFilter(): void
    {
        add_filter(
            'acf/update_value/key=' . EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_PARENT_TOPIC,
            [$this, 'updateParentTopicField'],
            10,
            4
        );
    }

    /**
     * Remove update value filter for parent topic field.
     *
     * @return void
     */
    public function removeUpdateValueParentTopicFilter(): void
    {
        remove_filter(
            'acf/update_value/key=' . EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_PARENT_TOPIC,
            [$this, 'updateParentTopicField']
        );
    }

    /**
     * Update topic field.
     *
     * We need to be sure that when we trigger update topic field => we will update parent
     * topic field too.
     *
     * @param $value
     * @param $postId
     * @param $field
     * @param $original
     *
     * @return array|mixed
     */
    public function updateTopicField($value, $postId, $field, $original): mixed
    {
        // If value is empty => nothing to do.
        if (empty($value)) {
            return $value;
        }

        $parentTopics = $this->populateTermArrayWithParents($value, $field['taxonomy']);
        $parentTopics = array_diff($parentTopics, $value);

        // Remove parent topic filter to avoid additional save.
        $this->removeUpdateValueParentTopicFilter();

        // Update parent topic.
        update_field(
            EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_PARENT_TOPIC,
            $parentTopics,
            $postId
        );

        // Return parent topic filter.
        $this->addUpdateValueParentTopicFilter();

        // Return new value.
        return $value;
    }

    /**
     * Update parent topic field.
     *
     * @param $value
     * @param $postId
     * @param $field
     * @param $original
     *
     * @return array|mixed
     */
    public function updateParentTopicField($value, $postId, $field, $original): mixed
    {
        // This field should be updated only when topic field is updated.
        $topicValue = get_field(EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC, $postId, false);

        // Have to populate parent topics, only when topics field is fileld.
        if (!empty($topicValue)) {
            $parentTopics = $this->populateTermArrayWithParents($topicValue, $field['taxonomy']);
            $parentTopics = array_diff($parentTopics, $topicValue);
        }

        return $parentTopics ?? [];
    }

    /**
     * Populates taxonomy topic field.
     *
     * @param $value
     * @param $postId
     * @param $field
     * @param $original
     *
     * @return array|mixed
     */
    public function updateValue($value, $postId, $field, $original): mixed
    {
        if ($field['key'] === ScAwardNominee::PARENT_CATEGORY_FIELD) {
            $scAwardValue = get_field(ScAwardNominee::CATEGORY_FIELD, $postId);
            if ($scAwardValue instanceof WP_Term) {
                $scAwardValue = [$scAwardValue];
            }
            $scAwardTermIds = array_map(static fn($term) => (string)$term->term_id, $scAwardValue);
            $parentScAwards = $this->populateTermArrayWithParents(
                $scAwardTermIds,
                $field['taxonomy']
            );
            return array_diff($parentScAwards, $scAwardTermIds);
        }

        if (is_array($value) && !empty($field['taxonomy'])) {
            // Taxonomy is able to be either a string or an array.
            // To correctly execute foreach need to wrap string into array first.
            $taxArray = (is_array($field['taxonomy']) ? $field['taxonomy'] : [$field['taxonomy']]);
            foreach ($taxArray as $taxonomy) {
                if (
                    !empty($field['populate_parental_terms']) &&
                    $field['key'] !== EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC &&
                    is_taxonomy_hierarchical($taxonomy)
                ) {
                    return $this->populateTermArrayWithParents($value, [$taxonomy]);
                }
            }
        }

        return $value;
    }

    /**
     * Enriches term array with their hierarchical parents.
     *
     * @param array $ids
     * @param array $taxonomies
     *
     * @return array
     */
    public function populateTermArrayWithParents(array $ids, array $taxonomies): array
    {
        $parents = [];
        $terms = get_terms([
            'taxonomy' => $taxonomies,
            'include' => $ids,
            'orderby' => 'include',
            'hide_empty' => false,
        ]);
        foreach ($terms as $term) {
            // array_merge is too greedy inside loops.
            $parents = [
                ...$parents,
                ...[(string)$term->term_id],
                ...array_map(
                    static fn($value) => (string)$value,
                    get_ancestors($term->term_id, $term->taxonomy, 'taxonomy')
                ),
            ];
        }
        return array_unique($parents);
    }


    /**
     * Make field readonly.
     *
     * @param $field
     *
     * @return mixed
     */
    public function makeFieldReadonly($field): mixed
    {
        $field['disabled'] = 1;

        return $field;
    }

    /**
     * Update $ character to match select for subfields.
     * https://www.advancedcustomfields.com/resources/query-posts-custom-fields/#4-sub-custom-field-values
     * @param string $where
     * @return string
     */
    public function prepareSubFieldsQuery(string $where): string
    {
        return str_replace("meta_key = 'speakers_$", "meta_key LIKE 'speakers_%", $where);
    }

    /**
     * Starts page revalidation on form submit ('acf/options_page/save' action).
     *
     * @param int|string $page_name The name of the ACF page. Defaults to option.
     * @param string $option_page_slug The option page slug.
     *
     * @return void
     */
    public function startPageRevalidation(int|string $page_name, string $option_page_slug): void
    {
        if ($page_name !== 'options' && $option_page_slug !== 'on-demand-revalidation') {
            return;
        }

        $url = parse_url(get_field('revalidate_page_url', $page_name));
        // $url always exists because input has ACF url type and is required.
        $path = $url['path'] ?? '/';

        // Prepare URL.
        $wpHeadlessSecret = WPGraphQLUtils::secret();
        $revalidation_query = http_build_query([
            'secret' => $wpHeadlessSecret,
            'path' => $path
        ]);
        $revalidation_url = $url['scheme'] . '://' . $url['host'] . '/api/revalidate?' . $revalidation_query;

        // Send revalidation request to FE.
        $response = wp_remote_get($revalidation_url);

        if (is_wp_error($response)) {
            Logger::log($response->get_error_message(), 'error');
        } else {
            update_user_option(get_current_user_id(), 'page_revalidation_option', $response['response']);
        }
    }

    /**
     * Handles custom revalidation admin notices.
     *
     * @return void
     */
    public function handleCustomRevalidationNotices(): void
    {
        $revalidationOption = get_user_option('page_revalidation_option');

        if (!$revalidationOption) {
            return;
        }

        if ($revalidationOption['code'] === 200) {
            wp_admin_notice('The cache was revalidated', ['type' => 'success']);
        } else {
            wp_admin_notice('Something went wrong. Please try again', ['type' => 'error']);
        }

        delete_user_option(get_current_user_id(), 'page_revalidation_option');
    }
}
