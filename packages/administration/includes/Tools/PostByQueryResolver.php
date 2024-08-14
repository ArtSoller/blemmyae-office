<?php

namespace Scm\Tools;

use Cra\BlemmyaeApplications\WP_GraphQL\Options;
use Exception;
use WPGraphQL\AppContext;

class PostByQueryResolver
{
    # ex. Editorial::TYPE or 'editorial'
    protected string $postType;

    # ex. 'Editorial'
    protected string $ctName;

    # ex. 'ct-editorial'
    protected string $ctDomain;

    /**
     * Adds actions to register graphql types and fields
     */
    public function __construct($postType)
    {
        $this->postType = $postType;
        $this->ctName = ucfirst($postType);
        $this->ctDomain = 'ct-' . $this->postType;
        $this->registerFields();
    }

    /**
     * @return void
     */
    protected function registerFields(): void
    {
        // ex. editorialByQuery
        add_action('graphql_register_types', [$this, 'postByQuery'], 10);
    }

    /**
     * Registers LandingByQuery graphQL field.
     *
     * This function supports landingBySlug and landingPreviewById fields.
     * @throws Exception
     */
    public function postByQuery(): void
    {
        $applicationsArgsConfig = [
            'applications' => [
                'type' => ['list_of' => 'ID'],
                'description' => __('List of application slugs.', $this->ctDomain),
            ],
        ];

        $config = [
            'type' => $this->ctName,
            'description' => __("$this->ctName resolved object.", $this->ctDomain),
            'args' => [
                ...$applicationsArgsConfig,
                'slug' => [
                    'type' => 'ID',
                    'description' => __("$this->ctName SLUG only.", $this->ctDomain),
                ],
            ],
            'resolve' =>
                fn($source, $args, $context, $info) => $this->postBySlugResolver($args, $context),
        ];

        register_graphql_field('RootQuery', $this->postType . 'BySlug', $config);
    }

    /**
     * Function generates blocks queue and initiates queue resolving.
     *
     * @param array $args
     * @param AppContext $context
     *
     * @return object|null
     */
    protected function postBySlugResolver(array $args, AppContext $context): ?object
    {
        return Options::graphqlPostResolverByApplicationSlug($args, $context, $this->postType);
    }
}
