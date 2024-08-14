<?php

/**
 * @license proprietary
 * @link https://cra.myjetbrains.com/youtrack/issue/BLEM-621
 */

declare(strict_types=1);

use Cra\CtPeople\PeopleCT;
use Scm\Tools\Logger;

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class AuthorRedirects
{
    private const PERSON_TAX_TYPE_AUTHOR = 'author';

    /**
     * Create author redirects '/slug' -> '/people_type/slug'.
     *
     * @return void
     * @throws Exception
     */
    public function create(): void
    {
        $query = new WP_Query([
            'nopaging' => true,
            'post_type' => 'people',
            'tax_query' => [
                [
                    'taxonomy' => PeopleCT::TAXONOMY__PEOPLE_TYPE,
                    'field' => 'slug',
                    'terms' => self::PERSON_TAX_TYPE_AUTHOR,
                ],
            ],
        ]);
        /** @var WP_Post $post */
        $countAll = $count = count($query->posts);
        foreach ($query->posts as $post) {
            $link = get_permalink($post);
            $this->debug("[$countAll/$count] $post->ID: $post->post_title");
            $this->createRedirect($link);
            $count--;
        }
    }

    /**
     * Create redirect for 'People' content.
     *
     * @param string $sourceUrl
     *
     * @throws Exception
     */
    private function createRedirect(string $sourceUrl): void
    {
        $sourceUri = untrailingslashit(wp_parse_url($sourceUrl, PHP_URL_PATH));
        $uriParts = explode('/', $sourceUrl);
        $slug = array_pop($uriParts);
        /** @phpstan-ignore-next-line */
        $result = Red_Item::create([
            'url' => $slug,
            'action_data' => ['url' => $sourceUri],
            'group_id' => 1,
            'match_type' => 'url',
            'action_type' => 'url',
            'action_code' => 301,
        ]);
        if ($result instanceof WP_Error) {
            throw new Exception($result->get_error_message());
        }
        $this->success("Created redirect from /$slug to $sourceUri");
    }

    /**
     * Log error message.
     *
     * @param string $message
     *
     * @return void
     */
    private function error(string $message): void
    {
        Logger::log($message, 'error');
    }

    /**
     * Log success message.
     *
     * @param string $message
     *
     * @return void
     */
    private function success(string $message): void
    {
        Logger::log($message, 'success');
    }

    /**
     * Log success message.
     *
     * @param string $message
     *
     * @return void
     */
    private function debug(string $message): void
    {
        Logger::log($message, 'debug');
    }
}

/**
 * Create author redirects '/slug' -> '/people_type/slug', eta:
 * real    0m34.458s
 * user    0m4.388s
 * sys     0m2.146s
 *
 * @return void
 * @throws Exception
 */
function blem621_blemmyae_deployment_ri_001(): void
{
    (new AuthorRedirects())->create();
}
