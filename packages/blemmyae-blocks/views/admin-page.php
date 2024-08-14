<?php

/**
 * Admin page view
 *
 * @package   Cra\BlemmyaeBlocks
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

?>
<div class="wrap">
    <form method="post" action="options.php">

        <?php
        settings_fields('blemmyaeblocks'); /*  Name of settings field in table. */ ?>

        <h1><?php
            echo esc_html(get_admin_page_title()); ?></h1>

        <?php
        do_settings_sections('blemmyaeblocks');
        ?>

        <div class="bottom-buttons">
            <?php
            submit_button(__('Save Changes', 'blemmyae-blocks'), 'primary', 'submit', false);
            ?>
        </div>
    </form>
</div>
