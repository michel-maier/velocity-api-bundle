<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\EventAction;

use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Traits\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class CallEventAction
{
    use ServiceTrait;
    use ContainerAwareTrait;
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }
    /**
     * @param Context $context
     *
     * @Velocity\EventAction("call", defaults={"params": {}})
     */
    public function execute(Context $context)
    {
        call_user_func_array(
            [
                $this->getContainer()->get($context->getVariable('service')),
                $context->getVariable('method'),
            ],
            $context->getVariable('params')
        );
    }
}
