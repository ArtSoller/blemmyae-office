<?php

declare(strict_types=1);

use Cra\BlemmyaeDeployment\A9SMigration;
use Cra\CtLearning\LearningCT;
use Cra\CtPeople\PeopleCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

const PORT1959_AUTHORS = [
    'Jessica C. Davis',
    'Jim Masters',
    'Aiden Tariku',
    'Ty Trumbull',
    'Daniel Thomas',
    'Dan Kobialka',
    'D. Howard Kass',
    'Sponsored by Barracuda',
    'Sponsored by BlackBerry Cybersecurity',
    'Sponsored by CyberCecurity',
    'Sponsored by Cynomi',
    'Sponsored by OpenText',
    'Sponsored by Panorays',
    'Sponsored by PlexTrac',
    'Sharon Florentine',
    'Eric Larson',
    'Ted Roller',
    'Sponsored by IT By Design',
    'Sponsored by PORT-1959 Test'
];

function port1959_migration_ri_001(): void
{
    (new RI())->setStatus('port1959_migration_ri_002', true);
}

/**
 * Create new Blemmyae People entities with designation of Author.
 *
 * @return void
 * @throws Exception
 */
function port1959_migration_ri_002(): void
{
    foreach (PORT1959_AUTHORS as $author) {
        $person = get_posts([
            'post_type'  => 'people',
            'title' => $author,
        ]);

        if (isset($person[0]) && $person[0] instanceof \WP_POST) {
            Logger::log('Person ' . $author . ' already exist.', 'notice');
        }

        if (count($person) > 1) {
            throw new \Exception("There are multiply pages with the same title.");
        }

        $pageId = null;
        if (isset($person[0])) {
            continue;
        }

        $postData = [
            'post_title' => $author,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 0,
            'post_type' => 'people',
        ];
        $pageId = wp_insert_post($postData);

        if ($pageId instanceof \WP_Error) {
            throw new \Exception("Failed to insert new post: " . $pageId->get_error_message());
        }

        $personType = get_term_by('name', 'Author', 'people_type');
        $authorFullName = explode(' ', $author);
        [$name, $lastname] = [array_shift($authorFullName), implode(' ', $authorFullName)];
        $fields = [
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_FIRST_NAME => $name ?? '',
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_MIDDLE_NAME => $lastname ?? '',
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_LAST_NAME => $personType ?? '',
        ];

        foreach ($fields as $field => $value) {
            if (isset($value) && $value !== '' && isset($pageId)) {
                A9SMigration::updateAcfField($field, $value, $pageId);
            }
        }

        do_action('acf/save_post', $pageId);
        Logger::log('Person ' . $author . ' was created.', 'success');
    }
}

/**
 * Create new Blemmyae topic taxonomy.
 *
 * @return void
 */
function port1959_migration_ri_003(): void
{
    $topics = [
        'Security Staff Acquisition & Development' => 'Retention',
    ];

    foreach ($topics as $subcategory => $topic) {
        Utils::insertTaxonomyTerm($topic, LearningCT::TAXONOMY__TOPIC, $subcategory);
    }

    wp_cache_flush();
}
