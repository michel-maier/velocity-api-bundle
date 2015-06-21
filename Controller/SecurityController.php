<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Controller;

use Velocity\Core\Behaviour\Controller\Base\SecurityControllerTrait;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Security management controller.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class SecurityController extends Controller
{
    use SecurityControllerTrait;
}
