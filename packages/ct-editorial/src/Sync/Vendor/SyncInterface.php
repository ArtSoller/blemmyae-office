<?php

declare(strict_types=1);

namespace Cra\CtEditorial\Sync\Vendor;

use Exception;

interface SyncInterface
{
    /**
     * Sets up sync.
     *
     * Connect to a remote server, set temporary directory, etc.
     *
     * @param array $config
     *
     * @throws Exception
     */
    public function setup(array $config = []): void;

    /**
     * Executes sync operation.
     *
     * @throws Exception
     */
    public function execute(): void;
}
