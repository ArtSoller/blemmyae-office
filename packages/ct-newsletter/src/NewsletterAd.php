<?php

declare(strict_types=1);

namespace Cra\CtNewsletter;

use Advanced_Ads;
use Cra\BlemmyaeApplications\BlemmyaeApplications;
use DateTime;
use DateTimeZone;
use Exception;
use RuntimeException;
use Scm\Tools\PsrLogger;
use Scm\Tools\Utils;
use Scm\Tools\WpCore;
use WP_Error;

class NewsletterAd
{
    public const SCHEDULED_AD_PERIODS_BY_POST = 'a9s_scheduled_ad_periods_by_post';

    /**
     * A9s app machine name.
     *
     * @var string
     */
    protected string $app = '';
    /**
     * List of vendors.
     *
     * @var array
     */
    protected array $vendors = [];
    /**
     * Available vendors as ad groups.
     *
     * @var array|null
     */
    protected ?array $vendorTerms = [];
    /**
     * Global schedule configuration.
     *
     * @var array
     */
    protected array $schedule = [];

    /**
     * Count of ad slots.
     *
     * @var int
     */
    protected int $slotCount = 8;

    /**
     * Wraps ad output.
     *
     * @param string $ad
     *
     * @return string
     */
    public static function wrapAd(string $ad = ''): string
    {
        add_filter('wp_lazy_loading_enabled', '__return_false');
        return "<div style=\"text-align: center;\">$ad</div>";
    }

    /**
     *  NewsletterAd constructor.
     */
    public function __construct($app)
    {
        try {
            $this->app = $app;
            if (BlemmyaeApplications::isA9sAppSupported($this->app)) {
                add_action('init', [$this, 'initHooks'], 11);

                add_action(
                    'wp_ajax_sendpressSaveAdPeriod',
                    static function () {
                        // @todo review deprecated
                        $period = filter_input(
                            INPUT_POST,
                            'sendpressAdPeriod',
                            FILTER_SANITIZE_STRING
                        ) ?: 'Auto';
                        $postId = filter_input(
                            INPUT_POST,
                            'sendpressPostID',
                            FILTER_SANITIZE_STRING
                        ) ?: 0;

                        $periods = get_option(self::SCHEDULED_AD_PERIODS_BY_POST) ?: [];
                        $periods[$postId] = $period;
                        update_option(self::SCHEDULED_AD_PERIODS_BY_POST, $periods);

                        echo 'Success';
                    }
                );
            }
        } catch (Exception | RuntimeException $error) {
            (new PsrLogger())->warning($error->getMessage(), $error->getTrace());
        }
    }

    /**
     * Init hooks.
     *
     * @return void
     * @throws Exception
     */
    public function initHooks(): void
    {
        $this->vendors();
        $this->loadScheduleAdsConfiguration();
        if (!class_exists(Advanced_Ads::class)) {
            return;
        }
        $this->vendorTerms ?: $this->vendorTerms();
        $this->importSponsorAdGroups();
    }

    /**
     * Get ad groups
     *
     * @return array
     */
    private function getAdGroups(): array
    {
        $taxonomyName = Advanced_Ads::AD_GROUP_TAXONOMY;
        $terms = get_terms($taxonomyName, ['hide_empty' => false]);
        $adGroups = [];

        foreach ($terms as $term) {
            $termName = $term->name;
            $key = strtolower($termName);
            $key = str_replace(' ', '_', $key);
            $adGroups[$key] = $termName;
        }

        return $adGroups;
    }

    /**
     * Checks if value in cache exists.
     *
     * @param mixed $value
     *
     * @return bool
     */
    private function isCacheValid(mixed $value): bool
    {
        return !empty($value);
    }

    /**
     * Sets vendors.
     *
     * @return array<int, string>
     */
    private function vendors(): array
    {
        if (!$this->vendors) {
            $cacheGroup = 'newsletter_ad_vendors';
            $vendors = wp_cache_get($this->app, $cacheGroup);
            if (!$this->isCacheValid($vendors)) {
                $vendors = [];
                $vendorsData = Utils::importCsv(__DIR__ . "/../assets/ads/vendors/$this->app.csv");
                foreach ($vendorsData as $vendor) {
                    $vendors[$vendor[1]] = $vendor[0]; // id => company
                }
                wp_cache_set($this->app, $vendors, $cacheGroup, WpCore::DAY_IN_SECONDS);
            }
            $this->vendors = $vendors;
        }

        return $this->vendors;
    }

    /**
     * Load "schedule" ads configuration.
     *
     * @return array<string, mixed>
     */
    private function loadScheduleAdsConfiguration(): array
    {
        if (!$this->schedule) {
            $cacheGroup = 'newsletter_ad_schedule';
            $schedule = wp_cache_get($this->app, $cacheGroup);
            if (!$this->isCacheValid($schedule)) {
                $schedule = array_map(
                    static fn(array $row) => [
                        'dayOfWeek' => $row[0],
                        'period' => $row[1],
                        'companyIdInOrder' => array_filter(['', ...explode('>', $row[2] ?? '')]),
                        // notice: start array id from `1`
                    ],
                    Utils::importCsv(__DIR__ . "/../assets/ads/schedule/$this->app.csv")
                );
                wp_cache_set($this->app, $schedule, $cacheGroup, WpCore::DAY_IN_SECONDS);
            }
            if (Utils::isDev()) {
                foreach ($schedule as $index => $scheduleItem) {
                    if ($scheduleItem['period'] === 'PM') {
                        // Shift array indexes by one and after that revert elements of array
                        $companyIds = array_reverse($scheduleItem['companyIdInOrder']);
                        $schedule[$index]['companyIdInOrder'] = array_combine(
                            range(1, count($companyIds)),
                            array_values($companyIds)
                        );
                    }
                }
            }
            $this->schedule = $schedule;
        }

        return $this->schedule;
    }

    /**
     * Currently scheduled.
     *
     * @param string $period
     *
     * @return int[]
     * @throws Exception
     */
    public function scheduledAdGroupIds(string $period = 'Auto'): array
    {
        $now = new DateTime('now', wp_timezone());

        // Filter out by day.
        /** @var array{dayOfWeek: string, period: string, companyIdInOrder: int[]}[] $current */
        $current = array_filter(
            $this->schedule,
            static fn($item) => $item['dayOfWeek'] === $now->format('l')
        );

        // Filter out by period.
        if ($period === 'Auto') {
            $period = $now->format('A');
        }
        $current = array_filter($current, static fn($item) => $item['period'] === $period);

        $current = array_shift($current);
        return $current['companyIdInOrder'] ?? [];
    }

    /**
     * Import sponsor ad groups
     */
    protected function importSponsorAdGroups(): void
    {
        $taxonomy = Advanced_Ads::AD_GROUP_TAXONOMY;
        $groupsData = Utils::importCsv(__DIR__ . "/../assets/ads/groups/$this->app.csv");
        $prefix = 'Ce2e-Sponsor-';

        foreach ($groupsData as $group) {
            $groupName = str_replace(' ', '-', $group[0]);
            if (!term_exists($prefix . $groupName, $taxonomy)) {
                wp_insert_term($prefix . $groupName, $taxonomy);
            }
        }
    }

    /**
     * Ad Groups for vendors.
     *
     * @return array<int, string>
     */
    protected function vendorTerms(): array
    {
        if (!class_exists('Advanced_Ads')) {
            return [];
        }

        if (!$this->vendorTerms) {
            $cacheGroup = 'newsletter_ad_vendor_terms';
            $vendors = wp_cache_get($this->app, $cacheGroup);
            if (!$this->isCacheValid($vendors)) {
                $taxonomy = Advanced_Ads::AD_GROUP_TAXONOMY;

                if (!taxonomy_exists($taxonomy)) {
                    $advancedAds = Advanced_Ads::get_instance();
                    if ($advancedAds instanceof Advanced_Ads) {
                        $advancedAds->create_post_types();
                    }
                }

                foreach ($this->vendors as $vendor) {
                    if (term_exists($vendor, $taxonomy)) {
                        continue;
                    }

                    wp_insert_term($vendor, $taxonomy);
                }

                /** @var array<int, string>|WP_Error $names */
                $names = get_terms([
                    'taxonomy' => Advanced_Ads::AD_GROUP_TAXONOMY,
                    'hide_empty' => false,
                    'fields' => 'id=>name'
                ]);

                if (is_wp_error($names)) {
                    (new PsrLogger())->warning('Unable to get ad vendor terms', ['error' => $names]);
                    return [];
                }

                $vendors = array_intersect(
                    $names,
                    $this->vendors
                );

                wp_cache_set($this->app, $vendors, $cacheGroup, WpCore::DAY_IN_SECONDS);
            }
            $this->vendorTerms = $vendors;
        }

        return $this->vendorTerms;
    }

    /**
     * Render Ad according to slot order and time period(am/pm).
     *
     * @param int $order
     * @param string $timePeriod
     * @return string
     * @throws Exception
     */
    public function renderAd(int $order, string $timePeriod): string
    {
        $schedule = $this->loadScheduleAdsConfiguration();
        $vendors = $this->vendors();
        $weekDay = (new DateTime('now', new DateTimeZone('UTC')))->format('l');
        $vendor = null;
        foreach ($schedule as $value) {
            extract($value, EXTR_OVERWRITE, 'ad');
            if ($dayOfWeek == $weekDay && strtolower($period) == $timePeriod) {
                $vendor = $companyIdInOrder[$order];
                break;
            }
        }

        $adGroup = !empty($vendors[$vendor]) ? $vendors[$vendor] : null;

        $adHtml = '';
        if ($adGroup) {
            $termId = WpCore::getTermByName(Advanced_Ads::AD_GROUP_TAXONOMY, $adGroup)->term_id;
            $adHtml = do_shortcode("[the_ad_group id=\"$termId\"]");
        }

        return self::wrapAd($adHtml);
    }

    /**
     * Render Current Day Ad according to time period(am/pm).
     * @param string $timePeriod
     * @return array
     * @throws Exception
     */
    public function renderCurrentDayAds(string $timePeriod): array
    {
        $currentDayAds = [];
        for ($slotId = 1; $slotId <= $this->slotCount; $slotId++) {
            $currentDayAds[] = $this->renderAd($slotId, $timePeriod);
        }

        return $currentDayAds;
    }
}
