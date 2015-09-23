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

use Velocity\Core\Bag;
use Velocity\Bundle\ApiBundle\Service\StorageService;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Service\GeneratorService;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class GenerateAction extends AbstractAction
{
    use ServiceAware\StorageServiceAwareTrait;
    use ServiceAware\GeneratorServiceAwareTrait;
    /**
     * @param GeneratorService $generatorService
     * @param StorageService   $storageService
     */
    public function __construct(GeneratorService $generatorService, StorageService $storageService)
    {
        $this->setGeneratorService($generatorService);
        $this->setStorageService($storageService);
    }
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("generate", description="execute a generator")
     */
    public function execute(Bag $params, Bag $context)
    {
        $generated = $this->getGeneratorService()->generate(
            $params->get('type'),
            $params->get('params', []),
            $params->get('options', [])
        );

        if ($params->has('store')) {
            $this->getStorageService()->save($params->get('store'), $generated);
        }

        $context->set($params->get('var'), $generated);
    }
}
