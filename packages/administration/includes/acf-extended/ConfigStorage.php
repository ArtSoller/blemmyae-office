<?php

/**
 * ConfigStorage.
 *
 * @author Eugene Yakovenko (yakoveka@gmail.com)
 */

namespace Scm\Acf_Extended;

use Cra\BlemmyaePpworks\PpworksAnnouncementCT;
use Cra\BlemmyaePpworks\PpworksArticleCT;
use Cra\BlemmyaePpworks\PpworksEpisodeCT;
use Cra\BlemmyaePpworks\PpworksSegmentCT;
use Cra\BlemmyaePpworks\PpworksSponsorProgramCT;
use Cra\CtCompanyProfile\CompanyProfileCT;
use Cra\CtEditorial\EditorialCT;
use Cra\CtLanding\LandingCT;
use Cra\CtLearning\LearningCT;
use Cra\CtLearning\SessionCT;
use Cra\CtNewsletter\NewsletterCT;
use Cra\CtPeople\PeopleCT;
use Cra\CtPeople\ScAwardNomineeCT;
use Cra\CtPeople\TestimonialCT;
use Cra\CtProductProfile\ProductProfileCT;
use Cra\CtWhitepaper\WhitepaperCT;

class ConfigStorage
{
    // @phpstan-ignore-next-line
    private string $configPath = ABSPATH . 'packages/administration/config/';

    /**
     * Initialize hooks.
     */
    public function __construct()
    {
        // We only want to save JSONs of field groups. Otherwise, ACF always tries to load it from JSON.
        add_filter('acf/settings/save_json', [$this, 'overrideLocalPathSave'], 10, 1);
        // We do want to both save and load field groups from PHP.
        add_filter('acf/settings/acfe/php_save', [$this, 'overrideLocalPathSave'], 10, 1);
        add_filter('acf/settings/acfe/php_load', [$this, 'overrideLocalPathLoad'], 10, 1);
        // Post types.
        add_filter('acf/settings/save_json/type=acf-post-type', [$this, 'overridePostTypesSave']);
        add_filter('acf/settings/acfe/php_load/post_types', [$this, 'overridePostTypesLoad']);
        add_filter('acf/settings/acfe/php_save/post_types', [$this, 'overridePostTypesSave']);
        add_filter('acf/settings/acfe/json_save/post_types', [$this, 'overridePostTypesSave']);
        // Taxonomy.
        add_filter('acf/settings/save_json/type=acf-taxonomy', [$this, 'overrideTaxonomySave']);
        add_filter('acf/settings/acfe/php_load/taxonomies', [$this, 'overrideTaxonomyLoad']);
        add_filter('acf/settings/acfe/php_save/taxonomies', [$this, 'overrideTaxonomySave']);
        add_filter('acf/settings/acfe/json_save/taxonomies', [$this, 'overrideTaxonomySave']);
    }

    /**
     * @return string
     */
    public function overrideLocalPath(): string
    {
        return $this->configPath . 'field_group';
    }

    /**
     * @param mixed $path
     * @return string
     */
    public function overrideLocalPathSave(mixed $path): string
    {
        return $this->overrideLocalPath();
    }

    /**
     * @param mixed $paths
     * @return string[]
     */
    public function overrideLocalPathLoad(mixed $paths): array
    {
        return [$this->overrideLocalPath()];
    }

    /**
     * @return string
     */
    public function overridePostTypes(): string
    {
        return $this->configPath . 'post_types';
    }

    /**
     * @param mixed $path
     * @return string
     */
    public function overridePostTypesSave(mixed $path): string
    {
        return $this->overridePostTypes();
    }

    /**
     * @param mixed $path
     * @return string[]
     */
    public function overridePostTypesLoad(mixed $path): array
    {
        return [$this->overridePostTypes()];
    }

    /**
     * @return string
     */
    public function overrideTaxonomy(): string
    {
        return $this->configPath . 'taxonomy';
    }

    /**
     * @param mixed $path
     * @return string
     */
    public function overrideTaxonomySave(mixed $path): string
    {
        return $this->overrideTaxonomy();
    }

    /**
     * @param mixed $path
     * @return string[]
     */
    public function overrideTaxonomyLoad(mixed $path): array
    {
        return [$this->overrideTaxonomy()];
    }

    /**
     * Get custom post types for places where acf is not active yet.
     *
     * @return array<string, array<string, string>>
     */
    public static function getCustomPostTypes(): array
    {
        return [
            CompanyProfileCT::POST_TYPE => ['graphql_single_name' => CompanyProfileCT::GRAPHQL_NAME],
            EditorialCT::POST_TYPE => ['graphql_single_name' => EditorialCT::GRAPHQL_NAME],
            LandingCT::POST_TYPE => ['graphql_single_name' => LandingCT::GRAPHQL_NAME],
            LearningCT::POST_TYPE => ['graphql_single_name' => LearningCT::GRAPHQL_NAME],
            SessionCT::POST_TYPE => ['graphql_single_name' => SessionCT::GRAPHQL_NAME],
            NewsletterCT::POST_TYPE => ['graphql_single_name' => NewsletterCT::GRAPHQL_NAME],
            PeopleCT::POST_TYPE => ['graphql_single_name' => PeopleCT::GRAPHQL_NAME],
            ProductProfileCT::POST_TYPE => ['graphql_single_name' => ProductProfileCT::GRAPHQL_NAME],
            WhitepaperCT::POST_TYPE => ['graphql_single_name' => WhitepaperCT::GRAPHQL_NAME],
            PpworksArticleCT::POST_TYPE => ['graphql_single_name' => PpworksArticleCT::GRAPHQL_NAME],
            PpworksAnnouncementCT::POST_TYPE => ['graphql_single_name' => PpworksAnnouncementCT::GRAPHQL_NAME],
            PpworksEpisodeCT::POST_TYPE => ['graphql_single_name' => PpworksEpisodeCT::GRAPHQL_NAME],
            PpworksSegmentCT::POST_TYPE => ['graphql_single_name' => PpworksSegmentCT::GRAPHQL_NAME],
            PpworksSponsorProgramCT::POST_TYPE => ['graphql_single_name' => PpworksSponsorProgramCT::GRAPHQL_NAME],
            ScAwardNomineeCT::POST_TYPE => ['graphql_single_name' => ScAwardNomineeCT::GRAPHQL_NAME],
            TestimonialCT::POST_TYPE => ['graphql_single_name' => TestimonialCT::GRAPHQL_NAME],
        ];
    }
}
