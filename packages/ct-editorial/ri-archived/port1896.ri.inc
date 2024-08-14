<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/PORT-1896
 */

declare(strict_types=1);

use Scm\Tools\Logger;

/**
 * Update structure for editorial post link.
 *
 * @return void
 */
function port1896_ct_editorial_ri_001(): void
{
    // Remove BLEMMYAE_TOPIC_PLACEHOLDER from path.
    $editorialStructureOption = 'editorial_structure';
    $pathStructure = get_option($editorialStructureOption);

    // Update option.
    $pathStructure = str_replace('BLEMMYAE_TOPIC_PLACEHOLDER/', '', $pathStructure);
    update_option($editorialStructureOption, $pathStructure);

    Logger::log("Editorial post structure was updated. New structure: " . $pathStructure, "info");
}