<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Tests\Model;

use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use /** @noinspection PhpUnusedAliasInspection */ Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 * @Velocity\Model
 */
class ModelWithAnnotatedMethods
{
    /**
     * @Route("/test-route")
     *
     * @Velocity\Sdk
     */
    public function sdkMethod()
    {
    }
}
