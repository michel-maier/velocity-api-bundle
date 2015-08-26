<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Twig;

use Velocity\Core\Twig\Base\TwigBaseExtension;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ApiExtension extends TwigBaseExtension
{
    /**
     * @var array
     */
    protected $globals;
    /**
     * @param array $variables
     */
    public function __construct(array $variables = [])
    {
        $this->globals = $variables;
    }
    /**
     * @return array
     */
    public function getGlobals()
    {
        return [
            'velocity' => $this->globals,
        ];
    }
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('base64_encode', [$this, 'getBase64EncodedString']),
        ];
    }
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [];
    }
    /**
     * @return array
     */
    public function getTokenParsers()
    {
        return [];
    }
    /**
     * @param $string
     *
     * @return string
     */
    public function getBase64EncodedString($string)
    {
        return base64_encode($string);
    }
    /**
     * @return string
     */
    public function getName()
    {
        return 'velocity_api';
    }
}
