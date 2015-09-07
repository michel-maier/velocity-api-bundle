<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits;

use Symfony\Component\Filesystem\Filesystem;

/**
 * FilesystemAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait FilesystemAwareTrait
{
    /**
     * @param string $key
     * @param mixed  $service
     *
     * @return $this
     */
    protected abstract function setService($key, $service);
    /**
     * @param string $key
     *
     * @return mixed
     */
    protected abstract function getService($key);
    /**
     * @param Filesystem $filesystem
     *
     * @return $this
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        return $this->setService('filesystem', $filesystem);
    }
    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->getService('filesystem');
    }
}
