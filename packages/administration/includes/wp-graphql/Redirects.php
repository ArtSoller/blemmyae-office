<?php

declare(strict_types=1);

namespace Scm\WP_GraphQL;

use Exception;
use Red_Item;

/**
 * Class Redirects.
 *
 * Adds "redirects" GraphQL field.
 */
class Redirects extends AbstractExtension
{
    /**
     * @inerhitDoc
     */
    protected function registerTypes(): void
    {
        register_graphql_object_type(
            'Redirect',
            [
                'description' => __('Redirect'),
                'fields' => [
                    'path' => ['type' => 'String'],
                    'code' => ['type' => 'Integer'],
                ],
            ]
        );
    }

    /**
     * @inerhitDoc
     * @throws Exception
     */
    protected function registerFields(): void
    {
        register_graphql_field(
            'RootQuery',
            'redirects',
            [
                'type' => ['list_of' => 'Redirect'],
                'description' => __('List of redirects associated with the provided slug.'),
                'args' => [
                    'path' => [
                        'type' => 'ID',
                    ],
                ],
                'resolve' => fn($source, $args) => $this->redirectsResolve($args),
            ]
        );
    }

    /**
     * Resolve callback for "redirects" GraphQL field.
     *
     * @param array $args
     *
     * @return array
     */
    private function redirectsResolve(array $args): array
    {
        $path = untrailingslashit(trim($args['path'] ?? ''));
        if (empty($path)) {
            return [];
        }

        $group = 'wp_redirection';

        if ($redirects = wp_cache_get($path, $group)) {
            return $redirects;
        }

        $redirects = $this->findManualRedirects($path);
        wp_cache_set($path, $redirects, $group, $this->getRedirectExpiration());

        return $redirects;
    }

    /**
     * Get time in seconds of a redirect expiration.
     * Default expiration time 90min. Min expiration time 60min. Max expiration time 150min.
     * Each next redirect will be cached based on the expiration time of the previous one.
     * For example, 130min -> 140min -> 150min -> 60min -> 70min.
     *
     * @return int
     */
    private function getRedirectExpiration(): int
    {
        $maxExpirationTime = 150;
        $minExpirationTime = 60;

        $optionName = 'wp_redirection_expiration_time';
        $expiration = get_option($optionName, 90);
        $expiration = $expiration > $maxExpirationTime ? $minExpirationTime : $expiration;
        update_option($optionName, $expiration + 10);

        return $expiration * 60;
    }

    /**
     * Find redirects manually set in WP admin settings.
     *
     * @param string $path
     *
     * @return array
     */
    private function findManualRedirects(string $path): array
    {
        global $wpdb;

        // todo: deprecate the "redirection" module, see port2287.ri.inc release instructions.
        $redirects = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, action_data FROM wp_redirection_items WHERE match_url = '$path' OR url = '%s'",
                $path
            ),
            ARRAY_A
        );

        // Return redirect with greater ID, if there are multiple redirects.
        if (sizeof($redirects) > 1) {
            usort($redirects, fn($a, $b) => (int)$a['id'] < (int)$b['id']);
        }

        $redirect = reset($redirects);

        return !empty($redirects) ? [['path' => $redirect['action_data'], 'code' => 301]] : [];
    }
}
