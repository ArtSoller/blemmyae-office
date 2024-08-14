<?php

/**
 * Main plugin file
 *
 * @package   Cra\BlemmyaeDeployment
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeDeployment;

use BrightNucleus\Config\ConfigInterface;
use BrightNucleus\Config\ConfigTrait;
use BrightNucleus\Config\Exception\FailedToProcessConfigException;
use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\CerberusApps;
use Cra\BlemmyaeApplications\Entity\Permalink;
use Scm\Tools\Logger;

/**
 * Main plugin class.
 *
 * @since 0.1.0
 *
 * @package Cra\BlemmyaeDeployment
 * @author  Gary Jones
 */
class Plugin
{
    use ConfigTrait;

    private const GENERATE_SITEMAP_CRON = 'blemmyae_generate_sitemap';

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
    }

    /**
     * Launch the initialization process.
     *
     * @since 0.1.0
     */
    public function run(): void
    {
        add_action('plugins_loaded', [$this, 'loadTextDomain']);

        add_action(self::GENERATE_SITEMAP_CRON, [$this, 'generateSitemaps']);
        if (!wp_next_scheduled(self::GENERATE_SITEMAP_CRON)) {
            wp_schedule_event(time(), 'daily', self::GENERATE_SITEMAP_CRON);
        }
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
     * Generate all sitemaps.
     *
     * @return void
     */
    public function generateSitemaps(): void
    {
        $applications = [
            BlemmyaeApplications::SCM => [
                'publication' => true,
            ],
            BlemmyaeApplications::CISO => [],
            BlemmyaeApplications::CSC => [],
            BlemmyaeApplications::CE2E => [],
            BlemmyaeApplications::MSSP => [],
            BlemmyaeApplications::CRC => [],
        ];
        $sitemapDirectory = 'sitemap';

        // Fix memory leak.
        wp_suspend_cache_addition(true);

        foreach ($applications as $application => $params) {
            Logger::log(sprintf('[sitemap:%s] Generate - Start.', $application), 'info');

            $className = 'Scm\Entity\Sitemap\\' . ucfirst($application);
            if (!class_exists($className)) {
                return;
            }

            $sitemap = new $className(
                Permalink::buildFrontendPathByApp($application),
                CerberusApps::getUploadSubDir($application, $sitemapDirectory),
                isset($params['publication']) ? get_option('blogname', 'SC Magazine') : '',
                $application
            );

            $sitemap->generate();
            Logger::log(sprintf('[sitemap:%s] Generate - End.', $application), 'info');
        }

        wp_suspend_cache_addition(false);
    }
}
