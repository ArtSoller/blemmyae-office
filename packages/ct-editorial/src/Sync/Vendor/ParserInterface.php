<?php

declare(strict_types=1);

namespace Cra\CtEditorial\Sync\Vendor;

use Cra\CtEditorial\Entity\Editorial;
use Exception;

interface ParserInterface
{
    /**
     * Parse Vendor item into Editorial.
     *
     * @param string $input
     *
     * @return Editorial
     * @throws Exception
     */
    public function parse(string $input): Editorial;
}
