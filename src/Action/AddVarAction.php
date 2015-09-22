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
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractAction;
use Velocity\Bundle\ApiBundle\Traits\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class AddVarAction extends AbstractAction
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
     * @param Bag $context
     *
     * @Velocity\Action("add_var", description="add a variable to context")
     */
    public function execute(Bag $params, Bag $context)
    {
        $context->set(
            $params->get('name'),
            call_user_func_array(
                [
                    $this->getContainer()->get($params->get('service')),
                    $params->get('method'),
                ],
                $params->get('params', [])
            )
        );
    }
}
