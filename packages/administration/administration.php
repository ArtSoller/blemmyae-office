<?php // phpcs:ignore PSR1.Files.SideEffects.FoundWithSymbols

/** @noinspection PhpDefineCanBeReplacedWithConstInspection */

/**
 * Plugin Name: Administration
 * Plugin URI: https://github.com/cra-repo/administration
 * Description: Re-usable core functionality for admin and non-admin pages
 * Author: avdkucherov@gmail.com (Alexander Kucherov)
 * Version: 3.90.0
 * Author URI: https://gitlab.com/Zinkutal
 * RI: true
 */

declare(strict_types=1);

use Scm\Acf_Extended\Graphql as ACFE_Graphql;
use Scm\Acf_Extended\Options as ACFE_Options;
use Scm\Acf_Extended\ConfigStorage;
use Scm\Advanced_Ads\CustomPlacement as AA_CustomPlacement;
use Scm\Advanced_Ads\Options as AA_Options;
use Scm\Advanced_Custom_Fields\ElasticPressOptions as ACF_EP_Options;
use Scm\Advanced_Custom_Fields\Options as ACF_Options;
use Scm\Archived_Post_Status\Options as APS_Options;
use Scm\Custom_Post_Type_UI\Options as CPTUI_Options;
use Scm\Entity\CustomGutenbergBlock as CGB;
use Scm\Entity\Flag;
use Scm\Entity\Media;
use Scm\Entity\PublishingOptions;
use Scm\Feed\Setup as FeedSetup;
use Scm\Roles\CustomCapabilities as Roles_CustomCapabilities;
use Scm\WP_GraphQL\GraphqlCdn;
use Scm\WP_GraphQL\Options as WPGQL_Options;

define('ADMINISTRATION_PATH', plugin_dir_path(__FILE__));
define('ADMINISTRATION_URL', plugins_url('', __FILE__));
define('ADMINISTRATION_PLUGIN_FILE', __FILE__);
define('ADMINISTRATION_PLUGIN_VERSION', '1.9.0');

// Initialize custom role capabilities.
new Roles_CustomCapabilities();

// ACF options.
new ACF_EP_Options();
new ACF_Options();

// ACF Extended options.
new ACFE_Options();
new ACFE_Graphql();

// ACF configs
new ConfigStorage();

// Advanced Ads:
// Options.
new AA_Options();
// Initialize custom ad placement.
new AA_CustomPlacement();

// APS options.
new APS_Options();

// APS options.
new CPTUI_Options();

// WPGraphQL options.
new WPGQL_Options();

// GraphQL CDN cache purging.
new GraphqlCdn();

// Custom Gutenberg Block.
(new CGB())->registerUnsupportedBlocksHooks();

new Media();

(new PublishingOptions())->init();

(new FeedSetup())->initHooks();

new Flag();
