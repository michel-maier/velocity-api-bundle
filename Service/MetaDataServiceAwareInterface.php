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

interface MetaDataServiceAwareInterface
{
    /**
     * @param MetaDataService $service
     *
     * @return $this
     */
    public function setMetaDataService(MetaDataService $service);
    /**
     * @return MetaDataService
     */
    public function getMetaDataService();
}