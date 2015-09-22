<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Action;

use Velocity\Bundle\ApiBundle\Bag;
use Velocity\Bundle\ApiBundle\Service\StorageService;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class StorageAction extends AbstractAction
{
    use ServiceAware\StorageServiceAwareTrait;
    /**
     * @param StorageService $storageService
     */
    public function __construct(StorageService $storageService)
    {
        $this->setStorageService($storageService);
    }
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("store", description="store object")
     */
    public function store(Bag $params, Bag $context)
    {
        unset($context);

        $this->getStorageService()->save(
            $params->get('file', $params->get('name', $params->get('key', $params->get('id')))),
            $params->get('content', $params->get('value', $params->get('data')))
        );
    }
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("retrieve", description="retrieve object")
     */
    public function retrieve(Bag $params, Bag $context)
    {
        $context->set(
            $params->get('var'),
            $this->getStorageService()->read(
                $params->get('file', $params->get('name', $params->get('key', $params->get('id'))))
            )
        );
    }
}
