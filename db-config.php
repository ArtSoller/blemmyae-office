<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * charset (string)
 * This sets the default character set. Since WordPress 4.2, the suggested
 * setting is "utf8mb4". We strongly recommend not downgrading to utf8,
 * using latin1, or sticking to the default: utf8mb4.
 *
 * Default: utf8mb4
 */
$wpdb->charset = 'utf8mb4'; // @todo: use DB_CHARSET;

/**
 * collate (string)
 * This sets the default column collation. For best results, investigate which
 * collation is recommended for your specific character set.
 *
 * Default: utf8mb4_unicode_520_ci
 */
$wpdb->collate = 'utf8mb4_unicode_520_ci'; // @todo: use DB_COLLATE;

/**
 * save_queries (bool)
 * This is useful for debugging. Queries are saved in $wpdb->queries. It is not
 * a constant because you might want to use it momentarily.
 * Default: false
 */
$wpdb->save_queries = false;

/**
 * recheck_timeout (float)
 * The amount of time to wait before trying again to ping mysql server.
 *
 * Default: 0.1 (Seconds)
 */
$wpdb->recheck_timeout = 0.1;

/**
 * persistent (bool)
 * This determines whether to use mysql_connect or mysql_pconnect. The effects
 * of this setting may vary and should be carefully tested.
 * Default: false
 */
$wpdb->persistent = false;

/**
 * allow_bail (bool)
 * This determines whether to use mysql connect or mysql connect has failed and to bail loading the rest of WordPress
 * Default: false
 */
$wpdb->allow_bail = false;

/**
 * max_connections (int)
 * This is the number of mysql connections to keep open. Increase if you expect
 * to reuse a lot of connections to different servers. This is ignored if you
 * enable persistent connections.
 * Default: 10
 */
$wpdb->max_connections = 5000;

/**
 * check_tcp_responsiveness
 * Enables checking TCP responsiveness by fsockopen prior to mysql_connect or
 * mysql_pconnect. This was added because PHP's mysql functions do not provide
 * a variable timeout setting. Disabling it may improve average performance by
 * a very tiny margin but lose protection against connections failing slowly.
 * Default: true
 */
$wpdb->check_tcp_responsiveness = true;

/**
 * The cache group that is used to store TCP responsiveness.
 * Default: ludicrousdb
 */
$wpdb->cache_group = 'ludicrousdb';

/**
 * This is the most basic way to add a server to LudicrousDB using only the
 * required parameters: host, user, password, name.
 * This adds the DB defined in wp-config.php as a read/write server for
 * the 'global' dataset. (Every table is in 'global' by default.)
 */
$hostMaster = explode(':', DB_HOST)[0];
if ($hostMaster) {
    $wpdb->add_database([
        'host' => $hostMaster,
        'user' => DB_USER,
        'password' => DB_PASSWORD,
        'name' => DB_NAME,
        'write' => 1,
        'read' => 2,
    ]);
    $wpdb->add_database([
        'host' => $hostMaster,
        'user' => DB_USER,
        'password' => DB_PASSWORD,
        'name' => 'wp_msspalert',
        'write' => 2,
        'read' => 2,
    ]);
    $wpdb->add_database([
        'host' => $hostMaster,
        'user' => DB_USER,
        'password' => DB_PASSWORD,
        'name' => 'wp_afternines1',
        'write' => 2,
        'read' => 2,
    ]);
}

// Add slave instance if it is present.
// @todo: Add ping check - may slow down by timeout instances without read-only hosts.
$dbHostReadOnlyReplica = str_replace('.cluster-', '.cluster-ro-', DB_HOST);
$hostReadOnlyReplica = explode(':', $dbHostReadOnlyReplica)[0];
if ($hostReadOnlyReplica && $hostReadOnlyReplica !== $hostMaster) {
    $wpdb->add_database([
        'host' => $hostReadOnlyReplica,
        'user' => DB_USER,
        'password' => DB_PASSWORD,
        'name' => DB_NAME,
        'write' => 0,
        'read' => 1,
        'timeout'  => 0.2,
    ]);
}
