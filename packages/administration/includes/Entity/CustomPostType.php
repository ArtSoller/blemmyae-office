<?php

/**
 * CustomPostType class, defines custom post type.
 *
 * @author Alexander Kucherov <avdkucherov@gmail.com>
 */

declare(strict_types=1);

namespace Scm\Entity;

/**
 * CustomPostType class.
 */
class CustomPostType
{
    /**
     * @var string Plugin dir path.
     *
     * @access protected
     */
    protected string $pluginDirPath;

    /**
     * CustomPostType constructor.
     * @param string $pluginDirPath Plugin dir path.
     */
    public function __construct(string $pluginDirPath = '')
    {
        $this->pluginDirPath = $pluginDirPath ?: dirname(__DIR__);
    }
}
