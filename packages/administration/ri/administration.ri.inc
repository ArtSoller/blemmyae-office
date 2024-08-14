<?php

/**
 * @noinspection ALL
 */

declare(strict_types=1);

use Cra\GbCollection\Collection;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Entity\CustomImport;

/**
 * Add auto sync to all field groups.
 *
 * @return void
 */
function administration_ri_010(): void
{
    foreach (acf_get_field_groups() as $group) {
        if ($group['key'] === 'group_acfe_dynamic_form_side') {
            continue;
        }
        $group['acfe_autosync'] = ['php', 'json'];
        acf_update_field_group($group);
    }
}

/**
 * Import entity configs.
 */
function administration_ri_014(): void
{
    if (!class_exists(CustomImport::class)) {
        return;
    }

    CustomImport::importPosts();
    CustomImport::importTaxonomies();
    CustomImport::importFieldGroups();

    wp_cache_flush();

    // Import acf configs on every deploy.
    (new RI())->setStatus('administration_ri_015', false);
}

/**
 * Run above RI every time.
 *
 * @return void
 */
function administration_ri_015(): void
{
    (new RI())->setStatus('administration_ri_014', false);
}
