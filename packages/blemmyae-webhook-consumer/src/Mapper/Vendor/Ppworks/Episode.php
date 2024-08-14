<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Ppworks;

use Cra\BlemmyaePpworks\Ppworks;
use Cra\BlemmyaePpworks\PpworksEpisodeCT;
use Cra\CtLearning\LearningCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\WebhookConsumer\WebhookMapping;
use Exception;
use Scm\Tools\WpCore;

/**
 * Webhook mapper for ppworks_episode post type.
 */
class Episode extends AbstractPodcast
{
    public const TYPE = 'episode';

    /**
     * @inheritDoc
     */
    public function wpEntityBundle(): string
    {
        return PpworksEpisodeCT::POST_TYPE;
    }

    /**
     * @inheritDoc
     */
    protected function isVaultPodcast(): bool
    {
        return $this->objectIsVault();
    }

    /**
     * @inheritDoc
     */
    protected function podcastNumber(): int
    {
        return $this->objectNumber();
    }

    /**
     * @inheritDoc
     */
    protected function updateAcfFields(ConsumerObjectId $id): void
    {
        parent::updateAcfFields($id);

        $this->updateAcfField(
            PpworksEpisodeCT::GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_VAULT,
            $this->objectIsVault()
        );
        $this->updateAcfField(
            PpworksEpisodeCT::GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_NUMBER,
            $this->objectNumber()
        );

        $this->updateAcfField(
            PpworksEpisodeCT::GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_HOSTS,
            array_map(fn(WebhookMapping $mapping) => $mapping->postId, $this->objectHosts())
        );

        $this->updateAcfField(
            PpworksEpisodeCT::GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_GUESTS,
            array_map(fn(WebhookMapping $mapping) => $mapping->postId, $this->objectGuests())
        );

        WpCore::setPostTerms(
            Ppworks::TAXONOMY__SHOW,
            $this->objectShow()->postId,
            $this->postId,
            PpworksEpisodeCT::GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_SHOW,
        );

        $segments = $this->objectSegments();
        if ($segments !== null) {
            $newSegmentIds = array_map(fn(WebhookMapping $mapping) => $mapping->postId, $segments);
            $oldSegmentIds = $this->getAcfField(
                PpworksEpisodeCT::GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_SEGMENTS,
                false
            );
            $orphanedSegmentIds = array_diff($oldSegmentIds, $newSegmentIds);
            $this->updateAcfField(
                PpworksEpisodeCT::GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_SEGMENTS,
                $newSegmentIds
            );
            // This is a workaround to the issue PORT-2119 which I cannot reproduce. There is a scenario which leaves
            // an orphaned (not in PPWorks) segment in Blemmyae. The segment can be safely removed.
            // Any leftover mappings will be automatically cleaned up.
            foreach ($orphanedSegmentIds as $invalidSegmentId) {
                wp_delete_post($invalidSegmentId, true);
            }
        }

        $topics = array_map(fn(WebhookMapping $mapping) => $mapping->postId, $this->objectCategories());
        WpCore::setPostTerms(
            LearningCT::TAXONOMY__TOPIC,
            $topics,
            $this->postId,
            PpworksEpisodeCT::GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_TOPICS,
        );

        $tags = array_map(fn(WebhookMapping $mapping) => $mapping->postId, $this->objectTags());
        WpCore::setPostTerms(
            PpworksEpisodeCT::TAXONOMY__TAG,
            $tags,
            $this->postId,
            PpworksEpisodeCT::GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_TAGS,
        );
    }

    /**
     * @inheritDoc
     */
    protected function upsertShortUrlRedirect(ConsumerObjectId $id, int $postId): void
    {
        // Figure out source and target URIs.
        $slug = $this->shortUrlSlug();
        $sourceUri = "/podcast-episode/$slug";
        $targetUri = WpCore::getPostRelativePermalink($postId);

        // Upsert redirect.
        $this->upsertRedirect($sourceUri, $targetUri);
    }

    /**
     * Get Short URL slug.
     *
     * @return string
     * @throws Exception
     */
    private function shortUrlSlug(): string
    {
        $showShortName = get_field(
            Ppworks::TAXONOMY__SHOW__SHORT_NAME,
            Ppworks::TAXONOMY__SHOW . '_' . $this->objectShow()->postId
        ) ?? '';
        $showShortName = strtolower(trim($showShortName));
        if (empty($showShortName)) {
            throw new Exception('PPWORKS SYNC - upsertShortUrlRedirect - Empty show short name!');
        }
        $episodeNumber = $this->objectNumber();

        return $this->objectIsVault() ?
            "vault-$showShortName-$episodeNumber" : "$showShortName-$episodeNumber";
    }

    /**
     * Get object vault flag.
     *
     * @return bool
     */
    private function objectIsVault(): bool
    {
        return !empty($this->object->vault);
    }

    /**
     * Get object number.
     *
     * @return int
     */
    private function objectNumber(): int
    {
        return (int)($this->object->number ?? 0);
    }
}
