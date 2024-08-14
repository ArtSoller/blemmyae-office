<?php

/** @noinspection PhpUnused */

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/PORT-2508
 * @author Konstantin Gusev <konstantin.gusev@cyberriskalliance.com>
 * @licence proprietary
 */

declare(strict_types=1);

use Cra\CtPeople\PeopleCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\WpCore;

function port2508_ct_people_ri_001(): void
{
    (new RI())->setStatus('port2508_ct_people_ri_002', true);
}

/**
 * Re-attach job title terms in people profiles.
 *
 * @return void
 */
function port2508_ct_people_ri_002(): void
{
    $success = fn(string $message) => Logger::log($message, 'success');
    $info = fn(string $message) => Logger::log($message, 'info');
    $error = fn(string $message) => Logger::log($message, 'error');

    $query = new WP_Query([
        'post_type' => PeopleCT::POST_TYPE,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ]);
    $totalCount = count($query->posts);
    $count = 1;
    $errorCount = 0;
    /** @var int $postId */
    foreach ($query->posts as $postId) {
        $info("[$count / $totalCount] Processing Person post with ID $postId.");

        $companies = get_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES, $postId, false) ?: [];
        $updatedCompanies = [];
        foreach ($companies as $company) {
            try {
                if (!empty($company[PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES__SUBFIELD_JOB_TITLE])) {
                    $company[PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES__SUBFIELD_JOB_TITLE_TAXONOMY] =
                        $company[PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES__SUBFIELD_JOB_TITLE_TAXONOMY] ?:
                            WpCore::getTermByName(
                                PeopleCT::TAXONOMY__JOB_TITLE,
                                $company[PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES__SUBFIELD_JOB_TITLE],
                                true
                            );
                }
            } catch (Exception $exception) {
                $error("ERROR --> {$exception->getMessage()}");
                $errorCount++;
            }
            $updatedCompanies[] = $company;
        }
        update_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES, $updatedCompanies, $postId);
        $success("Successfully updated!");

        $count++;
    }

    if ($errorCount > 0) {
        $error("There have been $errorCount errors.");
    }
    $success("Finished updating all published Person posts!");
}
