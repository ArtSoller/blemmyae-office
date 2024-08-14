<?php

/**
 * Main plugin file
 *
 * @package   Cra\CtEditorial
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\CtEditorial;

use BrightNucleus\Config\ConfigInterface;
use BrightNucleus\Config\ConfigTrait;
use BrightNucleus\Config\Exception\FailedToProcessConfigException;
use BrightNucleus\Settings\Settings;
use Cra\CtEditorial\Sync\Vendor\Innodata\Sync as InnodataSync;
use Exception;
use Scm\Tools\Logger;

/**
 * Main plugin class.
 *
 * @since 0.1.0
 *
 * @package Cra\CtEditorial
 * @author  Gary Jones
 */
class Plugin
{
    use ConfigTrait;

    public const INNODATA_SYNC_CRON = 'editorial_innodata_sync_cron';

    /**
     * Static instance of the plugin.
     *
     * @since 0.1.0
     *
     * @var self
     */
    protected static Plugin $instance;

    /**
     * Instantiate a Plugin object.
     *
     * Don't call the constructor directly, use the `Plugin::get_instance()`
     * static method instead.
     *
     * @param ConfigInterface $config Config to parametrize the object.
     *
     * @throws FailedToProcessConfigException If the Config could not be parsed correctly.
     *
     * @since 0.1.0
     *
     */
    public function __construct(ConfigInterface $config)
    {
        $this->processConfig($config);
        add_action('save_post_editorial', [$this, 'fillYearTaxonomy']);
    }

    /**
     * Callback for save_post_editorial action. Fills year taxonomy field
     * of editorial on creation.
     *
     * @param int $postId
     *
     * @return void
     */
    public function fillYearTaxonomy(int $postId): void
    {
        // Do nothing if year field is not empty
        if (get_field(EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_YEAR, $postId, false)) {
            return;
        }
        $publishYear = get_the_date('Y', $postId);
        $publishYearTermId = get_term_by('slug', $publishYear, 'years')?->term_id ?? null;

        update_field(EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_YEAR, $publishYearTermId, $postId);
    }

    /**
     * Launch the initialization process.
     *
     * @since 0.1.0
     */
    public function run(): void
    {
        add_action('plugins_loaded', [$this, 'loadTextDomain']);
        // phpcs:ignore @phpstan-ignore-next-line
        new Editorial();

        add_action(self::INNODATA_SYNC_CRON, [$this, 'innodataSync']);
        if (!wp_next_scheduled(self::INNODATA_SYNC_CRON)) {
            wp_schedule_event(time(), 'daily', self::INNODATA_SYNC_CRON);
        }

        new EditorialByQueryResolver(EditorialCT::POST_TYPE);
        new EditorialByQueryResolver(ScAwardNominee::POST_TYPE);

        new EditorialCT();
    }

    /**
     * Load the plugin text domain.
     *
     * @since 0.1.0
     */
    public function loadTextDomain(): void
    {
        /**
         * Plugin text domain.
         *
         * @var string $textDomain
         */
        $textDomain = $this->config->getKey('Plugin.textdomain');
        $languagesDir = 'languages';
        if ($this->config->hasKey('Plugin/languages_dir')) {
            /**
             * Directory path.
             *
             * @var string $languagesDir
             */
            $languagesDir = $this->config->getKey('Plugin.languages_dir');
        }

        load_plugin_textdomain($textDomain, false, $textDomain . '/' . $languagesDir);
    }

    /**
     * Sync Innodata XMLs into Editorial Briefs.
     */
    public function innodataSync(): void
    {
        if (
            !defined('INNODATA_SFTP_HOST') ||
            !defined('INNODATA_SFTP_USERNAME') ||
            !defined('INNODATA_SFTP_PASSWORD') ||
            !defined('INNODATA_SFTP_PORT') ||
            empty(INNODATA_SFTP_HOST) ||
            empty(INNODATA_SFTP_PORT) ||
            empty(INNODATA_SFTP_USERNAME)
        ) {
            Logger::log('Innodata SFTP credentials are missing. Skipping.', 'warning');

            return;
        }

        try {
            $sync = new InnodataSync();
            $config = [
                'host' => INNODATA_SFTP_HOST,
                'port' => INNODATA_SFTP_PORT,
                'username' => INNODATA_SFTP_USERNAME,
                'password' => INNODATA_SFTP_PASSWORD,
            ];
            $sync->setup($config);
            $sync->execute();
        } catch (Exception $exception) {
            Logger::log($exception->getMessage(), 'error');
        }
    }
}
