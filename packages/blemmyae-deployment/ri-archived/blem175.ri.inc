<?php

/**
 * @file Add Intrado meta fields to webcats.
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/BLEM-175
 */

declare(strict_types=1);

use Haymarket\Post_Meta\WebcastSettings;

/**
 * Update Intrado meta fields for the existing webcasts.
 *
 * @throws \WP_CLI\ExitException
 */
function blem175_blemmyae_deployment_ri_003(): void
{
    if (!class_exists(WebcastSettings::class)) {
        return;
    }

    global $wpdb;

    $metaFields = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT post_id, meta_value FROM $wpdb->postmeta
            WHERE meta_key = %s AND meta_value LIKE %s",
            WebcastSettings::META_REGISTER_URL,
            $wpdb->esc_like('https://onlinexperiences.com') . '%'
        ),
        OBJECT_K
    );
    foreach ($metaFields as $metaField) {
        try {
            WebcastSettings::saveIntradoMetaFields((int) $metaField->post_id, $metaField->meta_value);
            _blem175_wp_cli_success(sprintf('Webcast %d updated.', $metaField->post_id));
        } catch (Exception $exception) {
            _blem175_wp_cli_error(
                sprintf(
                    'Webcast %d: %s',
                    $metaField->post_id,
                    $exception->getMessage()
                )
            );
        }
    }

    _blem175_wp_cli_success(sprintf('Processed %d Intrado webcasts.', count($metaFields)));
}

/**
 * Output error message if run from WP CLI.
 *
 * @param string $message
 *
 * @throws \WP_CLI\ExitException
 */
function _blem175_wp_cli_error(string $message): void
{
    if (defined('WP_CLI') && WP_CLI) {
        WP_CLI::error($message);
    }
}

/**
 * Output success message if run from WP CLI.
 *
 * @param string $message
 */
function _blem175_wp_cli_success(string $message): void
{
    if (defined('WP_CLI') && WP_CLI) {
        WP_CLI::success($message);
    }
}
