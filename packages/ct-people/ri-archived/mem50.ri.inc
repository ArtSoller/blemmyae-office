<?php
/**
 * @licence PROPRIETARY
 *
 * @author CRA
 *
 * @link: https://cra.myjetbrains.com/youtrack/issue/MEM-50
 *
 * @phpcs disable NeutronStandard.Globals.DisallowGlobalFunctions
 */

declare(strict_types=1);

use Cra\CtCompanyProfile\CompanyProfile;
use Cra\CtPeople\People;
use Scm\Entity\Media;
use Scm\Tools\Utils;
use Scm\Tools\Logger;

const CSC_FIELD_KEYS = [
    'hubspotId',
    'hubspotPath',
    'createdAt',
    'hubspotName',
    'childTableId',
    'updatedAt',
    'fullName',
    'jobTitle',
    'company',
    'headshot',
];

/**
 * Import CSC people from HubSpot.
 *
 * @return void
 */
function mem50_ct_people_ri_002(): void
{
    $logger = new Logger();

    $cscTeamIds = array_map(static fn (array $row) => $row[7], Utils::importCsv(__DIR__ . '/csc_people_hubspot_migration/csc-our-team.csv'));
    $executiveCommitteeIds = array_map(static fn (array $row) => $row[7], Utils::importCsv(__DIR__ . '/csc_people_hubspot_migration/csc-executive-committee.csv'));
    $people = array_map(static fn (array $person) => array_combine(CSC_FIELD_KEYS, $person), Utils::importCsv(__DIR__ . '/csc_people_hubspot_migration/csc-people.csv'));

    $logger->log(sprintf('Importing %s persons..', count($people)),'info');
    $successes = 0;
    foreach ($people as $person) {
        $person['fullName'] = trim($person['fullName']);
        $person['company'] = trim($person['company']);
        $person['jobTitle'] = trim($person['jobTitle']);
        $logger->log(sprintf('Importing person %s...', $person['fullName']),'info');
        $names = explode(' ', $person['fullName']);
        $firstName = array_shift($names);
        $lastName = array_pop($names);
        $postId = findPersonPost($firstName, $lastName);
        if (!$postId) {
            $logger->log(sprintf('Person %s not found in CMS. Inserting new post...', $person['fullName']),'info');
            $postId = wp_insert_post([
                'post_title' => $person['fullName'],
                'post_type' => People::POST_TYPE
            ]);
            if (is_wp_error($postId)) {
                $logger->log('Error creating new post, skipping.','warning');
                continue;
            }
        }

        // Update  main and CSC specific fields.
        updatePersonFields($person, $postId);

        // Set CSC people type taxonomy.
        $personTerms = [];
        if (in_array($person['hubspotId'], $cscTeamIds, true)) {
            $personTerms[] = get_term_by('name', 'CSC Team', 'csc_people_type')->term_id;
        }
        if (in_array($person['hubspotId'], $executiveCommitteeIds, true)) {
            $personTerms[] = get_term_by('name', 'Executive Committee', 'csc_people_type')->term_id;
        }
        update_field(People::FIELD__CSC_PEOPLE_TYPE, $personTerms, $postId);
        wp_set_post_terms($postId, $personTerms, 'csc_people_type');

        wp_publish_post($postId);
        $successes++;
        $logger->log(sprintf('Successfully imported %s - %s', $person['fullName'], $postId),'success');
    }
    $logger->log(sprintf('Successfully Imported %s out of %s persons.', $successes, count($people)),'success');
}

/**
 * Update person's ACF fields.
 * Does not override main fields for existing person.
 *
 * @param array $person Person array from imported CSV.
 * @param int   $postId Person post ID
 *
 * @return void
 */
function updatePersonFields(array $person, int $postId): void
{
    update_field(People::FIELD__CSC_HUBSPOT_ID, $person['hubspotId'], $postId);

    $names = explode(' ', $person['fullName']);
    $firstName = array_shift($names);
    $lastName = array_pop($names);
    // Edge elements were extracted, so others can be used as a middle name.
    $middleName = (implode(' ', $names));

    // Update names if they are not set.
    if (!get_field(People::FIELD__FIRST_NAME, $postId)) {
        update_field(People::FIELD__FIRST_NAME, $firstName, $postId);
    }
    if (!get_field(People::FIELD__MIDDLE_NAME, $postId)) {
        update_field(People::FIELD__MIDDLE_NAME, $middleName, $postId);
    }
    if (!get_field(People::FIELD__LAST_NAME, $postId)) {
        update_field(People::FIELD__LAST_NAME, $lastName, $postId);
    }

    updatePersonCompanyAndTitle($person['company'], $person['jobTitle'], $postId);
    updateHeadshotFields(
        $person['headshot'],
        $postId,
        sprintf('Headshot for %s from HubSpot', $person['fullName'])
    );
}

/**
 * Update person's 'Companies' ACF field.
 * Insert company post if it's missing in CMS.
 *
 * @param string $companyName Company name
 * @param string $jobTitle    Person Job Title
 * @param int    $postId      Person post ID
 *
 * @return void
 */
function updatePersonCompanyAndTitle(string $companyName, string $jobTitle, int $postId): void
{
    $logger = new Logger();
    // Update main Companies field (add a new company).
    if (empty($companyName)) {
        return;
    }
    $logger->log('Updating company and job title for the person...','info');
    // Make sure that job title term exists, or insert it if it's not.
    Utils::insertTaxonomyTerm($jobTitle, People::TAXONOMY__JOB_TITLE);
    $jobTitleTermId = get_term_by(
        'name',
        $jobTitle,
        People::TAXONOMY__JOB_TITLE,
        OBJECT,
        'db'
        )->term_id ?? null;
    $companyPostId = findCompanyPost($companyName);
    if (!$companyPostId) {
        $logger->log(sprintf('Company %s not found in CMS. Inserting new post...', $companyName),'info');
        $companyPostId = wp_insert_post([
            'post_status' => 'publish',
            'post_title' => $companyName,
            'post_type' => CompanyProfile::POST_TYPE,
        ]);
        if (is_wp_error($postId)) {
            $logger->log('Error creating new post, skipping.','warning');
            return;
        }
        update_field(CompanyProfile::FIELD__COMPANY_NAME, $companyName, $companyPostId);
    }
    $companies = get_field(People::FIELD__COMPANIES, $postId, false) ?: [];
    $companies[] = [
        People::FIELD__COMPANIES__COMPANY => [$companyPostId],
        People::FIELD__COMPANIES__JOB_TITLE => $jobTitle,
        People::FIELD__COMPANIES__JOB_TITLE_TAXONOMY => $jobTitleTermId,
    ];
    update_field(People::FIELD__COMPANIES, array_unique($companies, SORT_REGULAR), $postId);

    // Update CSC specific fields
    update_field(People::FIELD__CSC_COMPANY, $companyPostId, $postId);
    update_field(People::FIELD__CSC_JOB_TITLE, $jobTitleTermId, $postId);
}

/**
 * Update Person's headshots (CSC specific and main if it is not set).
 *
 * @param string $url         Image URL
 * @param int    $postId      Person Post ID
 * @param string $description Optional description for the image
 *
 * @return void
 */
function updateHeadshotFields(string $url, int $postId, string $description = ''): void
{
    $logger = new Logger();
    if (empty($url)) {
        $logger->log('Missing headshot for the speaker.','warning');
        return;
    }
    // Cut headshot sizes in the one of the URL (https://*.png,1000,100)
    $url = preg_replace('/,\d+,\d+$/', '', $url);
    $cscHeadshot = get_field(People::FIELD__CSC_HEADSHOT, $postId);
    $id = $cscHeadshot ? (int) $cscHeadshot['ID'] : 0;
    $source = $id ? get_field(Media::FIELD__ORIGINAL_SOURCE, $id) : '';

    if (!$source || $source !== $url) {
        try {
            $logger->log('Inserting new image for headshot...','debug');
            $id = Utils::createFileAttachmentFromUrl($url, $description);
            update_field(Media::FIELD__ORIGINAL_SOURCE, $url, $id);
            update_field(People::FIELD__CSC_HEADSHOT, $id, $postId);
        } catch (Exception $ex) {
            $logger->log('Error creating image attachment. Skipping.','warning');
            return;
        }
    }

    if (!get_field(People::FIELD__HEADSHOT, $postId, false)) {
        // Non-vendor headshot is missing for the person. Setting.
        update_field(People::FIELD__HEADSHOT, $id, $postId);
    }
}

/**
 * Find Company post ID by its name.
 *
 * @param string $companyName
 *
 * @return int
 */
function findCompanyPost(string $companyName): int
{
    $query = new WP_Query([
        'post_status' => 'publish',
        'post_type' => CompanyProfile::POST_TYPE,
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key' => 'company_name',
                'value' => $companyName,
            ],
        ],
    ]);

    return $query->have_posts() ? $query->next_post()->ID : 0;
}

/**
 * Find Person post ID by his name.
 *
 * @param string $firstName
 * @param string $lastName
 *
 * @return int
 */
function findPersonPost(string $firstName, string $lastName): int
{
    $query = new WP_Query([
        'post_status' => 'publish',
        'post_type' => People::POST_TYPE,
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key' => 'first_name',
                'value' => $firstName,
            ],
            [
                'key' => 'last_name',
                'value' => $lastName,
            ]
        ],
    ]);

    return $query->have_posts() ? $query->next_post()->ID : 0;
}
