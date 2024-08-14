<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-392
 */

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Utils as AdministationUtils;

/**
 * Import entity configs.
 */
function blem392_blemmyae_deployment_ri_001(): void
{
    $configOverrideS3 = [
        'delivery-domain' => 'files.scmagazine.com',
        'region' => 'us-east-2',
    ];
    switch (AdministationUtils::environment()) {
        case 'live':
        case 'prod':
        case 'production':
            $configOverrideS3['bucket'] = 'cra-portal-backend-blemmyae-files-prod';
            $configOverrideS3['object-prefix'] = 'wp-content/uploads/';
            break;
        case 'preprod':
        case 'qa1':
        case 'qa2':
        case 'test':
            $configOverrideS3['bucket'] = 'cra-portal-backend-blemmyae-files-test';
            $configOverrideS3['object-prefix'] = 'wp-content/uploads/test/';
            break;
        default:
            // Dev, local, etc.
            $configOverrideS3['bucket'] = 'cra-portal-backend-blemmyae-files-dev';
            $configOverrideS3['object-prefix'] = 'wp-content/uploads/dev/';
            break;
    }

    $optionKeyS3 = class_exists(
        'Amazon_S3_And_CloudFront'
    ) ? Amazon_S3_And_CloudFront::SETTINGS_KEY : 'tantan_wordpress_s3';
    update_option($optionKeyS3, array_replace(get_option($optionKeyS3, []), $configOverrideS3));

    wp_cache_flush();
    // @todo: Move out ri init to administration.
    (new RI())->setStatus('blem392_blemmyae_deployment_ri_002', false);
}

/**
 * Re-sets `blem392_blemmyae_deployment_ri_001`.
 */
function blem392_blemmyae_deployment_ri_002(): void
{
    // @todo: Move out ri init to administration.
    (new RI())->setStatus('blem392_blemmyae_deployment_ri_001', false);
}
