<?php

/**
 * Post class, custom migrations etc.
 *
 * @author Eugene Yakovenko <yakoveka@gmail.com>
 */

namespace Scm\Entity;

use Cra\CtPeople\PeopleCT;
use Scm\Tools\Logger;

class MergePosts
{
    private const COLLECTION_WIDGET_FIELD_AUTHOR = 'field_62726b0e44367';

    /**
     * @var string|int Original post ID.
     *
     * @access protected
     */
    protected string|int $originalId;

    /**
     * @var string|int Duplicate post ID.
     *
     * @access protected
     */
    protected string|int $duplicateId;

    /**
     * Post constructor.
     * @param string|int $originalId
     * @param string|int $duplicateId
     */
    public function __construct(string|int $originalId, string|int $duplicateId)
    {
        $this->originalId = $originalId;
        $this->duplicateId = $duplicateId;
    }

    /**
     * Text-related fields.
     *
     * @param string $duplicateValue
     * @param string $keepValue
     * @param string $fieldName
     * @param string $groupName
     *
     * @return void
     */
    private function mergeTextFields(
        string $duplicateValue,
        string $keepValue,
        string $fieldName,
        string $groupName
    ): void {
        if (empty($keepValue)) {
            update_field($fieldName, $duplicateValue, $this->originalId);
            Logger::log("Updated $fieldName for the post $this->originalId", 'status');
            return;
        }
        if ($duplicateValue !== $keepValue) {
            Logger::log(
                // phpcs:ignore
                "Conflict in field $fieldName of $groupName; duplicate value: $duplicateValue, kept value: $keepValue; post_id: $this->originalId",
                'status'
            );
        }
    }

    /**
     * Prepare value for repeater field by column key.
     *
     * @param array $preparedValue
     * @param string $columnKey
     *
     * @return array
     */
    private function prepareRepeaterFieldByColumnKey(array $preparedValue, string $columnKey): array
    {
        $uniqueCompanies = array_unique(array_column($preparedValue, $columnKey));
        $duplicates = array_diff(array_keys($preparedValue), array_keys($uniqueCompanies));
        foreach ($duplicates as $duplicate) {
            unset($preparedValue[$duplicate]);
        }

        return $preparedValue;
    }

    /**
     * Prepare value for repeater field by all its subfields.
     *
     * @param array $preparedValue
     * @param array $allEntries
     *
     * @return array
     */
    private function prepareRepeaterFieldByAllEntries(
        array $preparedValue,
        array $allEntries
    ): array {
        $regions = array_filter(array_map(function ($term) use ($allEntries) {
            $termsList = [];
            foreach ($allEntries as $subfield) {
                $termsList[$subfield] = is_array($term[$subfield])
                    ? array_map(function ($subfieldTerm) {
                        return $subfieldTerm->term_id;
                    }, $term[$subfield])
                    : null;
            }

            $termsList = array_filter($termsList);

            // Check if all the subfields are present.
            return !empty(array_diff(array_keys($termsList), $allEntries))
                ? $termsList
                : null;
        }, $preparedValue));

        // Remove duplicate from associative regions array.
        return array_map(
            'unserialize',
            array_unique(array_map('serialize', $regions))
        );
    }

    /**
     * Repeater fields.
     *
     * @param mixed $keepValue
     * @param mixed $duplicateValue
     * @param string $fieldKey
     * @param string $fieldName
     *
     * @return void
     */
    private function mergeRepeaterFields(
        mixed $keepValue,
        mixed $duplicateValue,
        string $fieldKey,
        string $fieldName
    ): void {
        if (is_array($keepValue) && is_array($duplicateValue)) {
            $preparedValue = array_merge($keepValue, $duplicateValue);

            if ($fieldKey === PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES) {
                $preparedValue = $this->prepareRepeaterFieldByColumnKey(
                    $preparedValue,
                    'job_title'
                );
            }

            if ($fieldKey === PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_REGIONS_COLLECTION) {
                $preparedValue = $this->prepareRepeaterFieldByAllEntries(
                    $preparedValue,
                    ['swoogo_community_region', 'swoogo_speaker_type']
                );
            }

            update_field($fieldKey, $preparedValue, $this->originalId);
            Logger::log("Updated $fieldName for the post $this->originalId", 'status');
        }
    }

    /**
     * Post or term reference fields.
     *
     * @param string $fieldKey
     * @param string $fieldName
     *
     * @return void
     */
    private function mergePostOrTermReferenceFields(string $fieldKey, string $fieldName): void
    {
        $keepValue = get_field($fieldKey, $this->originalId, false);
        $dupValue = get_field($fieldKey, $this->duplicateId, false);
        $valueToSet = empty($keepValue) ? $dupValue
            : (in_array(
                gettype($keepValue),
                ['string', 'integer']
            ) ? $keepValue : array_unique(array_merge(
                $keepValue,
                $dupValue
            )));

        update_field($fieldKey, $valueToSet, $this->originalId);

        Logger::log("Updated $fieldName for the post $this->originalId", 'status');
    }

    /**
     * Merges duplicate person data into the original person.
     * For now, supports People post type only.
     *
     * @return void
     */
    public function mergeDuplicateByIds(): void
    {
        $groups = acf_get_field_groups(['post_id' => $this->originalId]);

        foreach ($groups as $group) {
            foreach (acf_get_fields($group['key']) as $field) {
                $keepValue = get_field($field['key'], $this->originalId);
                $dupValue = get_field($field['key'], $this->duplicateId);

                if (empty($dupValue)) {
                    continue;
                }

                switch ($field['type']) {
                    case 'url':
                    case 'email':
                    case 'wysiwyg':
                    case 'text':
                        if (isset($field['label']) && isset($group['label'])) {
                            $this->mergeTextFields(
                                $dupValue,
                                $keepValue,
                                $field['label'],
                                $group['label']
                            );
                        }
                        break;
                    case 'repeater':
                        if (isset($field['key']) && isset($field['label'])) {
                            $this->mergeRepeaterFields(
                                $keepValue,
                                $dupValue,
                                $field['key'],
                                $field['label']
                            );
                        }
                        break;
                    case 'post_object':
                    case 'acfe_taxonomy_terms':
                    case 'taxonomy':
                        if (isset($field['key']) && isset($field['label'])) {
                            $this->mergePostOrTermReferenceFields($field['key'], $field['label']);
                        }
                        break;
                    case 'image':
                    default:
                        break;
                }
            }
        }

        $oldPostLink = get_permalink($this->duplicateId);
        $keepPostLink = get_permalink($this->originalId);

        wp_update_post([
            'ID' => $this->duplicateId,
            'post_status' => 'trash',
        ]);

        Logger::log(
            "Post $this->duplicateId was put to trash, it was the duplicate of $this->originalId",
            'status'
        );
        Logger::log("Create redirect from $oldPostLink -> $keepPostLink", 'status');
    }

    /**
     * Updates author for post that was authored by duplicate person.
     *
     * @return void
     */
    public function migrateDuplicateAuthorData(): void
    {
        global $wpdb;

        // phpcs:ignore
        $authoredPosts = $wpdb->get_results("SELECT post_id, meta_value FROM wp_postmeta WHERE meta_value LIKE '%\"$this->duplicateId\"%' and meta_key = 'author'");

        foreach ($authoredPosts as $authoredPost) {
            $metaValue = unserialize($authoredPost->meta_value);
            if (is_array($metaValue)) {
                $key = array_search($this->duplicateId, $metaValue);
                $metaValue[$key] = $this->originalId;

                update_field(
                    self::COLLECTION_WIDGET_FIELD_AUTHOR,
                    $metaValue,
                    $authoredPost->post_id
                );

                Logger::log("Author of post $authoredPost->post_id was updated", 'status');
            }
        }
    }
}
