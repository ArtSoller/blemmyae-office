<?php

require(__DIR__ . '/../../wp-config.php');
$_SERVER['HTTP_HOST'] = WP_SITEURL ?? 'https://blemmyae.ddev.site';

const WP_USE_THEMES = false;
global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
require_once(__DIR__ . '/../../wp-load.php');

$namespace = $argv[3];
$className = "Cra\\$namespace\\$argv[1]Generator";
new $className($argv[1], str_replace('-', '_', $argv[2]), $namespace);
