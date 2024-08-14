<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 * @link https://cra.myjetbrains.com/youtrack/issue/BLEM-614
 */

declare(strict_types=1);

use Cra\CtEditorial\EditorialCT;
use Cra\CtPeople\PeopleCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\WpCore;

/**
 * Class represents Security Weekly article.
 */
// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class SwArticle
{
    public int $id;

    /**
     * @var string[]
     */
    public array $categories;

    public string $title;

    public string $authorName;

    public string $authorFirstName;

    public string $authorLastName;

    public string $authorPicture;

    public DateTime $date;

    public string $body;

    public string $deck;

    public string $featuredImage;

    public string $link;

    /**
     * Populate from JSON.
     *
     * @param array $json
     *
     * @return $this
     * @throws Exception
     */
    public function populateFromJson(array $json): self
    {
        $this->id = $json['id'];
        $this->categories = array_values(array_unique($json['categories'] ?? []));
        $this->title = $json['title'] ?? '';
        $this->authorName = trim($json['author']['name'] ?? '');
        $this->splitAuthorName();
        $this->authorPicture = $json['author']['picture'] ?? '';
        $this->date = new DateTime($json['date'] ?? '1970-01-01');
        $this->body = $json['body'] ?? '';
        $this->deck = $json['excerpt'] ?? '';
        $this->featuredImage = $json['image'] ?? '';
        $this->link = $json['link'] ?? '';

        return $this;
    }

    /**
     * Split author name into first and last names.
     *
     * @return void
     * @throws Exception
     */
    private function splitAuthorName(): void
    {
        if ($this->authorName === 'Security Weekly Productions') {
            $this->authorFirstName = 'SC';
            $this->authorLastName = 'Staff';
        } else {
            $name = explode(' ', $this->authorName);
            if (count($name) !== 2) {
                throw new Exception(
                    "Cannot easily determine author's first and last name – $this->authorName"
                );
            }
            $this->authorFirstName = $name[0];
            $this->authorLastName = $name[1];
        }
    }
}

/**
 * Class for importing Security Weekly articles.
 */
// phpcs:ignore
class ImportSwArticles
{
    private const ARTICLES_DIR = 'wp-content/uploads/sw-articles';

    private const REDIRECTS_MAP_FILE = 'wp-content/uploads/sw_articles_redirects.map';

    private array $mediaUrlsToAttachmentIdMap = [];

    private array $redirectsMap = [];

    /**
     * Import SW articles.
     *
     * @return void
     */
    public function import(): void
    {
        $importCount = 0;
        $failCount = 0;
        foreach ($this->articles() as $article) {
            try {
                $post = $this->importArticle($article);
                $this->redirectsMap[$article->link] = get_post_permalink($post);
                $this->success(
                    "Imported SW article '$article->title' (SW ID: $article->id, WP ID: $post->ID)."
                );
                $importCount += 1;
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
                $failCount += 1;
            }
        }
        $this->success("Imported $importCount SW articles.");
        if ($failCount) {
            $this->error("Failed to import $failCount SW articles. See errors above for details.");
        }
    }

    /**
     * Remove legacy WP drafts of SW articles.
     *
     * @return void
     */
    public function removeLegacyDrafts()
    {
        foreach ($this->oldSwArticlesQuery('draft')->posts as $post) {
            if (!wp_delete_post($post->ID, true)) {
                $this->error("Error deleting post '$post->post_title' (ID: $post->ID)");
            } else {
                $this->success("Deleted draft post '$post->post_title' (ID: $post->ID)");
            }
        }
    }

    /**
     * Update newer SW posts to be the new editorial type.
     *
     * @return void
     */
    public function updateNewerSwArticles()
    {
        $term = get_term_by('name', 'SW Article', 'editorial_type');
        foreach ($this->oldSwArticlesQuery('publish')->posts as $post) {
            try {
                WpCore::setPostTerms(
                    'editorial_type',
                    $term->term_id,
                    $post->ID,
                    EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TYPE
                );
                $this->success("Updated post '$post->post_title' (ID: $post->ID)");
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }
    }

    /**
     * Get query to fetch old SW posts.
     *
     * @param string $postStatus
     *
     * @return WP_Query
     */
    private function oldSwArticlesQuery(string $postStatus): WP_Query
    {
        return new WP_Query([
            'nopaging' => true,
            'post_type' => 'editorial',
            'post_status' => $postStatus,
            'tax_query' => [
                'relation' => 'AND',
                [
                    'taxonomy' => 'editorial_type',
                    'field' => 'name',
                    'terms' => ['Perspective'],
                ],
                [
                    'taxonomy' => 'brand',
                    'field' => 'name',
                    'terms' => ['Security Weekly'],
                ],
            ],
        ]);
    }

    /**
     * Get all SW articles.
     *
     * @return Generator<SwArticle>
     */
    private function articles(): Generator
    {
        foreach (glob(ABSPATH . self::ARTICLES_DIR . '/*.json') as $filePath) {
            try {
                $articleJson = json_decode(file_get_contents($filePath), true);
                yield (new SwArticle())->populateFromJson($articleJson);
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
        }
    }

    /**
     * Import SW article.
     *
     * @param SwArticle $article
     *
     * @return WP_Post
     * @throws Exception
     */
    private function importArticle(SwArticle $article): WP_Post
    {
        return $this->upsertSwArticle($article, $this->findLegacyImportedArticle($article));
    }

    /**
     * Find WP post of SW article from the legacy import.
     *
     * @param SwArticle $article
     *
     * @return WP_Post|null
     */
    private function findLegacyImportedArticle(SwArticle $article): ?WP_Post
    {
        // It's just faster to hard-code map then trying to match due to little differences in titles.
        $map = [
            39920 => 214055,
            37980 => 173830,
            37936 => 173828,
            37925 => 173826,
            37695 => 173824,
            37366 => 173822,
            37299 => 173820,
            36767 => 173817,
            36560 => 173815,
            36428 => 173813,
            36196 => 173811,
            35937 => 173809,
            35852 => 173807,
            35849 => 173805,
            35697 => 173803,
            35695 => 173801,
            35670 => 173799,
            35467 => 173797,
            35461 => 173794,
            35445 => 173791,
            35424 => 173789,
            35339 => 173787,
            35231 => 173785,
            35180 => 173783,
            34925 => 173781,
            34750 => 173779,
            33269 => 173777,
            33097 => 173775,
            32953 => 173773,
            30593 => 173757,
        ];
        $postId = $map[$article->id] ?? null;
        return $postId ? get_post($postId) : null;
    }

    /**
     * Create WP post for SW article.
     *
     * @param SwArticle $article
     * @param WP_Post|null $legacyPost
     *
     * @return WP_Post
     * @throws Exception
     */
    private function upsertSwArticle(SwArticle $article, ?WP_Post $legacyPost): WP_Post
    {
        $newPost = WpCore::insertPost([
            'ID' => $legacyPost ? $legacyPost->ID : 0,
            'post_title' => $article->title,
            'post_date' => $article->date->format('c'),
            'post_content' => $this->replaceSwMedia($article->body),
            'post_status' => $legacyPost ? 'publish' : 'draft',
            'post_name' => sanitize_title($article->title),
            'post_type' => 'editorial',
        ]);

        update_field(EditorialCT::GROUP_EDITORIAL_ADVANCED__FIELD_DECK, $article->deck, $newPost->ID);
        update_field(
            EditorialCT::GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR,
            [$this->ensureSwAuthor($article)],
            $newPost->ID
        );
        WpCore::setPostTerms(
            'editorial_type',
            get_term_by('name', 'SW Article', 'editorial_type')->term_id,
            $newPost->ID,
            EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TYPE
        );
        WpCore::setPostTerms(
            'brand',
            [get_term_by('name', 'Security Weekly', 'brand')->term_id],
            $newPost->ID,
            EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_BRAND,
        );
        WpCore::setPostTerms(
            'region',
            get_term_by('name', 'North America', 'region')->term_id,
            $newPost->ID,
            EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_REGION,
        );
        WpCore::setPostTerms(
            'topic',
            $this->getTopicIds($article),
            $newPost->ID,
            EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC,
        );
        update_field(
            EditorialCT::GROUP_EDITORIAL_ADVANCED__FIELD_FEATURED_IMAGE,
            $this->attachmentIdFromUrl($article->featuredImage),
            $newPost->ID
        );
        update_field(EditorialCT::GROUP_EDITORIAL_ADVANCED__FIELD_SHOW_FEATURED_IMAGE, 0, $newPost->ID);

        if ($newPost->post_status !== 'publish') {
            wp_publish_post($newPost);
        }

        return $newPost;
    }

    /**
     * Replace media from SW domain with SCM attachments.
     *
     * @param string $body
     *
     * @return string
     */
    private function replaceSwMedia(string $body): string
    {
        // Examples:
        // src="http://secweekly2.wpengine.com/WindowsLiveWriter/MetasploitNewGUI_128FB/image_thumb_11.png"
        // href="http://secweekly2.wpengine.com/WindowsLiveWriter/MetasploitNewGUI_128FB/image_22.png"
        $regex = '/(?:(?:src)|(?:href))="(?P<url>https?:\/\/.*?\.wpengine\.com\/.*?)"/';
        preg_match_all($regex, $body, $matches);
        $urls = array_unique($matches[0]['url'] ?? []);
        foreach ($urls as $url) {
            try {
                $newUrl = wp_get_attachment_url($this->attachmentIdFromUrl($url));
                $body = str_replace($url, $newUrl, $body);
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
                // Some media might be missing. Just lead to SW landing page.
                $body = str_replace($url, '/security-weekly', $body);
            }
        }

        return $body;
    }

    /**
     * Get attachment ID for the provided URL.
     *
     * This function makes sure that duplicate attachments are not created during the import.
     *
     * @param string $url
     *
     * @return int
     * @throws Exception
     */
    private function attachmentIdFromUrl(string $url): int
    {
        if (!isset($this->mediaUrlsToAttachmentIdMap[$url])) {
            $this->mediaUrlsToAttachmentIdMap[$url] = WpCore::mediaHandleSideload($url);
        }
        return $this->mediaUrlsToAttachmentIdMap[$url];
    }

    /**
     * Ensure that SW author exists as person.
     *
     * @param SwArticle $article
     *
     * @return int
     * @throws Exception
     */
    private function ensureSwAuthor(SwArticle $article): int
    {
        $query = new WP_Query([
            'post_status' => 'publish',
            'post_type' => PeopleCT::POST_TYPE,
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'first_name',
                    'value' => $article->authorFirstName,
                ],
                [
                    'key' => 'last_name',
                    'value' => $article->authorLastName,
                ],
            ],
        ]);
        if ($query->have_posts()) {
            return $query->next_post()->ID;
        }

        $post = WpCore::insertPost([
            'post_status' => 'publish',
            'post_type' => PeopleCT::POST_TYPE,
        ]);
        update_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_FIRST_NAME, $article->authorFirstName, $post->ID);
        update_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_LAST_NAME, $article->authorLastName, $post->ID);

        return $post->ID;
    }

    /**
     * Get topic IDs from SW article.
     *
     * @param SwArticle $article
     *
     * @return int[]
     */
    private function getTopicIds(SwArticle $article): array
    {
        $topicsIds = array_map(
            static fn(WP_Term $term): int => $term->term_id,
            array_values(
                array_filter(
                    array_map(
                        function (string $category): false|WP_Error|WP_Term {
                            return get_term_by(
                                'name',
                                $this->mapSwCategoryToTopicName($category),
                                'topic'
                            );
                        },
                        $article->categories
                    ),
                    static fn($term) => $term instanceof WP_Term
                )
            )
        );
        return !empty($topicsIds)
            ? $topicsIds
            : [get_term_by('name', 'Uncategorized', 'topic')->term_id];
    }

    /**
     * Map SW category to topic name.
     *
     * @param string $category
     *
     * @return string
     */
    private function mapSwCategoryToTopicName(string $category): string
    {
        $trimmedCategory = trim($category);
        $lowerCategory = strtolower($trimmedCategory);
        $map = [
            'Identity and Access' => 'Identity and Access',
            'Data Protection' => 'Data Security',
            'Asset Management' => 'Asset Management',
            'Application Security' => 'Application Security',
            'Endpoint Security' => 'Endpoint Security',
            'Network Access Control' => 'Network Security',
            'Network Traffic Analysis' => 'Network Security',
            'Vulnerability Management' => 'Vulnerability Management',
            'Zero trust' => 'Zero trust',
            'Incident Response' => 'Incident Response',
            'Security Awareness' => 'Security Awareness',
            'Threat Intelligence' => 'Threat Intelligence',
            'Managed Security Services' => 'Managed Security',
            'Careers' => 'Careers',
            'Exploit Prevention' => 'Malware',
            'Insider Threat' => 'Insider Threat',
            'Physical Security' => 'Physical Security',
            'Compliance' => 'Compliance',
            'Remote Access' => 'Remote Access',
            'Active Directory' => 'Active Directory',
            'Encryption' => 'Encryption',
            'Backup and Recovery' => 'Backup and Recovery',
            'Secure File Sharing' => 'Secure File Sharing',
            '3rd Party Risk' => 'Third-Party Risk',
            'Container Security' => 'Container Security',
            'DevOps' => 'DevOps',
            'Cloud Security' => 'Cloud Security',
            'IoT' => 'IoT',
            'Hardware Security' => 'Hardware Security',
            'Mobile Security' => 'Mobile',
            'Application Isolation' => 'Application Isolation',
            'Firewall' => 'Firewall',
            'Wireless Security' => 'Wireless Security',
            'Intrusion Detection' => 'Intrusion Detection',
            'DDoS' => 'DDoS',
            'Penetration Testing' => 'Pen Testing',
            'Attack Surface Mgmt' => 'Attack Surface Mgmt',
            'Bug Bounties' => 'Bug Bounties',
            'Configuration Management' => 'Configuration Management',
            'Attack Simulation' => 'Attack Simulation',
            'Patch Management' => 'Patch Management',
            'Security Operations' => 'SOC',
            'SIEM' => 'SIEM',
            'Forensics' => 'Forensics',
            'Security Training' => 'Training',
            'Email Security' => 'Email Security',
            'OSINT' => 'OSINT',
            'Social Engineering' => 'Social Engineering',
            'Threat Hunting' => 'Threat Hunting',
            'Threat Modeling' => 'Threat Modeling',
            'Security Research' => 'Security Research',
            'Leadership' => 'Leadership',
            'Deception' => 'Deception',
            'Privacy' => 'Privacy',
            'ICS Security' => 'ICS Security',
        ];
        foreach ($map as $mappableCategory => $topic) {
            if (strtolower($mappableCategory) === $lowerCategory) {
                return $topic;
            }
        }
        return $trimmedCategory;
    }

    /**
     * Create nginx redirects map file.
     *
     * @return void
     */
    public function createNginxRedirectsMap(): void
    {
        $path = ABSPATH . self::REDIRECTS_MAP_FILE;
        $file = fopen($path, 'w');
        foreach ($this->redirectsMap as $old => $new) {
            if (!fwrite($file, "$old $new;\n")) {
                $this->error("An error has occurred writing to $path");
            }
        }
        fclose($file);
        $this->success("Nginx redirects map has been created: $path");
    }

    /**
     * Log error message.
     *
     * @param string $message
     *
     * @return void
     */
    private function error(string $message): void
    {
        Logger::log($message, 'warning');
    }

    /**
     * Log success message.
     *
     * @param string $message
     *
     * @return void
     */
    private function success(string $message): void
    {
        Logger::log($message, 'success');
    }
}

/**
 * Set long-running RI as completed, so it can be manually triggered after deployment.
 *
 * @return void
 */
function blem614_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('blem614_blemmyae_deployment_ri_002', true);
}

/**
 * Import SW articles.
 *
 * @return void
 */
function blem614_blemmyae_deployment_ri_002(): void
{
    $import = new ImportSwArticles();
    $import->removeLegacyDrafts();
    $import->import();
    $import->createNginxRedirectsMap();
    $import->updateNewerSwArticles();
}
