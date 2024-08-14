<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Ppworks;

use Cra\BlemmyaePpworks\Ppworks;
use Cra\BlemmyaePpworks\PpworksEpisodeCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Mapper\AbstractWordpressWebhookMapper;
use Cra\WebhookConsumer\Mapper\MediaTrait;
use Cra\WebhookConsumer\Mapper\PostTrait;
use Cra\WebhookConsumer\Mapper\RedirectTrait;
use Cra\WebhookConsumer\WebhookMapping;
use Cra\WebhookConsumer\WebhookMessageHandler;
use DateTime;
use DateTimeZone;
use Exception;
use Scm\Tools\WpCore;
use WP_Term;

/**
 * Abstract webhook mapper for ppworks_episode and ppworks_segment post types.
 */
abstract class AbstractPodcast extends AbstractWordpressWebhookMapper
{
    use PostTrait;
    use RedirectTrait;
    use MediaTrait;

    /**
     * @inheritDoc
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId
    {
        $publishDate = $this->objectPublishDate();
        $this->postId = $this->upsertWebhookMappingAsPost(
            $this->webhookMappingWithFallback($id),
            $timestamp,
            [
                'post_title' => $this->objectTitle(),
                'post_name' => $this->podcastSlug($id),
                'post_content' => $this->objectDescription(),
                'post_status' => WpCore::POST_STATUS_DRAFT,
                'post_date' => $publishDate ? $publishDate->format('c') : '',
            ]
        );

        try {
            $this->updateAcfFields($id);
        } catch (Exception $exception) {
            // Clean up if the post has been just created.
            $this->cleanupThisPost();

            throw $exception;
        }

        $this->updateThisPostStatus($this->getPostStatus());

        $this->upsertShortUrlRedirect($id, $this->postId);
        $this->removeRedirectsFromPostUri($this->postId);

        return $this->getThisPostEntityId();
    }

    /**
     * Get post status for the podcast.
     *
     * @return string
     * @throws Exception
     */
    protected function getPostStatus(): string
    {
        if (!$this->objectShowInFeeds()) {
            return Ppworks::POST_STATUS__UNFINISHED;
        }

        $utcTz = new DateTimeZone('UTC');
        $publishDate = $this->objectPublishDate();
        $now = new DateTime('now', $utcTz);

        return $now > $publishDate ?
            WpCore::POST_STATUS_PUBLISH : Ppworks::POST_STATUS__TO_BE_PUBLISHED;
    }

    /**
     * Get podcast slug.
     *
     * See https://cra.myjetbrains.com/youtrack/issue/PORT-2062 for details.
     *
     * @param ConsumerObjectId $id
     *
     * @return string
     * @throws Exception
     */
    protected function podcastSlug(ConsumerObjectId $id): string
    {
        $ppId = $id->getId();
        $postId = $this->webhookMapping($id)?->postId ?? 0;

        if ($this->objectShowInFeeds()) {
            # E.g.: 11873-managing-bug-bounty-programs-at-scale-dr-jared-demott-psw-796
            return $this->generateUniquePostSlug("$ppId {$this->objectTitle()}", $postId);
        }

        # E.g. 11873-pauls-security-weekly-796
        # E.g. for vault: 10911-security-weekly-news-vault

        $show = get_term($this->objectShow()->postId, Ppworks::TAXONOMY__SHOW);
        if (!($show instanceof WP_Term)) {
            throw new Exception("Unable to find associated show with podcast $id");
        }

        $slugTitle = "$ppId $show->name ";
        $slugTitle .= $this->isVaultPodcast() ? "vault" : $this->podcastNumber();

        return $this->generateUniquePostSlug($slugTitle, $postId);
    }

    /**
     * Get isVault value of the podcast.
     *
     * @return bool
     */
    abstract protected function isVaultPodcast(): bool;

    /**
     * Get podcast number.
     *
     * @return int
     */
    abstract protected function podcastNumber(): int;

    /**
     * Update ACF fields of the post.
     *
     * @param ConsumerObjectId $id
     *
     * @return void
     * @throws Exception
     */
    protected function updateAcfFields(ConsumerObjectId $id): void
    {
        $this->updateAcfField(
            PpworksEpisodeCT::GROUP_PPWORKS_PODCAST_BASIC__FIELD_YOUTUBE_ID,
            $this->objectYoutubeId()
        );
        $this->updateAcfField(
            PpworksEpisodeCT::GROUP_PPWORKS_PODCAST_BASIC__FIELD_LIBSYN_VIDEO_ID,
            $this->objectLibsynVideoId()
        );
        $this->updateAcfField(
            PpworksEpisodeCT::GROUP_PPWORKS_PODCAST_BASIC__FIELD_LIBSYN_AUDIO_ID,
            $this->objectLibsynAudioId()
        );
        $this->updateAcfField(
            PpworksEpisodeCT::GROUP_PPWORKS_PODCAST_BASIC__FIELD_S3_VIDEO,
            $this->objectS3Video()
        );
        $this->updateAcfField(
            PpworksEpisodeCT::GROUP_PPWORKS_PODCAST_BASIC__FIELD_S3_AUDIO,
            $this->objectS3Audio()
        );

        $this->updateAcfField(
            PpworksEpisodeCT::GROUP_PPWORKS_PODCAST_BASIC__FIELD_TRANSCRIPTION,
            $this->objectTranscription()
        );

        $this->updateAcfField(
            PpworksEpisodeCT::GROUP_PPWORKS_PODCAST_BASIC__FIELD_RAW_TRANSCRIPTION_FILE,
            $this->objectTranscriptionFile()
        );

        $this->updateImageField(
            $this->postId,
            $this->objectS3Screenshot(),
            PpworksEpisodeCT::GROUP_PPWORKS_PODCAST_BASIC__FIELD_FEATURED_IMAGE,
            "Featured image for $id podcast from PPWorks"
        );

        $this->updateApplicationField();
    }

    /**
     * Upsert short URL redirect.
     *
     * @param ConsumerObjectId $id
     * @param int $postId
     *
     * @return void
     * @throws Exception
     */
    abstract protected function upsertShortUrlRedirect(ConsumerObjectId $id, int $postId): void;

    /**
     * Get object show_in_feeds flag.
     *
     * @return bool
     */
    protected function objectShowInFeeds(): bool
    {
        return !empty($this->object->show_in_feeds);
    }

    /**
     * Get object title.
     *
     * @return string
     */
    protected function objectTitle(): string
    {
        return (string)($this->object->title ?? '');
    }

    /**
     * Get object description.
     *
     * @return string
     */
    protected function objectDescription(): string
    {
        return (string)($this->object->description ?? '');
    }

    /**
     * Get object publish date.
     *
     * @return DateTime|null
     */
    protected function objectPublishDate(): ?DateTime
    {
        $date = $this->object->publish_date ?? '';

        return $date ?
            (date_create($date, wp_timezone()) ?: null) :
            null;
    }

    /**
     * Get object S3 Screenshot link.
     *
     * @return string
     */
    protected function objectS3Screenshot(): string
    {
        return (string)($this->object->s3_screenshot ?? '');
    }

    /**
     * Get object YouTube ID.
     *
     * @return string
     */
    protected function objectYoutubeId(): string
    {
        return (string)($this->object->youtube_id ?? '');
    }

    /**
     * Get object Libsyn Video ID.
     *
     * @return string
     */
    protected function objectLibsynVideoId(): string
    {
        return (string)($this->object->libsyn_video_id ?? '');
    }

    /**
     * Get object Libsyn Audio ID.
     *
     * @return string
     */
    protected function objectLibsynAudioId(): string
    {
        return (string)($this->object->libsyn_audio_id ?? '');
    }

    /**
     * Get object S3 Video link.
     *
     * @return string
     */
    protected function objectS3Video(): string
    {
        return (string)($this->object->s3_video ?? '');
    }

    /**
     * Get object S3 Audio link.
     *
     * @return string
     */
    protected function objectS3Audio(): string
    {
        if (property_exists($this->object, 's3_full_audio')) {
            return (string)($this->object->s3_full_audio ?? '');
        }
        if (property_exists($this->object, 's3_audio')) {
            return (string)($this->object->s3_audio ?? '');
        }

        return '';
    }

    /**
     * Get object hosts.
     *
     * @return array|WebhookMapping[]
     * @throws Exception
     */
    protected function objectHosts(): array
    {
        return WebhookMapping::findMultiple(
            $this->object->hosts ?? [],
            Person::TYPE__HOST,
            WebhookMessageHandler::VENDOR__PPWORKS
        );
    }

    /**
     * Get object guests.
     *
     * @return array|WebhookMapping[]
     * @throws Exception
     */
    protected function objectGuests(): array
    {
        return WebhookMapping::findMultiple(
            $this->object->guests ?? [],
            Person::TYPE__GUEST,
            WebhookMessageHandler::VENDOR__PPWORKS
        );
    }

    /**
     * Get object show.
     *
     * @return WebhookMapping
     * @throws Exception
     */
    protected function objectShow(): WebhookMapping
    {
        $mapping = WebhookMapping::findById(
            new ConsumerObjectId(
                WebhookMessageHandler::VENDOR__PPWORKS,
                Show::TYPE,
                (string)($this->object->show ?? '')
            )
        );
        if (!$mapping) {
            throw new Exception('Missing ppworks show webhook mapping.');
        }

        return $mapping;
    }

    /**
     * Get object segments if present.
     *
     * @return WebhookMapping[]|null
     * @throws Exception
     */
    protected function objectSegments(): ?array
    {
        if (!property_exists($this->object, 'segments')) {
            return null;
        }
        return WebhookMapping::findMultiple(
            $this->object->segments ?? [],
            Segment::TYPE,
            WebhookMessageHandler::VENDOR__PPWORKS
        );
    }

    /**
     * Get object categories.
     *
     * @return WebhookMapping[]
     * @throws Exception
     */
    protected function objectCategories(): array
    {
        return WebhookMapping::findMultiple(
            $this->object->categories ?? [],
            Category::TYPE,
            WebhookMessageHandler::VENDOR__PPWORKS
        );
    }

    /**
     * Get object tags.
     *
     * @return array|WebhookMapping[]
     * @throws Exception
     */
    protected function objectTags(): array
    {
        return WebhookMapping::findMultiple(
            $this->object->tags ?? [],
            Tag::TYPE,
            WebhookMessageHandler::VENDOR__PPWORKS
        );
    }

    /**
     * Get object transcription.
     *
     * @return string
     */
    protected function objectTranscription(): string
    {
        return $this->object->transcription ?? '';
    }

    /**
     * Get object transcription file.
     *
     * @return string
     */
    protected function objectTranscriptionFile(): string
    {
        return $this->object->transcription_file ?? '';
    }
}
