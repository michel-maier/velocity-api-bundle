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

use Velocity\Bundle\ApiBundle\Service\MetaDataService;

/**
 * MetaDataServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait MetaDataServiceAwareTrait
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
     * @param MetaDataService $service
     *
     * @return $this
     */
    public function setMetaDataService(MetaDataService $service)
    {
        return $this->setService('metaData', $service);
    }
    /**
     * @return MetaDataService
     */
    public function getMetaDataService()
    {
        return $this->getService('metaData');
    }
}