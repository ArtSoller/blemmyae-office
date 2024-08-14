<?php

/**
 * @licence proprietary
 *
 * @author Eugene Yakovenko <yakoveka@gmail.com>
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger;

use ValueError;

/**
 * Interface for processed webhook messages.
 */
interface ProcessedMessageInterface extends ConsumerMessageInterface
{
    /**
     * Get status of the processed message.
     *
     * @return string
     */
    public function getStatus(): string;

    /**
     * Set status of the processed message.
     *
     * @param string $status
     *
     * @throws ValueError
     */
    public function setStatus(string $status): void;

    /**
     * @return string
     */
    public function getPostId(): string;

    /**
     * @param string $postId
     *
     * @throws ValueError
     */
    public function setPostId(string $postId): void;

    /**
     * @return string
     */
    public function getPostType(): string;

    /**
     * @param string $postType
     *
     * @throws ValueError
     */
    public function setPostType(string $postType): void;
}
