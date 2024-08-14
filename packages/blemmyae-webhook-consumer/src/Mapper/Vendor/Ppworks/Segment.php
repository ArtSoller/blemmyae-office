<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Ppworks;

use Cra\BlemmyaePpworks\PpworksEpisodeCT;
use Cra\BlemmyaePpworks\PpworksSegmentCT;
use Cra\CtLearning\LearningCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\WebhookConsumer\WebhookMapping;
use Cra\WebhookConsumer\WebhookMessageHandler;
use Exception;
use Scm\Tools\WpCore;

/**
 * Webhook mapper for ppworks_segment post type.
 */
class Segment extends AbstractPodcast
{
    public const TYPE = 'segment';

    /**
     * @inheritDoc
     */
    public function wpEntityBundle(): string
    {
        return PpworksSegmentCT::POST_TYPE;
    }

    /**
     * @inheritDoc
     */
    protected function updateAcfFields(ConsumerObjectId $id): void
    {
        parent::updateAcfFields($id);

        $this->updateSegmentType();

        $this->updateAcfField(
            PpworksSegmentCT::GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_POSITION,
            $this->objectPosition()
        );

        $this->updateAcfField(
            PpworksSegmentCT::GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_ANNOUNCEMENTS,
            array_map(fn(WebhookMapping $mapping) => $mapping->postId, $this->objectAnnouncements())
        );

        $this->updateAcfField(
            PpworksSegmentCT::GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_ARTICLES,
            array_map(fn(WebhookMapping $mapping) => $mapping->postId, $this->objectArticles())
        );

        $this->updateAcfField(
            PpworksSegmentCT::GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_SPONSOR_PROGRAMS,
            array_map(fn(WebhookMapping $mapping) => $mapping->postId, $this->objectSponsorPrograms())
        );

        $this->updateAcfField(
            PpworksSegmentCT::GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_HOSTS,
            array_map(fn(WebhookMapping $mapping) => $mapping->postId, $this->objectHosts())
        );

        $this->updateAcfField(
            PpworksSegmentCT::GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_GUESTS,
            array_map(fn(WebhookMapping $mapping) => $mapping->postId, $this->objectGuests())
        );

        $this->updateAcfField(
            PpworksSegmentCT::GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_EPISODE,
            $this->objectEpisode()->postId
        );

        WpCore::setPostTerms(
            PpworksSegmentCT::TAXONOMY__SHOW,
            $this->objectShow()->postId,
            $this->postId,
            PpworksSegmentCT::GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_SHOW,
        );

        $topics = array_map(fn(WebhookMapping $mapping) => $mapping->postId, $this->objectCategories());
        WpCore::setPostTerms(
            LearningCT::TAXONOMY__TOPIC,
            $topics,
            $this->postId,
            PpworksSegmentCT::GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_TOPICS,
        );

        $tags = array_map(fn(WebhookMapping $mapping) => $mapping->postId, $this->objectTags());
        WpCore::setPostTerms(
            PpworksSegmentCT::TAXONOMY__TAG,
            $tags,
            $this->postId,
            PpworksSegmentCT::GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_TAGS,
        );

        $this->updateAcfField(
            PpworksSegmentCT::GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_GALLERY_IMAGES,
            $this->upsertImages(
                $this->objectGalleryImages(),
                "Gallery image for {$this->objectTitle()} segment from PPWorks"
            )
        );
    }

    /**
     * Update segment type.
     *
     * @throws Exception
     */
    private function updateSegmentType(): void
    {
        $term = WpCore::getTermByName(PpworksSegmentCT::TAXONOMY__SEGMENT, $this->objectType(), true);
        WpCore::setPostTerms(
            PpworksSegmentCT::TAXONOMY__SEGMENT,
            $term->term_id,
            $this->postId,
            PpworksSegmentCT::GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_TYPE,
        );
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function isVaultPodcast(): bool
    {
        $episodeId = $this->objectEpisode()->postId;

        return (bool)get_field(PpworksEpisodeCT::GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_VAULT, $episodeId);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function podcastNumber(): int
    {
        $episodeId = $this->objectEpisode()->postId;

        return (int)get_field(PpworksEpisodeCT::GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_NUMBER, $episodeId);
    }

    /**
     * @inheritDoc
     */
    protected function upsertShortUrlRedirect(ConsumerObjectId $id, int $postId): void
    {
        $this->upsertRedirect(
            "/podcast-segment/{$id->getId()}",
            WpCore::getPostRelativePermalink($postId)
        );
    }

    /**
     * Get object type.
     *
     * @return string
     */
    private function objectType(): string
    {
        return (string)($this->object->type ?? '');
    }

    /**
     * Get object position.
     *
     * @return int
     */
    private function objectPosition(): int
    {
        return (int)($this->object->position ?? 0);
    }

    /**
     * Get object gallery images.
     *
     * @return array|string[]
     */
    private function objectGalleryImages(): array
    {
        return $this->object->gallery_images ?? [];
    }

    /**
     * Get object announcements.
     *
     * @return array|WebhookMapping[]
     * @throws Exception
     */
    private function objectAnnouncements(): array
    {
        return WebhookMapping::findMultiple(
            $this->object->announcements ?? [],
            Announcement::TYPE,
            WebhookMessageHandler::VENDOR__PPWORKS
        );
    }

    /**
     * Get object articles.
     *
     * @return array|WebhookMapping[]
     * @throws Exception
     */
    private function objectArticles(): array
    {
        return WebhookMapping::findMultiple(
            $this->object->articles ?? [],
            Article::TYPE,
            WebhookMessageHandler::VENDOR__PPWORKS
        );
    }

    /**
     * Get object sponsor programs.
     *
     * @return array|WebhookMapping[]
     * @throws Exception
     */
    private function objectSponsorPrograms(): array
    {
        return WebhookMapping::findMultiple(
            $this->object->sponsors ?? [],
            SponsorProgram::TYPE,
            WebhookMessageHandler::VENDOR__PPWORKS
        );
    }

    /**
     * Get object episode.
     *
     * @return WebhookMapping
     * @throws Exception
     */
    private function objectEpisode(): WebhookMapping
    {
        $mapping = WebhookMapping::findById(
            new ConsumerObjectId(
                WebhookMessageHandler::VENDOR__PPWORKS,
                Episode::TYPE,
                (string)($this->object->episode ?? '')
            )
        );
        if (!$mapping) {
            throw new Exception('Missing ppworks episode webhook mapping.');
        }

        return $mapping;
    }
}
