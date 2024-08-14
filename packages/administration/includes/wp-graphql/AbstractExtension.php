<?php

declare(strict_types=1);

namespace Scm\WP_GraphQL;

abstract class AbstractExtension
{
    /**
     * Register GraphQL types.
     */
    abstract protected function registerTypes(): void;

    /**
     * Register GraphQL fields.
     */
    abstract protected function registerFields(): void;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->registerTypes();
        $this->registerFields();
    }
}
