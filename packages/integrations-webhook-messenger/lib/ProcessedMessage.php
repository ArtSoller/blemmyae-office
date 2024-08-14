<?php

/**
 * @licence proprietary
 *
 * @author Eugene Yakovenko <yakoveka@gmail.com>
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger;

/**
 * Message class for the processed webhook queue.
 */
final class ProcessedMessage implements ProcessedMessageInterface
{
    use ValidatorTrait;
    use ConsumerMessageTrait;

    private string $postId;

    private string $postType;

    private string $status;

    /**
     * Construct ConsumerMessage from raw $message.
     *
     * @param ConsumerMessageInterface $message
     * @param null|array<string,mixed> $data
     */
    public function __construct(ConsumerMessageInterface $message, ?array $data = null)
    {
        $this->setEvent($message->getEvent());
        $this->setObjectId($message->getObjectId());
        $this->setObject($message->getObject());
        $this->setTimestamp($message->getTimestamp());

        $this->setPostId($data['postId'] ?? '');
        $this->setPostType($data['postType'] ?? '');
        $this->setStatus($data['status'] ?? '');
    }

    /**
     * @inheritDoc
     */
    public function getPostId(): string
    {
        return $this->postId;
    }

    /**
     * @inheritDoc
     */
    public function setPostId(string $postId): void
    {
        $this->validateValue($postId, 'postId');
        $this->postId = $postId;
    }

    /**
     * @inheritDoc
     */
    public function getPostType(): string
    {
        return $this->postType;
    }

    /**
     * @inheritDoc
     */
    public function setPostType(string $postType): void
    {
        $this->validateValue($postType, 'postType');
        $this->postType = $postType;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function setStatus(string $status): void
    {
        $this->validateValue($status, 'status');
        $this->status = $status;
    }
}
