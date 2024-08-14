<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Ppworks;

use Cra\BlemmyaePpworks\Ppworks;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Mapper\AbstractWordpressWebhookMapper;
use Cra\WebhookConsumer\Mapper\MediaTrait;
use Cra\WebhookConsumer\Mapper\RedirectTrait;
use Cra\WebhookConsumer\Mapper\TaxonomyTrait;
use Exception;

/**
 * Webhook mapper for ppworks_show taxonomy term type.
 */
class Show extends AbstractWordpressWebhookMapper
{
    use TaxonomyTrait;
    use RedirectTrait;
    use MediaTrait;

    public const TYPE = 'show';

    /**
     * @inheritDoc
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId
    {
        $this->termId = $this->upsertWebhookMappingAsTaxonomyTerm(
            $this->webhookMappingWithFallback($id),
            $this->objectTitle()
        );

        $this->updateTermField(
            Ppworks::TAXONOMY__SHOW__SHORT_NAME,
            $this->objectShortname()
        );
        $this->updateTermField(
            Ppworks::TAXONOMY__SHOW__DESCRIPTION,
            $this->objectDescription()
        );
        $this->updateImageField(
            Ppworks::TAXONOMY__SHOW . '_' . $this->termId,
            $this->objectDefaultImage(),
            Ppworks::TAXONOMY__SHOW__DEFAULT_IMAGE,
            "Default image for {$this->objectTitle()} show term from PPWorks"
        );
        $this->updateTermField(
            Ppworks::TAXONOMY__SHOW__AUDIO_ONLY,
            (int)$this->objectAudioOnly()
        );
        $this->updateTermField(
            Ppworks::TAXONOMY__SHOW__CUSTOM_SERIES,
            (int)$this->objectIsSeries()
        );
        $this->updateTermField(
            Ppworks::TAXONOMY__SHOW__SUBSCRIBE_VIDEO,
            [
                Ppworks::TAXONOMY__SHOW__SUBSCRIBE_VIDEO_APPLE => $this->objectSubscribeVideoApple(),
                Ppworks::TAXONOMY__SHOW__SUBSCRIBE_VIDEO_GOOGLE => $this->objectSubscribeVideoGoogle(),
                Ppworks::TAXONOMY__SHOW__SUBSCRIBE_VIDEO_RSS => $this->objectSubscribeVideoRss(),
            ]
        );
        $this->updateTermField(
            Ppworks::TAXONOMY__SHOW__SUBSCRIBE_AUDIO,
            [
                Ppworks::TAXONOMY__SHOW__SUBSCRIBE_AUDIO_APPLE => $this->objectSubscribeAudioApple(),
                Ppworks::TAXONOMY__SHOW__SUBSCRIBE_AUDIO_SPOTIFY => $this->objectSubscribeAudioSpotify(),
                Ppworks::TAXONOMY__SHOW__SUBSCRIBE_AUDIO_AMAZON => $this->objectSubscribeAudioAmazon(),
                Ppworks::TAXONOMY__SHOW__SUBSCRIBE_AUDIO_PANDORA => $this->objectSubscribeAudioPandora(),
                Ppworks::TAXONOMY__SHOW__SUBSCRIBE_AUDIO_GOOGLE => $this->objectSubscribeAudioGoogle(),
                Ppworks::TAXONOMY__SHOW__SUBSCRIBE_AUDIO_RSS => $this->objectSubscribeAudioRss(),
            ]
        );

        $this->upsertShortUrlRedirect();

        return $this->getThisTermEntityId();
    }

    /**
     * Update current term's ACF field.
     *
     * @param string $selector
     * @param mixed $value
     *
     * @return void
     */
    private function updateTermField(string $selector, mixed $value): void
    {
        update_field($selector, $value, Ppworks::TAXONOMY__SHOW . '_' . $this->termId);
    }

    /**
     * Upsert short URL redirect.
     *
     * @return void
     * @throws Exception
     */
    private function upsertShortUrlRedirect(): void
    {
        if (empty($this->objectShortname())) {
            throw new Exception('PPWORKS SYNC - upsertShortUrlRedirect - Empty show short name!');
        }

        $sourceUri = "/podcast-show/{$this->objectShortname()}";
        $termLink = get_term_link($this->termId, Ppworks::TAXONOMY__SHOW);
        if (is_wp_error($termLink)) {
            throw new Exception($termLink->get_error_message());
        }
        $targetUri = untrailingslashit(wp_parse_url($termLink, PHP_URL_PATH));

        $this->upsertRedirect($sourceUri, $targetUri);
    }

    /**
     * Get object title.
     *
     * @return string
     */
    private function objectTitle(): string
    {
        return (string)($this->object->title ?? '');
    }

    /**
     * Get object shortname.
     *
     * @return string
     */
    private function objectShortname(): string
    {
        return (string)($this->object->shortname ?? '');
    }

    /**
     * Get object description.
     *
     * @return string
     */
    private function objectDescription(): string
    {
        return (string)($this->object->description ?? '');
    }

    /**
     * Get object default image.
     *
     * @return string
     */
    private function objectDefaultImage(): string
    {
        return (string)($this->object->default_image ?? '');
    }

    /**
     * Get object audio_only.
     *
     * @return bool
     */
    private function objectAudioOnly(): bool
    {
        return !empty($this->object->audio_only);
    }

    /**
     * Get object is_series.
     *
     * @return bool
     */
    private function objectIsSeries(): bool
    {
        return !empty($this->object->is_series);
    }

    /**
     * Get object subscribe_video_apple.
     *
     * @return string
     */
    private function objectSubscribeVideoApple(): string
    {
        return (string)($this->object->subscribe_video_apple ?? '');
    }

    /**
     * Get object subscribe_video_google.
     *
     * @return string
     */
    private function objectSubscribeVideoGoogle(): string
    {
        return (string)($this->object->subscribe_video_google ?? '');
    }

    /**
     * Get object subscribe_video_rss.
     *
     * @return string
     */
    private function objectSubscribeVideoRss(): string
    {
        return (string)($this->object->subscribe_video_rss ?? '');
    }

    /**
     * Get object subscribe_audio_apple.
     *
     * @return string
     */
    private function objectSubscribeAudioApple(): string
    {
        return (string)($this->object->subscribe_audio_apple ?? '');
    }

    /**
     * Get object subscribe_audio_spotify.
     *
     * @return string
     */
    private function objectSubscribeAudioSpotify(): string
    {
        return (string)($this->object->subscribe_audio_spotify ?? '');
    }

    /**
     * Get object subscribe_audio_amazon.
     *
     * @return string
     */
    private function objectSubscribeAudioAmazon(): string
    {
        return (string)($this->object->subscribe_audio_amazon ?? '');
    }

    /**
     * Get object subscribe_audio_pandora.
     *
     * @return string
     */
    private function objectSubscribeAudioPandora(): string
    {
        return (string)($this->object->subscribe_audio_pandora ?? '');
    }

    /**
     * Get object subscribe_audio_google.
     *
     * @return string
     */
    private function objectSubscribeAudioGoogle(): string
    {
        return (string)($this->object->subscribe_audio_google ?? '');
    }

    /**
     * Get object subscribe_audio_rss.
     *
     * @return string
     */
    private function objectSubscribeAudioRss(): string
    {
        return (string)($this->object->subscribe_audio_rss ?? '');
    }

    /**
     * @inheritDoc
     */
    public function wpEntityBundle(): string
    {
        return Ppworks::TAXONOMY__SHOW;
    }
}
