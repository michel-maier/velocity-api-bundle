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

use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Traits\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Velocity\Bundle\ApiBundle\EventAction\Base\AbstractEventAction;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class CallEventAction extends AbstractEventAction
{
    use ContainerAwareTrait;
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }
    /**
     * @Velocity\EventAction("call")
     */
    public function execute()
    {
        call_user_func_array(
            [
                $this->getContainer()->get($this->getContext()->getRequiredVariable('service')),
                $this->getContext()->getRequiredVariable('method'),
            ],
            $this->getContext()->getVariable('params', [])
        );
    }
}
