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

use Psr\Log\LoggerInterface;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Traits\LoggerAwareTrait;
use Velocity\Bundle\ApiBundle\EventAction\Base\AbstractEventAction;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class LogEventAction extends AbstractEventAction
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
     * @Velocity\EventAction("log", ignoreOnException=true)
     */
    public function log()
    {
        $context = $this->getContext();

        $this->getLogger()->log(
            strtoupper($context->getVariable('level', 'debug')),
            $context->getVariable('message', $this->buildMessage())
        );
    }
    /**
     * @return string
     */
    protected function buildMessage()
    {
        return 'event ['.$this->getContext()->getCurrentEventName().']';
    }
}
