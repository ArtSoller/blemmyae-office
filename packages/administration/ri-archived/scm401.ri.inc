<?php

/**
 * The file that defines an example of RI.
 */

// Scm site code.
const SCM_ID = 2;

/**
 * Implementation of release instruction.
 */
function administration_ri_001(): void
{
    // Multi siting check example.
    if (get_current_blog_id() !== SCM_ID) {
        return;
    }
    echo esc_html('Hello World, SCM!');
}
