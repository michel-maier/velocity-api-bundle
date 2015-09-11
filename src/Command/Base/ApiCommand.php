<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Command\Base;

use Symfony\Component\Console\Command\Command;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base API Commmand
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
abstract class ApiCommand extends Command
{
    use ServiceTrait;
}
