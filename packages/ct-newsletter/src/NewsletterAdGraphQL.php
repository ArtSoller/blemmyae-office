<?php

declare(strict_types=1);

namespace Cra\CtNewsletter;

use Exception;

class NewsletterAdGraphQL
{
    public function __construct()
    {
        $this->registerFields();
    }

    /**
     * @return void
     */
    protected function registerFields(): void
    {
        add_action('graphql_register_types', [$this, 'newsletterAdByOrderPeriodApp'], 10);
        add_action('graphql_register_types', [$this, 'newsletterAdsByPeriodApp'], 10);
    }

    /**
     * Registers newsletterAdByGroup graphQL field.
     *
     * @throws Exception
     */
    public function newsletterAdByOrderPeriodApp(): void
    {
        $newsletterAdArgs = [
            'order' => [
                'type' => 'Integer',
                'description' => 'Ad slot number.',
            ],
            'period' => [
                'type' => 'String',
                'description' => 'Time period (am/pm).',
            ],
            'app' => [
                'type' => 'String',
                'description' => 'App name.',
            ],
        ];
        $config = [
            'type' => 'String',
            'description' => "NewsletterAdByGroup resolved object.",
            'args' => [
                ...$newsletterAdArgs
            ],
            'resolve' =>
                fn($source, $args, $context, $info) => $this->graphqlNewsletterAdResolverByOrderPeriod($args),
        ];

        register_graphql_field('RootQuery', 'newsletterAdByOrder', $config);
    }

    /**
     * @param array $args
     * @return string|null
     * @throws Exception
     */
    protected function graphqlNewsletterAdResolverByOrderPeriod(array $args): ?string
    {
        if (empty($args['order']) || empty($args['period']) || empty($args['app'])) {
            return null;
        }

        return (new NewsletterAd($args['app']))->renderAd($args['order'], $args['period']);
    }

    /**
     * Registers newsletterAdByGroup graphQL field.
     *
     * @throws Exception
     */
    public function newsletterAdsByPeriodApp(): void
    {
        $newsletterAdArgs = [
            'period' => [
                'type' => 'String',
                'description' => 'Time period (am/pm).',
            ],
            'app' => [
                'type' => 'String',
                'description' => 'App name.',
            ],
        ];
        $config = [
            'type' => ['list_of' => 'String'],
            'description' => "NewsletterCurrentDayAds resolved object.",
            'args' => [
                ...$newsletterAdArgs
            ],
            'resolve' =>
                fn($source, $args, $context, $info) => $this->graphqlNewsletterCurrentDayAdsResolver($args),
        ];

        register_graphql_field('RootQuery', 'newsletterCurrentDayAds', $config);
    }

    /**
     * @param array $args
     * @return array|null
     * @throws Exception
     */
    protected function graphqlNewsletterCurrentDayAdsResolver(array $args): ?array
    {
        if (empty($args['period']) || empty($args['app'])) {
            return null;
        }

        return (new NewsletterAd($args['app']))->renderCurrentDayAds($args['period']);
    }
}
