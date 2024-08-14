<?php

/**
 * @link https://cra.myjetbrains.com/youtrack/issue/PORT-2510
 *
 * @author  Konstantin Gusev <konstantin.gusev@cyberriskalliance.com>
 * @license proprietary
 */

/** @noinspection PhpUnused */

declare(strict_types=1);

use Cra\CtPeople\PeopleCT;
use Cra\WebhookConsumer\Mapper\Vendor\Swoogo\Speaker;
use Cra\WebhookConsumer\WebhookMapping;
use Cra\WebhookConsumer\WebhookMessageHandler;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\WpCore;

/**
 * Remove Swoogo speaker webhook mappings.
 *
 * @return void
 */
function port2510_blemmyae_webhook_consumer_ri_001(): void
{
    try {
        WebhookMapping::deleteByVendorAndType(
            WebhookMessageHandler::VENDOR__SWOOGO,
            Speaker::TYPE
        );
    } catch (Exception $exception) {
        Logger::log($exception->getMessage(), 'error');
    }
}

/**
 * Set the next RI as already run because it must be run manually.
 *
 * @return void
 */
function port2510_blemmyae_webhook_consumer_ri_002(): void
{
    (new RI())->setStatus('port2510_blemmyae_webhook_consumer_ri_003', true);
}

const DUPLICATE_LEADERSHIP_BOARD_SPEAKER_SWOOGO_IDS = [
    793736,
];

const DUPLICATE_SPEAKER_POST_IDS = [
    715847,
];

/**
 * Fix Swoogo speakers.
 *
 * @return void
 */
function port2510_blemmyae_webhook_consumer_ri_003(): void
{
    global $wpdb;

    try {
        $leadershipBoardSpeakerNames = [];

        // Make sure that Leadership Board speakers are published.
        $leadershipBoardSpeakers = _port2510_get_speaker_ids_for_query(
            "SELECT post_id FROM $wpdb->postmeta
            WHERE meta_key = 'swoogo_direct_link' AND meta_value LIKE '%leadership_board_members%speaker%';"
        );
        foreach ($leadershipBoardSpeakers as $id) {
            $post = WpCore::getPost((int)$id);

            $firstName = trim(get_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_FIRST_NAME, $post->ID) ?? '');
            $middleName = trim(get_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_MIDDLE_NAME, $post->ID) ?? '');
            $lastName = trim(get_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_LAST_NAME, $post->ID) ?? '');
            $name = trim($middleName ? "$firstName $middleName $lastName" : "$firstName $lastName");

            // Delete bad speaker post.
            if (!$name) {
                $id = $post->ID;
                WpCore::deletePost($post->ID, true);
                Logger::log("Deleted Swoogo Leadership Board speaker without a name (ID=$id).", 'success');
                continue;
            }

            $swoogoId = (int)get_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_ID, $post->ID);
            if (in_array($swoogoId, DUPLICATE_LEADERSHIP_BOARD_SPEAKER_SWOOGO_IDS, true)) {
                $id = $post->ID;
                WpCore::deletePost($post->ID, true);
                Logger::log("Deleted duplicate Leadership Board speaker - $name (ID=$id).", 'success');
                continue;
            }

            $leadershipBoardSpeakerNames[] = $name;

            // Make sure that the post is published.
            if ($post->post_status !== 'publish') {
                wp_publish_post((int)$id);
                Logger::log("Published Leadership Board speaker - $name (ID=$post->ID).", 'success');
            }

            // Make sure that title matches person's name.
            if ($name !== $post->post_title) {
                WpCore::updatePost([
                    'ID' => $post->ID,
                    'post_title' => $name,
                ]);
                Logger::log("Updated title of Leadership Board speaker $name (ID=$post->ID).", 'success');
            }

            // Set Swoogo name fields.
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_FIRST_NAME, $firstName, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_MIDDLE_NAME, $middleName, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_LAST_NAME, $lastName, $post->ID);
            Logger::log("Updated Swoogo name fields of Leadership Board speaker $name (ID=$post->ID).", 'success');
        }

        // Delete other Swoogo speakers (they aren't used).
        $otherSwoogoSpeakers = _port2510_get_speaker_ids_for_query(
            "SELECT m.*, p.post_title
            FROM $wpdb->postmeta m
            INNER JOIN $wpdb->posts p ON p.ID = m.post_id
            WHERE m.meta_key = 'swoogo_direct_link'
            AND m.meta_value NOT LIKE '%leadership_board_members%'
            AND m.meta_value LIKE '%speaker%'
            AND p.post_status = 'publish';",
        );
        foreach ($otherSwoogoSpeakers as $id) {
            $post = WpCore::getPost((int)$id);

            $firstName = trim(get_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_FIRST_NAME, $post->ID) ?? '');
            $middleName = trim(get_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_MIDDLE_NAME, $post->ID) ?? '');
            $lastName = trim(get_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_LAST_NAME, $post->ID) ?? '');
            $name = trim($middleName ? "$firstName $middleName $lastName" : "$firstName $lastName");

            // Delete bad speaker post.
            if (!$name) {
                $id = $post->ID;
                WpCore::deletePost($post->ID, true);
                Logger::log("Deleted Swoogo speaker without a name (ID=$id).", 'success');
                continue;
            }

            // Skip hubspot person.
            $hubspotId = (int)get_field(PeopleCT::GROUP_CSC_PEOPLE_ADVANCED__FIELD_CSC_HUBSPOT_ID, $post->ID);
            if ($hubspotId) {
                Logger::log("Skipping CSC person - $name (ID=$id).", 'success');
                continue;
            }

            // Delete Leadership Board speaker duplicates.
            if (in_array($name, $leadershipBoardSpeakerNames, true)) {
                $id = $post->ID;
                WpCore::deletePost($post->ID, true);
                Logger::log("Deleted duplicate Swoogo Leadership Board speaker - $name (ID=$id).", 'success');
                continue;
            }

            // Delete speaker duplicates.
            if (in_array($post->ID, DUPLICATE_SPEAKER_POST_IDS, true)) {
                $id = $post->ID;
                WpCore::deletePost($post->ID, true);
                Logger::log("Deleted duplicate speaker - $name (ID=$id).", 'success');
                continue;
            }

            // Clear Swoogo fields.
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_FIRST_NAME, null, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_MIDDLE_NAME, null, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_LAST_NAME, null, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_ID, null, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_BIO, null, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_COMPANY, null, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_JOB_TITLE, null, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_PHONE, null, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_EMAIL, null, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_TWITTER, null, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_DIRECT_LINK, null, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_BIRTH_DATE, null, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_HEADSHOT, null, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_HASH, null, $post->ID);
            update_field(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_REGIONS_COLLECTION, [], $post->ID);
            WpCore::setPostTerms(PeopleCT::TAXONOMY__SWOOGO_SPEAKER_TYPE, [], $post->ID);
            WpCore::setPostTerms(PeopleCT::TAXONOMY__COMMUNITY_REGION, [], $post->ID);
            Logger::log("Reset Swoogo fields for speaker who's not from Leadership Board - $name.", 'success');
        }
    } catch (Exception $exception) {
        Logger::log($exception->getMessage(), 'error');
    }
}

/**
 * @param string $query
 *
 * @return string[]
 */
function _port2510_get_speaker_ids_for_query(string $query): array
{
    global $wpdb;

    return array_column(
        // @phpstan-ignore-next-line
        $wpdb->get_results($query, ARRAY_A) ?: [],
        'post_id'
    );
}
