<?php
/**
 * Minimal page for ELB HTTP health check.
 *
 * Must return HTTP 200 (not 302) even when no host header is sent. The only
 * known page in Core that does so is wp-activate.php. Using this page instead
 * reduces server load and ensures that the theme exists.
 *
 * @link http://docs.aws.amazon.com/ElasticLoadBalancing/latest/DeveloperGuide/elb-healthchecks.html
 * @package headless
 */

# @todo: identify a more elegant way
# require('../../../wp-load.php');
echo 'OK';
