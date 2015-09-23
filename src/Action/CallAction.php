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

use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Core\Bag;
use Velocity\Core\Traits\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractAction;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class CallAction extends AbstractAction
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
     * @param Bag $params
     *
     * @Velocity\Action("call", description="execute")
     */
    public function execute(Bag $params)
    {
        call_user_func_array(
            [
                $this->getContainer()->get($params->get('service')),
                $params->get('method'),
            ],
            $params->get('params', [])
        );
    }
}
