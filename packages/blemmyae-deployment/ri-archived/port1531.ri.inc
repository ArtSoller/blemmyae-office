<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

use Scm\Tools\Logger;

const PORT1531_BAD_REDIRECTS_OPTION = 'port1531_bad_redirects';

/**
 * Set list of bad redirects.
 *
 * @return void
 * @noinspection PhpUnused
 */
function port1531_blemmyae_deployment_ri_001(): void
{
    update_option(
        PORT1531_BAD_REDIRECTS_OPTION,
        [
            "/podcast-episode/secure-digital-life-66",
            "/podcast-segment/aleksei-tiurin-acunetix",
            "/podcast-segment/application-news",
            "/podcast-segment/appsec-development-partnership-application-security-weekly-8",
            "/podcast-segment/article-discussion",
            "/podcast-segment/bill-ottman-minds-secure-digital-life-90",
            "/podcast-segment/black-hat-dual-interview-pt-1-enterprise-security-weekly-105",
            "/podcast-segment/bryson-bort-scythe-pauls-security-weekly-589",
            "/podcast-segment/bugs-breaches-and-more",
            // phpcs:ignore
            "/podcast-segment/chris-clymer-jack-nichelson-and-jason-middaugh-infosec-world-enterprise-security-weekly-35",
            "/podcast-segment/detecting-the-empires-death-star-attack-pauls-security-weekly-517",
            "/podcast-segment/enterprise-news",
            "/podcast-segment/gabriel-gumbs-stealthbits",
            "/podcast-segment/galen-hunt-microsoft-azure",
            "/podcast-segment/harry-sverdlove-edgewise",
            "/podcast-segment/john-kinsella-layered-insight",
            "/podcast-segment/john-moran-dflabs",
            "/podcast-segment/joshua-abraham-praetorian",
            "/podcast-segment/leadership-articles",
            "/podcast-segment/leadership-communication",
            "/podcast-segment/leadership-communication-and-innovation",
            "/podcast-segment/may-23-2017-hack-naked-news-126",
            "/podcast-segment/news-enterprise-security-weekly-35",
            "/podcast-segment/news-startup-security-weekly-31",
            "/podcast-segment/nmap-scripts-with-lua-and-nse",
            "/podcast-segment/reverse-analyzing-attacks-for-detection-justin-henderson-pauls-security-weekly-519",
            "/podcast-segment/roger-courville-eventbuilder-startup-security-weekly-36",
            "/podcast-segment/ron-gula-gula-tech-adventures",
            "/podcast-segment/security-money",
            "/podcast-segment/security-news",
            "/podcast-segment/startup-security-weekly-13-news",
            "/podcast-segment/startup-security-weekly-14-brian-beyer-ceo-of-red-canary",
            "/podcast-segment/startup-security-weekly-14-news",
            "/podcast-segment/sven-morgenroth-netsparker",
            "/podcast-segment/tracking-security-innovation",
            "/podcast-segment/understanding-ssl-and-https",
            "/podcast-segment/zane-lackey-signal-sciences",
        ]
    );
}

/**
 * Remove bad redirects created during webhook sync.
 *
 * @return void
 * @noinspection PhpUnused
 */
function port1531_blemmyae_deployment_ri_002(): void
{
    /** @var string[] $badRedirects */
    $badRedirects = get_option(PORT1531_BAD_REDIRECTS_OPTION, []);
    $count = 0;
    /** @phpstan-ignore-next-line */
    foreach (Red_Item::get_all() as $redirect) {
        $from = untrailingslashit($redirect->get_url());
        if (in_array($from, $badRedirects, true)) {
            $redirect->delete();
            $to = $redirect->get_action_data();
            Logger::log("Deleted redirect from $from to $to.", 'success');
            $count++;
        }
    }
    Logger::log("Successfully deleted $count bad redirect(s).", 'success');
}
