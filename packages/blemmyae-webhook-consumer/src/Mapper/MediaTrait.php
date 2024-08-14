<?php

/**
 * @author  Konstantin Gusev <konstantin.gusev@cyberriskalliance.com>
 * @license proprietary
 */

namespace Cra\WebhookConsumer\Mapper;

use Cra\WebhookConsumer\Logger;
use Exception;
use Scm\Tools\WpCore;

/**
 * Trait which provides helper media related methods.
 */
trait MediaTrait
{
    /**
     * Update image field.
     *
     * @param int|string $acfEntityId e.g. post ID, "{TAXONOMY}_{TERM_ID}"
     * @param string $imageUrl
     * @param string $field
     * @param string $description
     *
     * @return int|null
     */
    protected function updateImageField(
        int|string $acfEntityId,
        string $imageUrl,
        string $field,
        string $description = ''
    ): ?int {
        if (empty($imageUrl)) {
            update_field($field, null, $acfEntityId);

            return null;
        }

        $currentImageId = get_field($field, $acfEntityId, false);
        $currentImageId = $currentImageId ? (int)$currentImageId : null;
        $newImageIds = $this->upsertImages([$imageUrl], $description);
        $newImageId = $newImageIds[0] ?? $currentImageId;
        if ($newImageId !== $currentImageId) {
            update_field($field, $newImageId, $acfEntityId);
        }

        return $newImageId;
    }

    /**
     * Upsert images into WordPress.
     *
     * @param string[] $imageUrls Image URLs to upsert into WordPress.
     * @param string $description (optional) Description for inserted images.
     *
     * @return array|int[] Returns array of image IDs.
     */
    protected function upsertImages(array $imageUrls, string $description = ''): array
    {
        $outputImageIds = [];
        foreach ($imageUrls as $imageUrl) {
            try {
                $outputImageIds[] = WpCore::upsertMediaByUrl($imageUrl, $description);
            } catch (Exception $exception) {
                (new Logger())->warning(
                    'Error uploading image.',
                    ['exception' => $exception->getMessage()]
                );
            }
        }

        return $outputImageIds;
    }
}
