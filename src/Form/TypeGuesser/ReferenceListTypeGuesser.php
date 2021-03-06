<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Form\TypeGuesser;

use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;
use Velocity\Core\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Velocity\Bundle\ApiBundle\Service\MetaDataService;

/**
 * ReferenceList Form Type Guesser
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ReferenceListTypeGuesser implements FormTypeGuesserInterface
{
    use ServiceTrait;
    use ServiceAware\MetaDataServiceAwareTrait;
    /**
     * @param MetaDataService $metaDataService
     */
    public function __construct(MetaDataService $metaDataService)
    {
        $this->setMetaDataService($metaDataService);
    }
    /**
     * @param string $class
     * @param string $property
     *
     * @return TypeGuess
     */
    public function guessType($class, $property)
    {
        if (!$this->getMetaDataService()->isModel($class)) {
            return null;
        }

        return $this->getMetaDataService()->getModelPropertyTypeGuess($class, $property);
    }
    /**
     * @param string $class
     * @param string $property
     *
     * @return ValueGuess
     */
    public function guessRequired($class, $property)
    {
        if (!$this->getMetaDataService()->isModel($class)) {
            return null;
        }

        return new ValueGuess(true, Guess::LOW_CONFIDENCE);
    }
    /**
     * @param string $class
     * @param string $property
     *
     * @return ValueGuess
     */
    public function guessMaxLength($class, $property)
    {
        if (!$this->getMetaDataService()->isModel($class)) {
            return null;
        }

        return new ValueGuess(null, Guess::LOW_CONFIDENCE);
    }
    /**
     * @param string $class
     * @param string $property
     *
     * @return ValueGuess
     */
    public function guessPattern($class, $property)
    {
        if (!$this->getMetaDataService()->isModel($class)) {
            return null;
        }

        return new ValueGuess(null, Guess::LOW_CONFIDENCE);
    }
}
