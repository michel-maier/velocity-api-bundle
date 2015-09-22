<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\ServiceAware;

use Velocity\Bundle\ApiBundle\Service\DocumentBuilderService;

/**
 * DocumentBuilderServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait DocumentBuilderServiceAwareTrait
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
     * @return DocumentBuilderService
     */
    public function getDocumentBuilderService()
    {
        return $this->getService('documentBuilder');
    }
    /**
     * @param DocumentBuilderService $service
     *
     * @return $this
     */
    public function setDocumentBuilderService(DocumentBuilderService $service)
    {
        return $this->setService('documentBuilder', $service);
    }
}
