<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-252
 */

use Scm\Tools\Logger;

function blem252_post_by_id($post_name, $post_type = 'post')
{
    $post_ids = get_posts(
        [
            'post_type' => $post_type,
            'posts_per_page' => 1,
            'post_name__in' => [$post_name],
            'fields' => 'ids',
        ]
    );

    return array_shift($post_ids);
}

/**
 * Map SC Awards post to brand.
 */
function blem252_blemmyae_deployment_ri_001(): void
{
    $scAwardsSlugs = [
        'the-business-of-cyber-how-security-defines-or-derails-success',
        'next-gen-tech-most-promising-areas-of-emerging-technology-and-innovation-in-cyber',
        'a-unified-front-effective-cooperative-vendor-user-relationships',
        'after-no-ordinary-year-congratulations-to-the-2021-sc-award-winners',
        'not-so-customary-customer-service-experts-offer-tips-on-vendor-best-practices',
        'meet-the-2021-sc-awards-judges',
        'security-executive-of-the-year-shawn-henry-crowdstrike',
        'legendary-entertainment-cso-helps-movie-magic-live-in-the-cloud',
        'best-security-company-cisco-systems',
        'rookie-security-company-of-the-year-beyond-identity',
        'security-innovator-of-the-year-jeremy-brown-trinity-cyber',
        'sounil-yu-creates-must-have-playbook-in-modern-information-security-programs',
        'sharethemicincyber-creators-drive-public-endorsement-of-fresh-faces',
        'cisco-systems-identity-services-engine-ise',
        'best-risk-policy-management-solution-firemon-risk-analyzer',
        'best-authentication-technology-okta-identity-cloud',
        'best-business-continuity-solution-cloudian-s3-object-lock',
        'adobe-security-coordination-center-overhauls-threat-intel-operations',
        'best-cloud-computing-security-solution-crowdstrike-falcon-platform',
        'best-computer-forensic-solution-tsfactory-llc-recordts',
        'best-data-leakage-prevention-solution-digital-guardian-data-protection-platform',
        'best-database-security-solution-secupi-data-protection-suite',
        'best-deception-technology-attivo-networks-threatdefend-platform',
        'best-email-security-solution-proofpoint-email-security',
        'best-scada-security-solution-scadafence-platform',
        'best-siem-solution-securonix-next-gen-siem',
        'best-threat-detection-technology-intezer-analyze',
        'best-threat-intelligence-technology-reversinglabs-titanium-platform',
        'best-vulnerability-management-solution-skybox-vulnerability-and-threat-management',
        'how-the-threat-landscape-drove-new-development-and-capabilities',
        'camille-francois-battles-disinformation-to-protect-elections-consumers',
        'brent-phillips-unites-diverse-groups-under-a-common-vision',
        'best-customer-service-sc-award-barracuda-networks',
        'best-identity-management-solution-okta-identity-cloud',
        'best-managed-security-service-crowdstrike-falcon-complete',
        'best-mobile-security-solution-lookout-security-platform',
        'best-cybersecurity-higher-education-program-champlain-college',
        'best-emerging-technology-bishop-fox-cast',
        'best-enterprise-security-solution-darktrace-cyber-ai-analyst',
        'best-it-security-related-training-program-knowbe4',
        'best-professional-certification-program-isc%c2%b2',
        'best-regulatory-compliance-solution-mimecast-cloud-archive-supervision',
        'best-sme-security-solution-cisco-umbrella',
        'best-web-application-solution-virsec-security-platform',
        'bill-butler-drives-workforce-expansion-through-the-full-learning-lifecycle',
        'julie-fitton-hammers-home-smart-tool-security-at-stanley-black-decker',
        'the-cyber-industrys-struggle-for-speed-and-superiority',
        'rich-baich-goes-global-with-defense-in-depth-strategy',
        'diversity-leaders-of-the-year-sharethemicincyber-founders-amplify-underrepresented-voices',
        '50-companies-named-trusted-providers-by-cloud-security-alliance',
        'knowbe4-ceo-stu-sjouwerman-talks-ipo-and-strengthening-that-human-firewall',
        'advancing-from-within-cybersecurity-leaders-drive-market-progress',
        'best-scada-security-solution-finalists',
        'best-threat-detection-technology-finalists',
        'best-threat-intelligence-technology-finalists',
        'best-sme-security-solution-finalists',
        'best-security-company-finalists',
        'rookie-security-company-of-the-year-finalists',
        'best-customer-service-finalists',
        'best-cybersecurity-higher-education-program-finalists',
        'best-siem-solution-finalists',
        'security-executive-of-the-year-finalists',
        'best-emerging-technology-finalists',
        'best-authentication-technology-finalists',
        'best-enterprise-security-solution-finalists',
        'security-innovator-of-the-year-finalists',
        'best-web-application-solution-finalists',
        'best-it-security-related-training-program-finalists',
        'best-professional-certification-program-finalists',
        'best-business-continuity-disaster-recovery-solution-finalists',
        'best-cloud-computing-security-solution-finalists',
        'best-computer-forensic-solution-finalists',
        'best-data-leakage-prevention-solution-finalists',
        'best-database-security-solution-finalists',
        'best-deception-technology-finalists',
        'best-regulatory-compliance-solution-finalists',
        'best-email-security-solution-finalists',
        'best-identity-management-solution-finalists',
        'best-managed-security-service-finalists',
        'best-mobile-security-solution-finalists',
        'best-nac-solution-finalists',
        'best-risk-policy-management-solution-finalists',
        'during-a-tumultuous-year-here-are-products-and-services-that-delivered',
        'best-vulnerability-management-solution-finalists',
        'vendors-as-partners-helping-the-user-community-help-themselves',
        'a-look-back-the-2020-professional-award-winners',
        'a-look-back-the-2020-trust-award-winners',
        'applications-still-open-to-serve-as-a-judge-for-the-2021-sc-awards',
        'sc-awards-to-recognize-cyber-solutions-companies-and-leaders-in-41-categories',
        'a-look-back-the-2020-excellence-award-winners',
        'entries-are-open-for-the-2021-sc-awards-and-were-expanding-the-scope-of-honors',
        'attivo-networks-threatdefend-platform-v5-0',
        'the-cyber-digital-divide-how-to-level-the-playing-field',
        'be-wary-of-medical-related-emails-proofpoint',
        'opentext-picks-up-carbonite-for-1-42b',
        'cisco-addresses-multiple-bugs-in-network-operating-systems',
        'sc-awards-finalists-2017',
        'sc-awards-finalists-2017-round-six',
        'sc-awards-finalists-2017-round-five',
        'sc-awards-finalists-2017-round-four',
        'sc-awards-finalists-2017-round-three',
        'sc-awards-finalists-2017-round-two',
        'sc-awards-finalists-2017-round-one',
        '2015-sc-awards-u-s-finalists',
        '2015-sc-awards-u-s-finalists-round-six',
        '2015-sc-awards-u-s-finalists-round-five',
        '2015-sc-awards-u-s-finalists-round-four',
        '2015-sc-awards-u-s-finalists-round-three',
        '2015-sc-awards-u-s-finalists-round-two',
        '2015-sc-awards-u-s-finalists-round-one',
        'siem-considerations-for-small-security-departments',
        'a-nac-for-eliminating-blind-spots-in-advanced-attacks',
        'file-sharing-and-mobile-productivity-how-to-limit-risk',
        'securely-adopting-2014s-top-mobility-trends',
        'surviving-in-an-era-of-continuous-compromise',
        'everyone-knows-the-80-20-principle-except-the-data-classification-experts',
        'its-when-not-if',
        'why-hackers-are-outsmarting-us',
        'closing-the-cyber-security-threat-intelligence-gap',
        'securing-data-in-the-cloud-mandate-for-encryption-and-key-management',
        'nightmare-on-main-street',
        '2014-sc-awards-u-s-finalists',
        '2014-sc-awards-u-s-finalists-round-six',
        '2014-sc-awards-u-s-finalists-round-five',
        '2014-sc-awards-u-s-finalists-round-four',
        '2014-sc-awards-u-s-finalists-round-three',
        '2014-sc-awards-u-s-finalists-round-two',
        '2014-sc-awards-u-s-finalists-round-one',
        'post-connect-network-visibility-a-fox-in-the-henhouse-for-network-security',
        'the-perils-of-blocking-byod',
        'achieving-compliance-in-todays-ever-changing-enterprise-network',
        'were-not-in-kansas-anymore',
        'byods-next-challenge-mobile-apps-collect-user-data',
        'cyber-attacks-the-easy-recipe-to-becoming-a-victim',
        'connect-the-dots-secure-everything-everywhere-and-that-includes-performance-and-productivity',
        'threat-centric-security-before-during-and-after-an-attack',
        'top-10-security-challenges-for-2013',
        'easy-math-the-mobile-security-payoff',
        'identity-crisis-from-the-data-center-into-the-cloud',
        'customer-service-in-a-network-security-world',
        'the-security-and-compliance-option-that-every-cio-should-know-about',
        'its-10-a-m-at-the-other-end-of-the-world-do-you-know-where-your-cloud-data-is',
        'cyber-attackers-riding-the-privileged-pathway',
        'security-as-the-infrastructure-platform-of-the-future',
        'centrify-for-best-customer-service',
        'hid-global-identity-assurance-for-best-identity-management-application',
        'axway-for-best-email-security-solution',
        'lieberman-software-for-best-identity-management-application',
        'lancope-for-best-computer-forensic-tool-best-enterprise-security-solution',
        'porticor-for-best-cloud-computing-security',
        'manageengine-for-best-siem-appliance',
        'teleperformance-group-for-best-security-team-cso-of-the-year-best-customer-service',
        'halon-security-for-best-email-security-solution',
        'magnet-forensics-for-best-computer-forensics-tool',
        'phoenix-ts-for-best-professional-training-program',
    ];
    $term = get_term_by('slug', 'sc-awards', 'topic');
    foreach ($scAwardsSlugs as $slug) {
        $isUpdated = false;
        $postId = blem252_post_by_id($slug, 'editorial');
        if ($postId) {
            wp_set_post_terms($postId, 'sc-awards', 'topic', true);
            $isUpdated = true;
            $field = get_field('topic', $postId) ?: [];
            $field[] = $term;
            update_field('topic', array_column($field, 'term_id'), $postId);
        }
        Logger::log("$postId is being updated with topic `SC Awards`", $isUpdated ? 'success' : 'info');
    }
    wp_cache_flush();
}

/**
 * Map Women in IT Security post to brand.
 */
function blem252_blemmyae_deployment_ri_002(): void
{
    $witSlugs = [
        '2021-women-in-it-security-call-for-nominations',
        'girl-security-brings-the-lived-security-experiences-of-diverse-girls-to-the-forefront',
        'a-lot-of-late-nights-zooms-compliance-chief-reflects-on-the-year-that-was-the-pandemic',
        'women-faces-among-all-of-those-white-men-digital-guardian-chiefs-explain-why-it-matters',
        'blackgirlshack-founder-im-trying-to-change-what-the-next-generation-of-cybersecurity-looks-like',
        'new-cyber-council-tackles-infosec-challenges-from-a-tech-perspective',
        'bidens-pick-as-white-house-cyber-czar-provides-critical-federal-leadership-and-diversity',
        'survey-says-women-in-cyber-make-31-percent-less-than-men',
        'new-business-requirements-post-covid-drive-boards-to-reassess-their-make-up',
        'female-cisos-lead-global-enterprises-through-unprecedented-change',
        'how-science-selects-a-password-policy',
        'shira-rubinoff-understanding-psychology-and-technology',
        'forget-getting-the-seat-at-the-table-i-built-my-own-table-and-want-you-to-sit-with-me',
        'second-career-cyber-attracting-new-talent',
        'the-cyber-immigrant-experience-then-and-now',
        'from-army-to-industry-a-ciso-comparison',
        'sylvia-acevedo-on-how-she-schooled-the-girls-scouts-in-tech',
        'how-google-harvard-tech-leaders-amplify-women-minority-voices',
        'parisa-tabriz-security-princess-killing-google-bugs',
        'maggie-louie-scoring-hits-with-cybersecurity-startup',
        'teresa-shea-building-trust-between-private-public-sectors',
        'priscilla-moriuchi-building-knowledge-at-intersection-of-tech-national-security',
        'eva-galperin-teaching-of-tech-mistakes-of-the-past',
        'caroline-wong-driving-success-by-every-metric',
        'sophie-pingor-breaking-into-walmart-for-the-sake-of-security',
        'katie-moussouris-building-around-core-values-labor-rights-principles',
        'keri-pearlson-creating-the-blueprint-to-build-a-cybersecurity-culture',
        'sivan-tehlia-prioritizing-mentorship-pandemic-or-no-pandemic',
        'candy-alexander-aligning-tech-policy-with-business-objectives',
        'deborah-golden-shattering-walls-of-unexpected-silos',
        'tarah-wheeler-bridging-policy-and-tech',
        'lesley-carhart-if-the-shirt-fits-or-even-if-it-doesnt-wear-it',
        'runa-sandvik-protected-journalists-driving-awareness',
        'vandana-verma-broadening-the-diversity-aperture',
        'lisa-markey-applying-expertise-across-verticals',
        'window-snyder-baking-security-into-the-app-dev-process',
        'lisa-sotto-pressing-compliance-as-the-high-priestess-of-privacy',
        'lets-start-a-new-conversation-about-women',
        'advanced-stress-protection-for-advanced-persistent-stress',
        'honorable-mentions-women-making-a-mark',
        'women-in-tech-a-strategy-for-change',
        'a-world-of-opportunity-international-women-in-cyber-spotlight-diversity-efforts-back-home',
        'as-cyber-investment-booms-so-do-calls-to-tackle-underrepresentation',
        'when-women-lead',
        'less-than-half-of-ethnic-minority-professionals-in-cybersecurity-feel-they-get-equal-treatment',
        'hiring-practices',
        'female-blackhats',
        'widening-the-field',
        'priming-the-pipeline-education',
        'where-opportunity-knocks',
    ];

    $term = get_term_by('slug', 'women-in-it-security', 'topic');
    foreach ($witSlugs as $slug) {
        $isUpdated = false;
        $postId = blem252_post_by_id($slug, 'editorial');
        if ($postId) {
            wp_set_post_terms($postId, 'women-in-it-security', 'topic', true);
            $isUpdated = true;
            $field = get_field('topic', $postId) ?: [];
            $field[] = $term;
            update_field('topic', array_column($field, 'term_id'), $postId);
        }
        Logger::log("$postId is being updated with topic `Women in IT Security`", $isUpdated ? 'success' : 'info');
    }
    wp_cache_flush();
}
