<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/CISO-100
 */

declare(strict_types=1);

use Cra\CtPeople\People;

/**
 * Copy CISO Leadership Board community values (Community Region, Speaker Type) to a Region Collection field.
 */
function ciso100_ct_people_ri_002(): void
{
    $speakerTypeTermIds = get_terms(['taxonomy' => People::TAXONOMY__SWOOGO_SPEAKER_TYPE, 'fields' => 'ids']);

    $personsQuery = new WP_Query([
        'post_type' => People::POST_TYPE,
        'post_status' => 'publish',
        'nopaging' => true,
        'tax_query' => [
            'relation' => 'AND',
            [
                'taxonomy' => People::TAXONOMY__SWOOGO_SPEAKER_TYPE,
                'field' => 'term_id',
                'operator' => 'IN',
                'terms' => $speakerTypeTermIds,
            ],
            [
                'taxonomy' => People::TAXONOMY__COMMUNITY_REGION,
                'field' => 'term_id',
                'operator' => 'IN',
                'terms' => get_terms(['taxonomy' => People::TAXONOMY__COMMUNITY_REGION, 'fields' => 'ids']),
            ]
        ]
    ]);

    $personIds = array_map(static fn ($post) => $post->ID, $personsQuery->get_posts());
    foreach ($personIds as $personId) {
        $speakerTypes = get_field(People::FIELD__SWOOGO_SPEAKER_TYPE, $personId, false);
        // Remove any redundant values, left only existing speaker type IDs
        $speakerTypes = array_filter($speakerTypes, static fn (int $id) => in_array($id, $speakerTypeTermIds));
        // Need to place child terms first
        if (!get_term($speakerTypes[0])->parent) {
            $speakerTypes = array_reverse($speakerTypes);
        }
        $regions = get_field(People::FIELD__SWOOGO_COMMUNITY_REGION, $personId, false);
        $collectionValue = [
            [
                People::FIELD__SWOOGO_REGIONS_COLLECTION__REGION => $regions,
                People::FIELD__SWOOGO_REGIONS_COLLECTION__SPEAKER_TYPE => $speakerTypes,
            ]
        ];
        update_field(People::FIELD__SWOOGO_REGIONS_COLLECTION, $collectionValue, $personId);
    }
}
