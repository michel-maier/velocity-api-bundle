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

use Psr\Log\LoggerInterface;
use Velocity\Bundle\ApiBundle\Bag;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Traits\LoggerAwareTrait;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractAction;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class LogAction extends AbstractAction
{
    use LoggerAwareTrait;
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("log", ignoreOnException=true, description="log")
     */
    public function log(Bag $params, Bag $context)
    {
        $this->getLogger()->log(
            strtoupper($params->get('level', 'debug')),
            $context->get('message', 'event ['.$context->get('eventName', '?').']')
        );
    }
}
