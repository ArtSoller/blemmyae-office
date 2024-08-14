<?php
/**
 * @licence PROPRIETARY
 *
 * @author CRA
 *
 * @link: https://cra.myjetbrains.com/youtrack/issue/MEM-50
 *
 * @phpcs disable NeutronStandard.Globals.DisallowGlobalFunctions
 */

declare(strict_types=1);

use Cra\CtLearning\Learning;
use Cra\CtPeople\People;
use Scm\Tools\Logger;

const FIELD__CSC_FIELD_GROUP = 'field_6319b0346c5b1';
const FIELD__CSC_HUBSPOT_SPEAKERS = 'field_6322d57b45055';
const FIELD__CSC_LEAD_BY = 'field_6319b1316c5b3';

/**
 * Add CSC Event speakers to "Learning Advanced -> Speakers" field.
 * Copy "Lead By" person in that field for task forces.
 * For other event types find speakers by their HubSpot IDs and add them to "Speakers" field.
 *
 * @return void
 */
function mem50_ct_learning_ri_001(): void
{
    $taskForces = queryCscLearningsByType('Task Force');
    Logger::log(sprintf('Found %d CSC Task Forces', count($taskForces)), 'info');
    $cisoBoardrooms = queryCscLearningsByType('CISO Boardroom');
    Logger::log(sprintf('Found %d CSC CISO Boardrooms', count($cisoBoardrooms)), 'info');
    $virtualBriefings = queryCscLearningsByType('Virtual Briefing');
    Logger::log(sprintf('Found %d CSC Virtual Briefings', count($virtualBriefings)), 'info');

    foreach ($taskForces as $taskForce) {
        setMemberPortalVendor($taskForce->ID);
        $cscFields = get_field(FIELD__CSC_FIELD_GROUP, $taskForce->ID, false) ?: [];
        if (empty($cscFields)) {
            Logger::log(sprintf(
                'Missing CSC specific fields for Learning #%d, skipping.', $taskForce->ID),
                'warning'
            );
            continue;
        }
        $leadById = (int) $cscFields[FIELD__CSC_LEAD_BY];
        if ($leadById) {
            addSpeakerTermToPerson($leadById);
            addNewEventSpeaker($taskForce->ID, $leadById);
        }
    }
    foreach ($cisoBoardrooms as $boardroom) {
        setMemberPortalVendor($boardroom->ID);
        migrateSpeakersFromCscField($boardroom->ID);
    }
    foreach ($virtualBriefings as $briefing) {
        migrateSpeakersFromCscField($briefing->ID);
    }
}

/**
 * Set "Vendor" ACF learning field to "CSC Member Portal".
 *
 * @param int $eventId Event (Learning) post ID.
 *
 * @return void
 */
function setMemberPortalVendor(int $eventId): void
{
    if (!empty(get_field(Learning::FIELD__VENDOR, $eventId, false))) {
        Logger::log(sprintf(
            'Learning #%d already has Vendor field set.', $eventId),
            'info'
        );
        return;
    }
    $vendor = [
        [
            'acf_fc_layout' => 'member_portal',
            'link' => '', // Intentionally empty.
        ],
    ];
    update_field(Learning::FIELD__VENDOR, $vendor, $eventId);
    Logger::log(sprintf(
        'Vendor "CSC Member Portal" successfully set for Learning #%d.', $eventId),
        'success'
    );
}

/**
 * Copy event speakers from CSC specific field to main "Speakers" field.
 *
 * @param int $eventId Event (Learning) post ID.
 *
 * @return void
 */
function migrateSpeakersFromCscField(int $eventId): void
{
    $cscFields = get_field(FIELD__CSC_FIELD_GROUP, $eventId, false) ?: [];
    if (empty($cscFields)) {
        Logger::log(sprintf(
            'Missing CSC specific fields for Learning #%d, skipping.', $eventId),
            'warning'
        );
        return;
    }
    $hubspotSpeakers = $cscFields[FIELD__CSC_HUBSPOT_SPEAKERS];
    if (empty($hubspotSpeakers)) {
        Logger::log(sprintf(
            'Missing speakers for Learning #%d, skipping.', $eventId),
            'warning'
        );
        return;
    }
    // Convert sting to an array
    $hubspotSpeakers = str_contains($hubspotSpeakers, ',') ? explode(',', $hubspotSpeakers) : [$hubspotSpeakers];
    foreach ($hubspotSpeakers as $speakerHsId) {
        $speakerId = findSpeakerByHubspotId($speakerHsId);
        addSpeakerTermToPerson($speakerId);
        addNewEventSpeaker($eventId, $speakerId);
    }
}

/**
 * Add "Speaker" people type term for a person if it's missing.
 *
 * @param int $personId Person post ID.
 *
 * @return void
 */
function addSpeakerTermToPerson(int $personId): void
{
    $peopleTypes = get_field(People::FIELD__PEOPLE_TYPE, $personId, false) ?: [];
    if (in_array(People::PEOPLE_TYPE__TERM__SPEAKER__ID, $peopleTypes)) {
        Logger::log(sprintf(
            'Person #%d already has "Speaker" term, skipping.', $personId),
            'info'
        );
        return;
    }
    $peopleTypes[] = People::PEOPLE_TYPE__TERM__SPEAKER__ID;
    update_field(People::FIELD__PEOPLE_TYPE, $peopleTypes, $personId);
    wp_set_post_terms($personId, $peopleTypes, People::TAXONOMY__PEOPLE_TYPE);
    Logger::log(sprintf(
        'Successfully added term "Speaker" to Person #%d people types.', $personId),
        'success'
    );
}

/**
 * Add new entry to event "Speakers" field.
 *
 * @param int $eventId Event (Learning) post ID.
 * @param int $speakerId Person post ID.
 *
 * @return void
 */
function addNewEventSpeaker(int $eventId, int $speakerId): void
{
    $currentSpeakers = get_field(Learning::FIELD__SPEAKERS, $eventId, false) ?: [];
    if (empty($currentSpeakers)) {
        $currentSpeakers[] = [
            Learning::FIELD__SPEAKERS_SPEAKER => [$speakerId],
        ];
        update_field(Learning::FIELD__SPEAKERS, $currentSpeakers, $eventId);
        Logger::log(sprintf(
            'Person #%d was added to learning #%d speakers.', $speakerId, $eventId),
            'success'
        );
        return;
    }
    $speakerIds = array_column($currentSpeakers, Learning::FIELD__SPEAKERS_SPEAKER);
    if (in_array([$speakerId], $speakerIds)) {
        Logger::log(sprintf(
            'Person #%d is already listed in learning #%d speakers.', $speakerId, $eventId),
            'info'
        );
        return;
    }
    $currentSpeakers[] = [
        Learning::FIELD__SPEAKERS_SPEAKER => [$speakerId],
    ];
    update_field(Learning::FIELD__SPEAKERS, $currentSpeakers, $eventId);
    Logger::log(sprintf(
        'Person #%d was added to learning #%d speakers.', $speakerId, $eventId),
        'success'
    );
}

/**
 * Find person by his HubSpot ID.
 *
 * @param string $hubspotId Speaker HubSpot ID.
 *
 * @return int Person post ID.
 */
function findSpeakerByHubspotId(string $hubspotId): int
{
    $query = new WP_Query([
        'post_type' => People::POST_TYPE,
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key' => 'csc_hubspot_id',
                'value' => $hubspotId,
            ],
        ],
    ]);

    return $query->next_post()->ID ?? 0;
}

/**
 * Find learnings by learning type name.
 *
 * @param string $type Learning Type term name, ex. "CISO Boardroom".
 *
 * @return array Array of published learnings with a given type.
 */
function queryCscLearningsByType(string $type): array
{
    $termId = get_term_by('name', $type, Learning::TAXONOMY__LEARNING_TYPE)->term_id ?? 0;
    if (!$termId) {
        // Term does not exist.
        return [];
    }
    return (new WP_Query([
        'post_type' => Learning::POST_TYPE,
        'post_status' => 'publish',
        'nopaging' => true,
        'tax_query' => [
            [
                'taxonomy' => Learning::TAXONOMY__LEARNING_TYPE,
                'field' => 'term_id',
                'terms' => $termId,
            ],
        ],
    ]))->posts;
}
