<?php

/**
 * @author  Konstantin Gusev <konstantin.gusev@cyberriskalliance.com>
 * @license proprietary
 */

namespace Cra\WebhookConsumer\Mapper;

use Cra\CtCompanyProfile\CompanyProfileCT;
use Cra\WebhookConsumer\WebhookMapping;
use Exception;
use Scm\Tools\WpCore;

/**
 * Trait to be used by mappers for company profile content type.
 */
trait CompanyProfilePostTrait
{
    use PostTrait;
    use MediaTrait;
    use SourceOfSyncTrait;

    public function wpEntityBundle(): string
    {
        return CompanyProfileCT::POST_TYPE;
    }

    /**
     * Upsert company post.
     *
     * @param WebhookMapping $webhookMapping
     * @param int $timestamp
     *
     * @return int
     * @throws Exception
     */
    protected function upsertCompanyPost(WebhookMapping $webhookMapping, int $timestamp): int
    {
        $postId = $webhookMapping->postId;
        if ($postId) {
            return $postId;
        }

        $postId = $this->findCompanyPostId();
        if ($postId) {
            return $postId;
        }

        return $this->upsertWebhookMappingAsPost(
            $webhookMapping,
            $timestamp,
            [
                'post_title' => $this->objectName(),
                'post_status' => WpCore::POST_STATUS_DRAFT,
            ]
        );
    }

    /**
     * Find 'company' content type post ID.
     *
     * @return int
     */
    protected function findCompanyPostId(): int
    {
        return WpCore::searchPostId([
            'post_status' => WpCore::POST_STATUS_PUBLISH,
            'post_type' => $this->wpEntityBundle(),
            'meta_query' => [
                [
                    'key' => 'company_name',
                    'value' => $this->objectName(),
                ],
            ],
        ]);
    }

    /**
     * Get object's trimmed name field.
     *
     * @return string
     */
    abstract protected function objectName(): string;

    /**
     * Disallow deletion of the associated WordPress entity.
     *
     * @return bool
     * @see AbstractWordpressWebhookMapper
     */
    protected function allowWpEntityDeletion(): bool
    {
        return false;
    }
}
