<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Service;

use Velocity\Bundle\ApiBundle\DocumentInterface;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Core\Traits\ServiceTrait;

/**
 * Document Builder Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class DocumentBuilderService
{
    use ServiceTrait;
    use ServiceAware\CallableServiceAwareTrait;
    /**
     * @param CallableService $callableService
     */
    public function __construct(CallableService $callableService)
    {
        $this->setCallableService($callableService);
    }
    /**
     * Register a document builder for the specified name (replace if exist).
     *
     * @param string   $name
     * @param callable $callable
     * @param array    $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function register($name, $callable, array $options = [])
    {
        $this->getCallableService()->registerByType('documentBuilder', $name, $callable, $options);

        return $this;
    }
    /**
     * Return the document builder registered for the specified name.
     *
     * @param string $name
     *
     * @return callable
     *
     * @throws \Exception if no document builder registered for this name
     */
    public function get($name)
    {
        return $this->getCallableService()->getByType('documentBuilder', $name);
    }
    /**
     * @param string $name
     * @param array  $data
     * @param array  $metas
     * @param array  $options
     *
     * @return DocumentInterface
     *
     * @throws \Exception
     */
    public function build($name, array $data = [], array $metas = [], array $options = [])
    {
        return $this->getCallableService()->executeByType('documentBuilder', $name, [$data, $metas, $options]);
    }
}
