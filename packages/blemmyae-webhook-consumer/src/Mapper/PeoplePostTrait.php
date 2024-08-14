<?php

/**
 * @author  Konstantin Gusev <konstantin.gusev@cyberriskalliance.com>
 * @license proprietary
 */

namespace Cra\WebhookConsumer\Mapper;

use Cra\CtCompanyProfile\CompanyProfileCT;
use Cra\CtPeople\PeopleCT;
use Cra\WebhookConsumer\WebhookMapping;
use Exception;
use Scm\Tools\WpCore;
use WP_Query;

/**
 * Trait to be used by mappers for people profile content type.
 */
trait PeoplePostTrait
{
    use PostTrait;
    use SourceOfSyncTrait;

    /**
     * @inheritDoc
     */
    public function wpEntityBundle(): string
    {
        return PeopleCT::POST_TYPE;
    }

    /**
     * Upsert company.
     *
     * @return int Returns post ID.
     * @throws Exception
     */
    protected function upsertCompanyPost(): int
    {
        $query = new WP_Query([
            'post_status' => WpCore::POST_STATUS_PUBLISH,
            'post_type' => CompanyProfileCT::POST_TYPE,
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'company_name',
                    'value' => $this->objectCompany(),
                ],
            ],
        ]);

        $post = $query->have_posts() ? $query->next_post() : null;
        if ($post) {
            /** @noinspection PhpCastIsUnnecessaryInspection */
            return (int)$post->ID;
        }

        $companyPostId = $this->upsertAnyPost([
            'post_status' => WpCore::POST_STATUS_PUBLISH,
            'post_type' => CompanyProfileCT::POST_TYPE,
            'post_title' => $this->objectCompany(),
        ]);
        $name = get_field(CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_COMPANY_NAME, $companyPostId);
        if (!$name) {
            update_field(
                CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_COMPANY_NAME,
                $this->objectCompany(),
                $companyPostId
            );
        }

        return $companyPostId;
    }

    /**
     * Get job title term.
     *
     * @param string $jobTitle
     *
     * @return int|string
     * @throws Exception
     */
    protected function getJobTitleTerm(string $jobTitle): int|string
    {
        return $jobTitle ?
            WpCore::getTermByName(PeopleCT::TAXONOMY__JOB_TITLE, $jobTitle, true)->term_id :
            '';
    }

    /**
     * Update companies field for the person.
     *
     * @param int $companyPostId
     *
     * @throws Exception
     */
    protected function updateCompaniesField(int $companyPostId): void
    {
        $currentCompanies = $this->getAcfField(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES, false) ?: [];
        $companies = [
            [
                'company' => [$companyPostId],
                'job_title' => $this->objectJobTitle(),
                'job_title_taxonomy' => $this->getJobTitleTerm($this->objectJobTitle()),
            ],
        ];
        foreach ($currentCompanies as $companyField) {
            $id = (int)$companyField[PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES__SUBFIELD_COMPANY][0];
            if ($id === $companyPostId) {
                continue;
            }
            $jobTitle = $companyField[PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES__SUBFIELD_JOB_TITLE];
            $companies[] = [
                'company' => [$id],
                'job_title' => $jobTitle,
                'job_title_taxonomy' => $this->getJobTitleTerm($jobTitle),
            ];
        }
        $this->updateAcfField(
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES,
            $companies,
        );
    }

    /**
     * Updates|Inserts 'People' post type post.
     *
     * @param WebhookMapping $webhookMapping
     * @param int $timestamp
     *
     * @return int  Post ID.
     *
     * @throws Exception
     */
    protected function upsertPersonPost(WebhookMapping $webhookMapping, int $timestamp): int
    {
        if (!$webhookMapping->postId) {
            $webhookMapping->postId = $this->findPersonPostId();
        }

        // Always update title (slug will update automatically).
        // Status will be draft only if it's a new post.
        return $this->upsertWebhookMappingAsPost($webhookMapping, $timestamp, [
            'post_title' => $this->getName(),
            'post_status' => WpCore::POST_STATUS_DRAFT,
        ]);
    }

    /**
     * Find 'person' content type post ID.
     *
     * @return int
     */
    protected function findPersonPostId(): int
    {
        if ($postId = $this->alternativeFindPersonPostId()) {
            return $postId;
        }

        return WpCore::searchPostId([
            'post_status' => WpCore::POST_STATUS_PUBLISH,
            'post_type' => $this->wpEntityBundle(),
            'meta_query' => [
                [
                    'key' => 'first_name',
                    'value' => $this->objectFirstName(),
                ],
                [
                    'key' => 'middle_name',
                    'value' => $this->objectMiddleName(),
                ],
                [
                    'key' => 'last_name',
                    'value' => $this->objectLastName(),
                ],
            ],
        ]);
    }

    /**
     * Alternative way of finding 'person' content type post ID.
     *
     * @return int
     */
    protected function alternativeFindPersonPostId(): int
    {
        return 0;
    }

    /**
     * Get full name.
     *
     * @return string
     */
    protected function getName(): string
    {
        return $this->objectMiddleName() ?
            sprintf(
                "%s %s %s",
                $this->objectFirstName(),
                $this->objectMiddleName(),
                $this->objectLastName()
            ) :
            sprintf("%s %s", $this->objectFirstName(), $this->objectLastName());
    }

    /**
     * Get object's trimmed first name field.
     *
     * @return string
     */
    abstract protected function objectFirstName(): string;

    /**
     * Get object's trimmed middle name field.
     *
     * @return string
     */
    abstract protected function objectMiddleName(): string;

    /**
     * Get object's trimmed last name field.
     *
     * @return string
     */
    abstract protected function objectLastName(): string;

    /**
     * Get object's trimmed job title field.
     *
     * @return string
     */
    abstract protected function objectJobTitle(): string;

    /**
     * Get object's trimmed company field.
     *
     * @return string
     */
    abstract protected function objectCompany(): string;
}
