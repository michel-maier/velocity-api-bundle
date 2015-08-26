<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class AbstractContainerAwareTestCase extends KernelTestCase
{
    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        if (null === static::$kernel) {
            static::bootKernel([]);
        }

        return static::$kernel->getContainer();
    }
    /**
     * @param string $id
     *
     * @return object
     */
    protected function get($id)
    {
        return $this->getContainer()->get($id);
    }
    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function getParameter($name)
    {
        return $this->getContainer()->getParameter($name);
    }
    /**
     * @param string      $id
     * @param null|string $failMessage
     */
    protected function assertContainerHasService($id, $failMessage = null)
    {
        $this->assertTrue(
            $this->getContainer()->has($id),
            $failMessage
        );
    }
    /**
     * @param string      $name
     * @param null|string $failMessage
     */
    protected function assertContainerHasParameter($name, $failMessage = null)
    {
        $this->assertTrue(
            $this->getContainer()->hasParameter($name),
            $failMessage
        );
    }
    /**
     * @param string      $name
     * @param mixed       $value
     * @param null|string $failMessage
     */
    protected function assertContainerParameterEquals($name, $value, $failMessage = null)
    {
        $this->assertEquals(
            $value,
            $this->getParameter($name),
            $failMessage
        );
    }
}
