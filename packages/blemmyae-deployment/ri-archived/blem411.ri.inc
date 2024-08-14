<?php

use Cra\CtEditorial\EditorialCT;
use Cra\CtPeople\People;
use Cra\CtPeople\PeopleCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

// phpcs:ignore
const NamesToSkip = ['SC Magazine', 'SC Staff', 'Staff Report', 'Partner content', 'SC Magazine UK staff', 'SC UK Staff', 'Cybersecurity Collaborative', 'SC Staff Report', 'Cybersecurity Collaborative', 'Presented by Forcepoint Security Labs', 'Partner content', 'CISO at a large health care organization', 'Stephen Lawton: Data security in the 21st century'];
// phpcs:ignore
const JobTitleKeywords = ['CEO', 'CTO', 'CMO', 'CSO', 'director', 'manager', 'managing', 'architect', 'VP', 'president', 'chief', 'CIO', 'consultant', 'recruiter'];

//taken from blem265 ri, removed uri argument
function blem411_upsert_company(string $title)
{
    /** @phpstan-ignore-next-line */
    $page = get_page_by_title($title, OBJECT, 'company_profile');
    $pageId = null;

    if ($page instanceof \WP_POST) {
        $pageId = $page->ID;
    }

    if (is_array($page)) {
        return null;
    }

    if (is_null($page)) {
        $postData = [
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 0,
            'post_type' => 'company_profile',
        ];
        $pageId = wp_insert_post($postData);

        if ($pageId instanceof \WP_Error) {
            return null;
        }

        // Default type
        $termType = get_term_by('name', 'Vendor', 'company_profile_type');
        if ($termType) {
            update_field('type', $termType->term_id, $pageId);
        }
    }

    return $pageId;
}

function blem411_update_multiple_authors($multiplePeopleParsed): void
{
    $termsGuestAuthors = @array_map(
        static fn(array $row) => [
            'author_id' => $row[0],
            'author_name' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/blem326/many_authors.csv', ',')
    );
    $termsGuestAuthorNames = array_column($termsGuestAuthors, 'author_name', 'author_id');

    $postGuestAuthors = @array_map(
        static fn(array $row) => [
            'id' => $row[0],
            'author' => $termsGuestAuthorNames[$row[1]] ?? null,
        ],
        Utils::importCsv(__DIR__ . '/blem326/post_id_author_id.csv', ',')
    );
    $postGuestAuthorIds = array_column($postGuestAuthors, 'author', 'id');

    global $wpdb;
    $row = $wpdb->get_results("SELECT import_id, post_id, unique_key FROM wp_pmxi_posts");
    $countAll = $count = count($row);
    $processedMultipleGuestAuthorsPosts = [];

    foreach ($row as $item) {
        $count--;

        $currentAuthor = $postGuestAuthorIds[$item->unique_key] ?? null;

        if ($currentAuthor) {
            $currentSingleAuthorsIds = [];
            foreach ($multiplePeopleParsed[$currentAuthor] as $singleAuthorName) {
                $authorId = blem326_upsert_person($singleAuthorName);
                array_push($currentSingleAuthorsIds, $authorId);
            }
            Logger::log('Multiple authors detected for ' . $item->post_id . ' - ' . $currentAuthor, 'info');
            Logger::log('Adding individual author entries - ' . print_r($multiplePeopleParsed[$currentAuthor]), 'info');
            update_field(EditorialCT::GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR, $currentSingleAuthorsIds, $item->post_id);
            $processedMultipleGuestAuthorsPosts [] = blem326_upsert_person($currentAuthor);
            do_action('acf/save_post', $item->post_id);
            Logger::log("-------------", 'info');
        }
    }

    foreach ($processedMultipleGuestAuthorsPosts as $post) {
        Logger::log('Removing multiple people guest author - ' . $post, 'info');
        wp_delete_post($post);
    }

    wp_cache_flush();
}

function blem411_update_person_fields($parsedSinglePersons): void
{
    foreach ($parsedSinglePersons as $personName => $personData) {
        $personFields = $personData['personFields'];
        $personPostId = blem326_upsert_person($personData['personFields']['displayNameWithoutInfo'] ?? $personName);
        if ($personFields['jobTitle'] == 'DUPLICATE') {
            Logger::log(
                'Skipping update of ' . $personPostId . ' because a duplicate entry',
                'info'
            );

            continue;
        }
        Logger::log(
            'Updating person, postId - ' . $personPostId .
            ', firstName - ' . $personFields['firstName'] .
            ', lastName - ' . $personFields['lastName'] .
            ', jobTitle - ' . $personFields['jobTitle'] .
            ', comapny - ' . $personFields['company'],
            'info'
        );
        update_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_FIRST_NAME, $personFields['firstName'], $personPostId);
        update_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_MIDDLE_NAME, $personFields['middleName'], $personPostId);
        update_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_LAST_NAME, $personFields['lastName'], $personPostId);

        if (get_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES, $personPostId)) {
            Logger::log(
                'Skipping update of ' . $personPostId . ' because already has a company',
                'info'
            );

            continue;
        }

        if ($personFields['jobTitle'] || $personFields['company']) {
            $companyId = blem411_upsert_company($personFields['company']);
            $row = [
                /** @phpstan-ignore-next-line */
                People::FIELD__COMPANIES__JOB_TITLE => ucfirst($personFields['jobTitle'] ?? ''),
                /** @phpstan-ignore-next-line */
                People::FIELD__COMPANIES__COMPANY => [$companyId],
            ];

            add_row(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES, $row, $personPostId);
        }
    }
}

function blem411_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('blem411_blemmyae_deployment_ri_002', true);
}

function blem411_blemmyae_deployment_ri_002(): void
{
    $allPeople = @array_map(
        static fn(array $row) => [
            'id' => $row[0],
            'author' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/blem326/author_id_author_name.csv', ',')
    );
    $allPeopleIds = array_column($allPeople, 'author', 'id');

    $multiplePeople = @array_map(
        static fn(array $row) => [
            'id' => $row[0],
            'author' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/blem326/many_authors.csv', ',')
    );
    $multiplePeopleIds = array_column($multiplePeople, 'author', 'id');

    $singlePeople = [];
    $parsedSinglePersons = [];

    $foundNames = [];

    Logger::log('Restoring single people data from csv file', 'info');

    $singlePeopleFields = @array_map(
        static fn(array $row) => [
            'displayName' => $row[0],
            'firstName' => $row[1],
            'middleName' => $row[2],
            'lastName' => $row[3],
            'jobTitle' => $row[4],
            'company' => $row[5],
        ],
        Utils::importCsv(__DIR__ . '/blem411/parsedPersonsList.csv', ',')
    );

    foreach ($singlePeopleFields as $singlePerson) {
        $personNameParts = explode(',', $singlePerson['displayName'], 2);

        $parsedSinglePersons[$singlePerson['displayName']] = [
            'personFields' => [
                'displayName' => $singlePerson['displayName'],
                'displayNameWithoutInfo' => $personNameParts[0],
                'firstName' => $singlePerson['firstName'],
                'middleName' => $singlePerson['middleName'],
                'lastName' => $singlePerson['lastName'],
                'jobTitle' => $singlePerson['jobTitle'],
                'company' => $singlePerson['company'],
            ],
        ];

        // phpcs:ignore
        array_push($singlePeople, ['displayName' => $singlePerson['displayName'], 'author' => $personNameParts[0] ?? $singlePerson['displayName']]);
        array_push($foundNames, $singlePerson['displayName']);
    }

    Logger::log('Single people are restored from csv file', 'info');

    $singlePeopleIds = array_column($singlePeople, 'author', 'id');

    $multiplePeopleParsed = [];

    Logger::log('Parsing multiple people', 'info');

    foreach ($multiplePeopleIds as $personId => $personName) {
        Logger::log($personName, 'info');
        $foundNames = [];
        if (!array_key_exists($personName, $multiplePeopleParsed)) {
            $multiplePeopleParsed[$personName] = [];
        }
        $resultPersonName = $personName;
        foreach ($singlePeopleIds as $singlePersonId => $singlePersonName) {
            if (!$singlePersonName) {
                continue;
            }
            $singlePersonFound = strpos($resultPersonName, $singlePersonName);
            if ($singlePersonFound !== false && !in_array($singlePersonName, $foundNames)) {
                Logger::log('Single person found in multiple persons - ' . $singlePersonName, 'info');
                array_push($multiplePeopleParsed[$personName], $singlePersonName);
                array_push($foundNames, $singlePersonName);
                // phpcs:ignore
                $resultPersonName = substr_replace($resultPersonName, "<$singlePersonName>", $singlePersonFound, strlen($singlePersonName));
                Logger::log('Resulting multiple persons name - ' . $resultPersonName, 'info');
            }
        }
        Logger::log("-------------", 'info');
    }

    Logger::log('Running existing single authors fields update', 'info');
    blem411_update_person_fields($parsedSinglePersons);

    Logger::log('Running posts with multiple authors in single author update', 'info');
    blem411_update_multiple_authors($multiplePeopleParsed);

    wp_cache_flush();
}
