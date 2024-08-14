<?php

declare(strict_types=1);

namespace Cra\CtEditorial\Sync\Vendor\Innodata;

use Cra\CtEditorial\Entity\Editorial;
use Cra\CtEditorial\Sync\Vendor\ParserInterface;
use DateTime;
use DOMDocument;
use DOMXPath;
use Exception;
use Scm\Tools\Logger;

/**
 * Class Parser which parses Innodata feed items into Editorial Brief.
 */
final class Parser implements ParserInterface
{
    private DOMXPath $xpath;

    private Editorial $editorial;

    private array $vendorFields = [];

    /**
     * @inheritDoc
     *
     * @param string $input Innodata XML contents.
     */
    public function parse(string $input): Editorial
    {
        $this->initInput($input);
        $this->editorial = new Editorial('innodata');
        $this->vendorFields = [];

        $this->setAlwaysPredefinedFields();
        $this->parseUniqueId();
        $this->parseDate();
        $this->parseTopic();
        $this->parsePublishedDate();
        $this->parseHeadline();
        $this->parseSource();
        $this->parseDescription();

        // In case we receive malformed xml without uid, we need to be sure that we are still able to save it.
        $this->customUniqueId();

        $this->editorial->vendorFields()->set($this->vendorFields);

        return $this->editorial;
    }

    private function initInput(string $xmlString): void
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->loadXML($xmlString);
        $this->xpath = new DOMXPath($document);
        $this->xpath->registerNamespace('i', 'http://iptc.org/std/nar/2006-10-01/');
    }

    /**
     * Set always predefined fields for any feed item.
     *
     * @throws Exception
     */
    private function setAlwaysPredefinedFields(): void
    {
        $this->editorial->type('brief');
        $this->editorial->brand('sc-media');
        $this->editorial->author('sc-staff');
    }

    /**
     * Parse unique ID.
     *
     * @throws Exception
     */
    private function parseUniqueId(): void
    {
        $itemMeta = $this->xpath
            ->query('/i:newsItem/i:itemMeta')
            ->item(0);
        if (!isset($itemMeta)) {
            throw new Exception('Missing id attribute on itemMeta!');
        }
        $this->vendorFields['uid'] = $itemMeta->attributes->getNamedItem('id')->nodeValue;
    }

    /**
     * Custom unique id based on title + date values.
     *
     * @throws Exception
     */
    private function customUniqueId(): void
    {
        $this->vendorFields['uid'] ??= md5(
            $this->editorial->title() .
            ($this->editorial->date() instanceof DateTime ? $this->editorial->date()->getTimestamp() : '')
        );
    }

    /**
     * Parse date.
     *
     * @throws Exception
     */
    private function parseDate(): void
    {
        $versionCreated = $this->xpath
            ->query('/i:newsItem/i:itemMeta/i:versionCreated')
            ->item(0);
        if (!isset($versionCreated)) {
            throw new Exception('Missing versionCreated in itemMeta!');
        }
        $this->editorial->date($versionCreated->nodeValue);
    }

    /**
     * Parse topic.
     *
     * @throws Exception
     */
    private function parseTopic(): void
    {
        $title = $this->xpath
            ->query('/i:newsItem/i:itemMeta/i:title')
            ->item(0);
        if (!isset($title)) {
            throw new Exception('Missing title in itemMeta!');
        }
        $this->vendorFields['topic'] = $title->nodeValue;
        $this->editorial->topics($this->mapInnodataTopicToEditorialTopicSlugs($title->nodeValue));
        $this->editorial->industry($this->mapInnodataTopicToEditorialIndustrySlug($title->nodeValue) ?: null);
    }

    /**
     * Map Innodata topic to Editorial topic slugs.
     *
     * @param string $topic
     *
     * @return string[]
     */
    private function mapInnodataTopicToEditorialTopicSlugs(string $topic): array
    {
        switch (trim(strtolower($topic))) {
            case 'cloud security':
                return ['cloud', 'cloud-security'];
            case 'threat intelligence':
                return ['threat-intelligence'];
            case 'information breaches':
                return ['risk-management', 'breach'];
            default:
                Logger::log("Unknown Innodata topic: $topic", 'warning');
                return ['uncategorized'];
        }
    }

    /**
     * Map Innodata topic to Editorial industry slugs.
     *
     * @param string $topic
     *
     * @return string
     */
    private function mapInnodataTopicToEditorialIndustrySlug(string $topic): string
    {
        switch (trim(strtolower($topic))) {
            case 'government news':
                return 'government';
            default:
                Logger::log("Unknown Innodata industry: $topic", 'warning');
                return '';
        }
    }

    /**
     * Parse published date.
     *
     * @throws Exception
     */
    private function parsePublishedDate(): void
    {
        $published = $this->xpath
            ->query('/i:newsItem/i:contentMeta/i:dateline')
            ->item(0);
        if (!isset($published)) {
            throw new Exception('Missing dateline in contentMeta!');
        }
        $this->vendorFields['published'] = $published->nodeValue;
    }

    /**
     * Parse headline into title and subtitle.
     *
     * @throws Exception
     */
    private function parseHeadline(): void
    {
        $headlines = $this->xpath->query('/i:newsItem/i:contentMeta/i:headline');
        if (!$headlines || !$headlines->length) {
            throw new Exception('Missing headline in contentMeta!');
        }
        $titleIndex = $headlines->length - 1;
        $this->editorial->title(preg_replace(
            '/[[:^print:]]/',
            '',
            $headlines->item($titleIndex)->nodeValue
        ));
        if ($titleIndex > 0) {
            $this->vendorFields['subtitle'] = preg_replace(
                '/[[:^print:]]/',
                '',
                $headlines->item(0)->nodeValue
            );
        }
    }

    /**
     * Parse source.
     *
     * @throws Exception
     */
    private function parseSource(): void
    {
        $sourceName = $this->xpath
            ->query('/i:newsItem/i:contentMeta/i:infoSource/i:name')
            ->item(0);
        if (!isset($sourceName)) {
            throw new Exception('Missing infoSource in contentMeta!');
        }
        $this->vendorFields['sourceName'] = $sourceName->nodeValue;

        $inline = $this->xpath
            ->query('/i:newsItem/i:contentMeta/i:description/i:inline')
            ->item(0);
        if (!isset($inline)) {
            return;
        }
        $this->vendorFields['sourceUrl'] = $inline->attributes->getNamedItem('uri')->nodeValue;
    }

    /**
     * Parse description.
     *
     * @throws Exception
     */
    private function parseDescription(): void
    {
        $description = $this->xpath
            ->query('/i:newsItem/i:contentMeta/i:description')
            ->item(0);
        if (!isset($description)) {
            throw new Exception('Missing description in contentMeta!');
        }

        $body = strip_tags(nl2br($description->C14N() ?: ''), '<inline><a><br>');
        // e.g. <inline uri="https://threatpost.com/apple-airtag-zero-day-trackers/175143/">Threatpost</inline>
        $regex = '/<inline.*?uri="(.*?)".*?>(.*?)<\/inline>/m';
        $body = preg_replace($regex, '<a href="$1" target="_blank">$2</a>', $body);
        $body = preg_replace('/[[:^print:]]/', '', $body);
        $this->editorial->body($body);
    }
}
