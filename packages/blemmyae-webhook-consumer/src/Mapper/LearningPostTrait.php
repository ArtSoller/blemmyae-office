<?php

/**
 * @author  Konstantin Gusev <konstantin.gusev@cyberriskalliance.com>
 * @license proprietary
 */

namespace Cra\WebhookConsumer\Mapper;

use Cra\CtLearning\LearningCT;

/**
 * Trait to be used by mappers for learning content type.
 */
trait LearningPostTrait
{
    use PostTrait;

    /**
     * @inheritDoc
     */
    public function wpEntityBundle(): string
    {
        return LearningCT::POST_TYPE;
    }

    /**
     * Updates Location field for 'Learning' or 'Session' post type post.
     *
     * @param string $fieldKey ACF 'Location' field key.
     * @param null|object{
     *     'name'?: ?string,
     *     'company'?: ?string,
     *     'line_1'?: ?string,
     *     'line_2'?: ?string,
     *     'line_3'?: ?string,
     *     'city'?: ?string,
     *     'state'?: ?string,
     *     'zip'?: ?string,
     *     'country'?: null|object|array{
     *         'code'?: ?string,
     *         'name'?: ?string,
     *         'continent'?: ?string,
     *         'zipcode_required'?: ?bool,
     *         'currency_code'?: ?string,
     *         'tax_name'?: ?string,
     *     },
     *     'country_code'?: ?string,
     *     'phone'?: ?string,
     *     'website'?: ?string,
     *     'latitude'?: ?string,
     *     'longitude'?: ?string,
     * } $location
     * @param string|null $virtualLocation
     *
     * @return void
     */
    protected function updateLearningLocationField(
        string $fieldKey,
        ?object $location,
        ?string $virtualLocation
    ): void {
        $country = !empty($location->country) ? (object)$location->country : null;
        $this->updateAcfField(
            $fieldKey,
            [
                'url' => $virtualLocation ?
                    [
                        'title' => 'Virtual location',
                        'url' => $virtualLocation,
                        'target' => '_blank',
                    ] : null,
                'phone' => $location->phone ?? null,
                'address' => isset($location) ?
                    [
                        'name' => $location->name,
                        'street' => trim(
                            sprintf(
                                '%s %s %s',
                                $location->line_1 ?? null,
                                $location->line_2 ?? null,
                                $location->line_3 ?? null
                            )
                        ),
                        'locality' => (isset($location->latitude) && isset($location->longitude)) ?
                            "$location->latitude, $location->longitude" :
                            null,
                        'postal' => $location->zip ?? null,
                        'region' => $location->state ?? null,
                        'country' => $country->name ?? null,
                    ] : null,
                // @todo add google map when its support will be added.
                'map' => null,
            ]
        );
    }
}
