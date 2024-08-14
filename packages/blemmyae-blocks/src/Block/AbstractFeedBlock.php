<?php

/**
 * AbstractBlock class, adds necessary fields and methods
 * to all descents - ContentTeaserBlock and Block
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Block;

/**
 * AbstractBlock class
 */
abstract class AbstractFeedBlock extends AbstractBlock
{
    /**
     * @var array<string, mixed>
     */
    protected array $supportedOptions;

    public int $page = 1;

    public int $pageOffset = 0;

    public int $nativePageOffset = 0;

    /**
     * @var string[]
     */
    public array $postType;

    /**
     * @var array<string, mixed>
     */
    public array $taxonomyQuery;

    /**
     * @var array<string, mixed>
     */
    public array $options;

    /**
     * @var int[]
     */
    public array $resolvedPostIds = [];

    public int $initialNumberOfItems;

    public int $numberOfItems;

    public int $numberOfNatives;

    public int $nativeAdFrequency;

    /**
     * @var string[]
     */
    public array $applications = [];

    /**
     * @var object{ID: int}|null
     */
    public ?object $author = null;

    /**
     * @var array<int, object{slug: string}>
     */
    public array $nativeAdTopics = [];

    /**
     * @var object{ID: int}|null
     */
    public ?object $nativeAdSponsor = null;

    /**
     * @var array<string, string>
     */
    public static array $blockOptions = [
        'nonDfpNatives' => 'nonDfpNatives',
        'nativeAds' => 'nativeAds',
        'upcomingEvents' => 'upcomingEvents',
        'onDemandEvents' => 'ondemandEvents',
        'ongoingEvents' => 'ongoingEvents',
    ];

    /**
     * Class constructor.
     *
     * @param array<string, mixed> $config
     * @param array<string, mixed> $supportedOptions
     * @param string $name
     */
    public function __construct(array $config, array $supportedOptions, string $name)
    {
        $this->config = $config;
        $this->supportedOptions = $supportedOptions;
        $this->name = $name;
    }

    /**
     * @param string $optionName
     *
     * @return bool
     */
    private function supportsOption(string $optionName): bool
    {
        return in_array($optionName, $this->supportedOptions, true);
    }

    /**
     * Returns array with keys being all possible block options,
     * and values - booleans indicating whether option is set
     * or not
     *
     * @return array<string, bool>
     */
    public function parsedBlockOptions(): array
    {
        $parsedBlockOptions = [];
        foreach (self::$blockOptions as $optionName) {
            $parsedBlockOptions[$optionName] = in_array($optionName, $this->options ?? [], true) &&
                                               $this->supportsOption($optionName);
        }

        return $parsedBlockOptions;
    }

    /**
     * Calculates number of feed items to resolve, excluding
     * natives and pinned posts
     *
     * @return int
     */
    protected function calculateNumberOfItemsToResolve(): int
    {
        [
            self::$blockOptions['nonDfpNatives'] => $hasNonDfpNatives,
            self::$blockOptions['nativeAds'] => $hasNativeAds,
        ] = $this->parsedBlockOptions();

        return (int)floor(
            $hasNonDfpNatives || $hasNativeAds ?
                $this->initialNumberOfItems - count($this->resolvedPostIds) - $this->numberOfNatives :
                $this->initialNumberOfItems - count($this->resolvedPostIds)
        );
    }
}
