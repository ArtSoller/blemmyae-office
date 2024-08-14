<?php

namespace Cra\CtEditorial;

use Scm\Tools\PostByQueryResolver;

class EditorialByQueryResolver extends PostByQueryResolver
{
    /**
     * Adds actions to register graphql types and fields
     */
    public function __construct($postType)
    {
        $this->postType = $postType;
        $this->ctName = match ($postType) {
            ScAwardNominee::POST_TYPE => "ScAwardNominee",
            default => ucfirst($postType),
        };
        $this->ctDomain = 'ct-editorial';
        $this->registerFields();
    }
}
