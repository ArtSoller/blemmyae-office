<?php

/**
 * CustomImport class, custom config import/export util.
 *
 * @author Alexander Kucherov <avdkucherov@gmail.com>
 */

namespace Scm\Entity;

use Generator;
use Scm\Custom_Post_Type_UI\Util as CPT_UI;
use Scm\Advanced_Custom_Fields\Util as ACF;
use Scm\Tools\Logger;

class CustomImport
{
    // @phpstan-ignore-next-line
    private static string $configPath = ABSPATH . 'packages/administration/config/';

    /**
     * @return void
     */
    public static function importFieldGroups(): void
    {
        self::process(self::$configPath . 'field_group', 'field_group');
    }

    /**
     * @return void
     */
    public static function importBlockTypes(): void
    {
        self::process(self::$configPath . 'block_type', 'block_type');
    }

    /**
     * @return void
     */
    public static function importPosts(): void
    {
        self::process(self::$configPath . 'post_types', 'post');
    }

    /**
     * @return void
     */
    public static function importTaxonomies(): void
    {
        self::process(self::$configPath . 'taxonomy', 'taxonomy');
    }

    /**
     * @param string $path
     * @param string $type
     * @param bool $unregister
     * @return bool
     * @todo: Update description.
     *
     */
    public static function process(string $path, string $type, bool $unregister = false): bool
    {
        $jobType = !$unregister ? 'import' : 'removal';
        foreach (self::getAllConfigs($path) as $config) {
            $status = false;
            $data = [
                'remove' => $unregister,
            ];
            $logMessage = '`' . $type . ':' . $config['filename'] . '` - ' . $jobType . ' - ';
            Logger::log($logMessage . 'start.', 'info');

            switch ($type) {
                case 'post':
                case 'taxonomy':
                case 'block_type':
                case 'field_group':
                case 'options_page':
                    $data['acf_import'] = $config['json'];
                    $data['acf_type'] = $type;
                    $status = ACF::import($data);
                    break;
                default:
                    break;
            }
            Logger::log($status ? $jobType : 'skipped', $status ? 'success' : 'notice');
            Logger::log($logMessage . 'end.' . PHP_EOL, 'info');
        }

        return true;
    }

    /**
     * Get all configs.
     *
     * @param string $path
     *
     * @return Generator<array{'json': string, 'filename': string}>
     */
    private static function getAllConfigs(string $path): Generator
    {
        // We must load all configs into memory first because otherwise
        // auto sync will have a chance to mess the files first.
        $files = glob(untrailingslashit($path) . '/*.json') ?: [];
        $configs = [];
        foreach ($files as $file) {
            $configs[] = [
                'json' => file_get_contents($file, true) ?: '',
                'filename' => basename($file),
            ];
        }
        // After all configs are loaded we can start returning them.
        foreach ($configs as $config) {
            yield $config;
        }
    }
}
