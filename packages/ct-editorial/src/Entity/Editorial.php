<?php

declare(strict_types=1);

namespace Cra\CtEditorial\Entity;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\CerberusApps;
use Cra\BlemmyaeApplications\Entity\Term;
use Cra\CtEditorial\EditorialCT;
use Cra\CtEditorial\Entity\Vendor\FieldsInterface as VendorFields;
use Cra\CtEditorial\Entity\Vendor\Innodata\Fields as InnodataFields;
use DateTime;
use Exception;
use InvalidArgumentException;
use WP_Post;
use WP_Query;
use WP_Term;

/**
 * Class Editorial which handles saving of editorials to CMS when syncing from external vendors.
 *
 * @todo Finish the rest of the fields.
 */
final class Editorial
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private ?WP_Post $post;

    private ?WP_Term $type = null;

    private ?WP_Term $brand = null;

    private ?WP_Term $industry = null;

    /**
     * @var WP_Term[]
     */
    private array $topics = [];

    private ?WP_Post $author;

    private string $status = 'draft';

    private string $title;

    private string $body;

    private ?DateTime $date;

    private VendorFields $vendorFields;

    /**
     * Class constructor.
     *
     * @param string $vendor
     */
    public function __construct(string $vendor)
    {
        $this->vendorFields = match ($vendor) {
            'innodata' => new InnodataFields(),
            default => throw new InvalidArgumentException('Unsupported vendor type!'),
        };
    }

    /**
     * Get post ID.
     *
     * @return int
     */
    public function id(): int
    {
        return $this->post->ID;
    }

    /**
     * Set post status.
     *
     * @param string|null $status
     *
     * @return string
     */
    public function status(?string $status = null): string
    {
        if (isset($status)) {
            $this->status = $status;
        }

        return $this->status;
    }

    /**
     * Get or set (if $slug provided) editorial type.
     *
     * @param string|null $slug
     *
     * @return WP_Term|null
     * @throws Exception
     */
    public function type(?string $slug = null): ?WP_Term
    {
        if (isset($slug)) {
            $this->type = $this->loadTerm($slug, 'editorial_type');
        }

        return $this->type;
    }

    /**
     * Get or set (if $slug provided) brand.
     *
     * @param string|null $slug
     *
     * @return WP_Term|null
     * @throws Exception
     */
    public function brand(?string $slug = null): ?WP_Term
    {
        if (isset($slug)) {
            $this->brand = $this->loadTerm($slug, 'brand');
        }

        return $this->brand;
    }

    /**
     * Get or set (if $slug provided) brand.
     *
     * @param string|null $slug
     *
     * @return WP_Term|null
     * @throws Exception
     */
    public function industry(?string $slug = null): ?WP_Term
    {
        if (isset($slug)) {
            $this->industry = $this->loadTerm($slug, 'industry');
        }

        return $this->industry;
    }

    /**
     * Get or set (if $topics provided) topics.
     *
     * Settings topics through this method overrides all existing topics.
     * To add topics use `addTopic` method.
     *
     * @param string[]|null $slugs
     *
     * @return WP_Term[]
     * @throws Exception
     * @see addTopic
     */
    public function topics(?array $slugs = null): array
    {
        if (isset($slugs)) {
            $this->topics = array_map(fn($slug) => $this->loadTerm($slug, 'topic'), $slugs);
        }

        return $this->topics;
    }

    /**
     * Add topic to
     *
     * @param string $slug
     *
     * @return WP_Term[]
     * @throws Exception
     * @see topics
     */
    public function addTopic(string $slug): array
    {
        $this->topics[] = $this->loadTerm($slug, 'topic');

        return $this->topics;
    }

    /**
     * Load term by slug for the taxonomy.
     *
     * @param string $slug
     * @param string $taxonomy
     *
     * @return WP_Term
     * @throws Exception
     */
    private function loadTerm(string $slug, string $taxonomy): WP_Term
    {
        $term = get_term_by('slug', $slug, $taxonomy);
        if (is_wp_error($term)) {
            throw new Exception($term->get_error_message(), (int)$term->get_error_code());
        }
        if (!$term) {
            throw new Exception("Term slug '$slug' does not exist on taxonomy '$taxonomy'.");
        }

        return $term;
    }

    /**
     * Get or set (if $slug provided) author.
     *
     * @param string|null $slug
     *
     * @return WP_Post|null
     * @throws Exception
     */
    public function author(?string $slug = null): ?WP_Post
    {
        if (isset($slug)) {
            $query = new WP_Query(['post_type' => 'people', 'name' => $slug]);
            if (!$query->have_posts()) {
                throw new Exception("Cannot find author with the provided slug $slug");
            }
            $this->author = $query->next_post();
        }

        return $this->author;
    }

    /**
     * Get or set (if $title provided) title.
     *
     * @param string|null $title
     *
     * @return string
     */
    public function title(?string $title = null): string
    {
        if (isset($title)) {
            $this->title = $title;
        }

        return $this->title ?: '';
    }

    /**
     * Get or set (if $body provided) body.
     *
     * @param string|null $body
     *
     * @return string
     */
    public function body(?string $body = null): string
    {
        if (isset($body)) {
            $this->body = $body;
        }

        return $this->body ?: '';
    }

    /**
     * Get or set (if $date provided) date.
     *
     * @param string|null $date
     *
     * @return DateTime|null
     * @throws Exception
     */
    public function date(?string $date = null): ?DateTime
    {
        if (isset($date)) {
            $this->date = new DateTime($date);
        }

        return $this->date;
    }

    /**
     * Get class for managing vendor specific fields.
     *
     * @return VendorFields
     */
    public function vendorFields(): VendorFields
    {
        return $this->vendorFields;
    }

    /**
     * Save Editorial to DB.
     *
     * @param int|string|null $searchBy null, "vendor", or post ID.
     * @param bool $force
     *
     * @return bool TRUE if saved and FALSE if skipped.
     * @throws Exception
     */
    public function upsert(
        mixed $searchBy = null,
        bool $force = false
    ): bool {
        $isFound = isset($searchBy) && $this->loadPost($searchBy);
        if (!$isFound || $force) {
            $this->upsertPost();
            $this->upsertAcfFields();

            return true;
        }

        return false;
    }

    /**
     * Load post if it exists.
     *
     * @param int|string $searchBy "vendor" or post ID.
     *
     * @return bool Returns FALSE if post doesn't exist.
     * @throws Exception
     */
    private function loadPost(mixed $searchBy): bool
    {
        $query = ['post_type' => 'editorial', 'post_status' => 'any'];
        if ($searchBy === 'vendor') {
            $uidFieldName = $this->vendorFields->uniqueIdFieldName();
            $uid = $this->vendorFields->uniqueId();
            if (empty($uid)) {
                throw new Exception('Missing unique ID!');
            }
            $query['meta_query'] = [
                [
                    'key' => "vendor_0_$uidFieldName",
                    'value' => $uid,
                ],
            ];
        } elseif (is_numeric($searchBy)) {
            $query['p'] = (int)$searchBy;
        } else {
            throw new InvalidArgumentException('Invalid $searchBy parameter provided!');
        }

        $wpQuery = new WP_Query($query);
        if ($wpQuery->have_posts()) {
            $this->post = $wpQuery->next_post();

            return true;
        }

        return false;
    }

    /**
     * Insert/update WP post.
     *
     * @throws Exception
     */
    private function upsertPost(): void
    {
        $post = isset($this->post) ?
            ['ID' => $this->post->ID] :
            ['post_status' => $this->status, 'post_type' => 'editorial'];
        $post += [
            'post_title' => $this->title,
            'post_content' => $this->body,
        ];
        if (isset($this->date)) {
            $post['post_date'] = $this->date->format(self::DATE_FORMAT);
        }

        $postId = wp_insert_post($post, true);

        if (is_wp_error($postId)) {
            throw new Exception($postId->get_error_message(), (int)$postId->get_error_code());
        }
        if (isset($this->post) && $this->post->ID !== $postId) {
            throw new Exception(
                "Duplicate post has been created! Old: {$this->post->ID}, new: $postId."
            );
        }

        $this->post = get_post($postId);
    }

    /**
     * Save ACF fields.
     */
    private function upsertAcfFields(): void
    {
        // Save editorial type.
        update_field(EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TYPE, $this->type->term_id, $this->post->ID);
        wp_set_post_terms($this->post->ID, [$this->type->term_id], 'editorial_type');

        // Save brand.
        update_field(EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_BRAND, [$this->brand->term_id], $this->post->ID);
        wp_set_post_terms($this->post->ID, [$this->brand->term_id], 'brand');

        // Save brand.
        update_field(
            EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_INDUSTRY,
            [$this->industry->term_id],
            $this->post->ID
        );
        wp_set_post_terms($this->post->ID, [$this->industry->term_id], 'industry');

        // Save topics.
        $topicIds = array_map(static fn(WP_Term $topic) => $topic->term_id, $this->topics);
        update_field(EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC, $topicIds, $this->post->ID);

        // Save author.
        update_field(EditorialCT::GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR, [$this->author->ID], $this->post->ID);

        // Save vendor field.
        update_field(
            EditorialCT::GROUP_BRIEF_ADVANCED__FIELD_VENDOR,
            [$this->vendorFields->repeaterArray()],
            $this->post->ID
        );

        // Find first sentence even with acronyms with a bit of sanitation.
        $temp = strip_tags($this->body, '<br>');
        $temp = str_replace('<br />', ' ', $temp);
        $temp = preg_replace('/\s+/', ' ', $temp);
        preg_match(
            '/^.*?[.!?](?=\s[A-Z]|\s?$)(?!.*\))/',
            $temp,
            $matches,
            PREG_OFFSET_CAPTURE
        );
        // Update deck.
        update_field(EditorialCT::GROUP_EDITORIAL_ADVANCED__FIELD_DECK, $matches[0][0] ?? '', $this->post->ID);

        // Applications fields.
        $app = Term::getAppTermBy('slug', BlemmyaeApplications::SCM);
        update_field(CerberusApps::APPLICATION_FIELD, $app->term_id, $this->post->ID);

        unset($temp, $matches);
    }
}
