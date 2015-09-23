<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Formatter;

use Symfony\Component\Yaml\Yaml;
use Velocity\Core\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;

/**
 * Yaml Formatter Action.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class YamlFormatter
{
    use ServiceTrait;
    /**
     * @param mixed $data
     * @param array $options
     *
     * @return string
     *
     * @Velocity\Formatter("application/x-yaml")
     * @Velocity\Formatter("text/yaml")
     */
    public function format($data, array $options = [])
    {
        unset($options);

        return Yaml::dump($data);
    }
}
