<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Storage;

use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Velocity\Bundle\ApiBundle\StorageInterface;
use Velocity\Core\Traits\ServiceTrait;
use Velocity\Core\Traits\FilesystemAwareTrait;

/**
 * File Storage
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class FileStorage implements StorageInterface
{
    use ServiceTrait;
    use FilesystemAwareTrait;
    /**
     * @param string     $root
     * @param Filesystem $filesystem
     */
    public function __construct($root, Filesystem $filesystem)
    {
        $this->setRoot($root);
        $this->setFilesystem($filesystem);
    }
    /**
     * @param string $root
     *
     * @return $this
     */
    public function setRoot($root)
    {
        return $this->setParameter('root', $root);
    }
    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getRoot()
    {
        return $this->getParameter('root');
    }
    /**
     * @param string $key
     * @param mixed  $value
     * @param array  $options
     *
     * @return $this
     */
    public function set($key, $value, $options = [])
    {
        $realPath       = $this->locate($key);
        $parentRealPath = dirname($realPath);

        $this->getFilesystem()->mkdir($parentRealPath);

        $this->getFilesystem()->dumpFile($realPath, $value);

        return $this;
    }
    /**
     * @param string $key
     * @param array  $options
     *
     * @return $this
     */
    public function clear($key, $options = [])
    {
        unset($options);

        $this->getFilesystem()->remove($this->locate($key));

        return $this;
    }
    /**
     * @param string $key
     * @param array  $options
     *
     * @return string
     *
     * @throws \Exception
     */
    public function get($key, $options = [])
    {
        $realPath = $this->locate($key);

        if (!$this->getFilesystem()->exists($realPath)) {
            throw $this->createNotFoundException("Unknown file '%s'", $$realPath);
        }

        return (new SplFileInfo($realPath, $this->getRoot(), $key))->getContents();
    }
    /**
     * @param string $key
     * @param array  $options
     *
     * @return bool
     */
    public function has($key, $options = [])
    {
        return !$this->getFilesystem()->exists($this->locate($key));
    }
    /**
     * @param string $relativePath
     *
     * @return string
     */
    protected function locate($relativePath)
    {
        return $this->getRoot().'/'.$relativePath;
    }
}
