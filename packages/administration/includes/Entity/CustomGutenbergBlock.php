<?php

/**
 * CustomGutenbergBlock class, defines custom block type.
 *
 * @author Alexander Kucherov <avdkucherov@gmail.com>
 */

declare(strict_types=1);

namespace Scm\Entity;

use WP_Block_Editor_Context;
use WP_Block_Type_Registry;

/**
 * CustomGutenbergBlock class.
 */
class CustomGutenbergBlock
{
    /**
     * @var string Plugin dir path.
     *
     * @access protected
     */
    protected string $pluginDirPath;

    /**
     * CustomPostType constructor.
     *
     * @param string $pluginDirPath Plugin dir path.
     */
    public function __construct(string $pluginDirPath = '')
    {
        $this->pluginDirPath = $pluginDirPath ?: dirname(__DIR__);
        add_filter('cron_schedules', [$this, 'addCustomCronIntervals'], 10, 1);
        add_filter('allowed_block_types_all', [$this, 'allowedBlockTypes'], 10, 4);
    }

    /**
     * @param array $schedules
     *
     * @return array
     */
    public function addCustomCronIntervals(array $schedules): array
    {
        $schedules['ten_minutes'] = [
            'interval' => 10 * MINUTE_IN_SECONDS,
            'display' => 'Once Every 10 Minutes',
        ];
        $schedules['three_minutes'] = [
            'interval' => 3 * MINUTE_IN_SECONDS,
            'display' => 'Once Every 3 Minutes',
        ];

        return $schedules;
    }

    /**
     * @param array|bool $allowedBlocks
     * @param WP_Block_Editor_Context $editorContext
     *
     * @return array|bool
     */
    public function allowedBlockTypes(
        array|bool $allowedBlocks,
        WP_Block_Editor_Context $editorContext
    ): array|bool {
        return [
            'core/block',
            'core/file',
            'core/image',
            'core/audio',
            'core/button',
            'core/buttons',
            'core/column',
            'core/columns',
            'core/embed',
            'core/freeform',
            'core/group',
            'core/heading',
            'core/html',
            'core/list',
            'core/list-item',
            'core/media-text',
            'core/paragraph',
            'core/preformatted',
            'core/pullquote',
            'core/quote',
            'core/separator',
            'core/spacer',
            'core/table',
            'core/text-columns',
            'core/verse',
            'core/video',
            'cra/webcast-speaker',
        ];
    }

    /**
     * @return void
     */
    public function registerUnsupportedBlocksHooks(): void
    {
        add_action('init', [$this, 'registerUnsupported']);
        add_action('enqueue_block_editor_assets', [$this, 'editorScripts']);
    }

    /**
     * Registers unsupported.
     */
    public function registerUnsupported(): void
    {
        $unsupportedBlocks = [
            'fl-builder/layout', // SW
            'haymarket/column',
            'haymarket/column-layout',
            'haymarket/content',
            'haymarket/related-articles',
            'haymarket/ad',
            'haymarket/related-links',
            'haymarket/html-asset',
            'haymarket/group-test',
            'haymarket/review',
            'haymarket/webcast',
            'themify-builder/canvas', // SW
        ];

        $blockRegistry = WP_Block_Type_Registry::get_instance();
        foreach ($unsupportedBlocks as $unsupportedBlock) {
            if ($blockRegistry && !$blockRegistry->is_registered($unsupportedBlock)) {
                register_block_type(
                    $unsupportedBlock,
                    [
                        'attributes' =>
                            [
                                'type' => ['type' => 'string', 'default' => $unsupportedBlock],
                            ],
                    ]
                );
            }
        }
    }

    /*
     * Registers unsupported.
     * @todo: adjust naming, enqueued js handles a lot of other things
     */
    public function editorScripts(): void
    {
        wp_register_script(
            'gutenberg-unsupported-esnext',
            plugins_url('build/index.js', dirname(__DIR__)),
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-edit-post'],
        );
        wp_enqueue_script('gutenberg-unsupported-esnext');
    }
}
