<?php

declare(strict_types=1);

namespace Cra\CtEditorial\Entity\Vendor\Innodata;

use Cra\CtEditorial\Entity\Vendor\FieldsInterface;
use DateTime;

/**
 * Class for Innodata specific vendor fields.
 */
class Fields implements FieldsInterface
{
    private ?string $uid = null;

    private ?DateTime $published = null;

    private ?string $topic = null;

    private ?string $subtitle = null;

    private ?string $sourceName = null;

    private ?string $sourceUrl = null;

    /**
     * {@inheritDoc}
     */
    public function uniqueIdFieldName(): string
    {
        return 'uid';
    }

    /**
     * {@inheritDoc}
     */
    public function uniqueId(): string
    {
        return $this->uid ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function set(array $values): self
    {
        if (isset($values['uid'])) {
            $this->uid = $values['uid'];
        }
        if (!empty($values['published'])) {
            $this->published = new DateTime($values['published']);
        }
        if (isset($values['topic'])) {
            $this->topic = $values['topic'];
        }
        if (isset($values['subtitle'])) {
            $this->subtitle = $values['subtitle'];
        }
        if (isset($values['sourceName'])) {
            $this->sourceName = $values['sourceName'];
        }
        if (isset($values['sourceUrl'])) {
            $this->sourceUrl = $values['sourceUrl'];
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function repeaterArray(): array
    {
        return [
            'acf_fc_layout' => 'innodata',
            'uid' => $this->uid ?? '',
            // Date format example: 1990-12-20 21:00:00
            'published' => $this->published ? $this->published->format('Y-m-d H:i:s') : '',
            'topic' => $this->topic ?? '',
            'subtitle' => $this->subtitle ?? '',
            'source' => [
                'title' => $this->sourceName ?? '',
                'url' => $this->sourceUrl ?? '',
                'target' => '_blank',
            ],
        ];
    }
}
