<?php

declare(strict_types=1);

use Scm\Tools\Logger;

const PORT1710_EWWW_IO_DISABLE_CREATION_OPTION = 'ewww_image_optimizer_disable_resizes';
const PORT1710_EWWW_IO_DISABLE_OPTIMIZATION_OPTION = 'ewww_image_optimizer_disable_resizes_opt';


/**
 * Disable optimization and creation of image resizes for EWWW IO.
 *
 * @return void
 */
function port1710_blemmyae_deployment_ri_001(): void
{
    $resizes = [
        'medium_large' => true,
        'large' => true,
        '1536x1536' => true,
        '2048x2048' => true
    ];

    update_option(PORT1710_EWWW_IO_DISABLE_CREATION_OPTION, $resizes);
    update_option(PORT1710_EWWW_IO_DISABLE_OPTIMIZATION_OPTION, $resizes);

    Logger::log("Successfully updated EWWW IO options.", 'success');

    wp_cache_flush();
}
