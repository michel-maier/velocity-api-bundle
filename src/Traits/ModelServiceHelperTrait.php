<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits;

use Velocity\Core\Traits\ServiceTrait;
use Velocity\Core\Traits\LoggerAwareTrait;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;

/**
 * VolatileModelService trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait ModelServiceHelperTrait
{
    use ServiceTrait;
    use LoggerAwareTrait;
    use ServiceAware\FormServiceAwareTrait;
    use ServiceAware\MetaDataServiceAwareTrait;
    use ServiceAware\WorkflowServiceAwareTrait;
    use ServiceAware\BusinessRuleServiceAwareTrait;
    /**
     * @return int|null
     */
    public abstract function getExpectedTypeCount();
    /**
     * @param array $types
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setTypes(array $types)
    {
        $expectedTypeCount = $this->getExpectedTypeCount();

        if (null !== $expectedTypeCount && $expectedTypeCount !== count($types)) {
            throw $this->createUnexpectedException(
                "Model service must have exactly %d types (found: %d)",
                $expectedTypeCount,
                count($types)
            );
        }

        return $this->setParameter('types', $types);
    }
    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->getParameter('types');
    }
    /**
     * @param string $separator
     *
     * @return string
     */
    public function getFullType($separator = '.')
    {
        return join($separator, $this->getTypes());
    }
    /**
     * Test if specified document event has registered event listeners.
     *
     * @param string $event
     *
     * @return bool
     */
    protected function observed($event)
    {
        return $this->hasListeners($this->buildEventName($event));
    }
    /**
     * Build the full event name.
     *
     * @param string $event
     *
     * @return string
     */
    protected function buildEventName($event)
    {
        return join('.', $this->getTypes()).'.'.$event;
    }
    /**
     * @param mixed $bulkData
     * @param array $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    protected function checkBulkData($bulkData, $options = [])
    {
        if (!is_array($bulkData)) {
            throw $this->createRequiredException('Missing bulk data');
        }

        if (!count($bulkData)) {
            throw $this->createRequiredException('No data to process');
        }

        unset($options);

        return $this;
    }
    /**
     * Return the underlying model class.
     *
     * @param string $alias
     *
     * @return string
     */
    protected function getModelClass($alias = null)
    {
        if (null !== $alias) {
            if ('.' === substr($alias, 0, 1)) {
                return $this->getModelClass().'\\'.substr($alias, 1);
            }

            return $alias;
        }

        return $this->getMetaDataService()->getModelClassForId(join('.', $this->getTypes()));
    }
    /**
     * Return a new instance of the model.
     *
     * @param array $options
     *
     * @return mixed
     */
    protected function createModelInstance($options = [])
    {
        if (isset($options['model']) && !is_bool($options['model'])) {
            if (is_object($options['model'])) {
                return $options['model'];
            }
            $class = $this->getModelClass($options['model']);
        } else {
            $class = $this->getModelClass();
        }

        return new $class();
    }
    /**
     * @param array $values
     * @param array $options
     *
     * @return array
     */
    protected function buildTypeVars($values, $options = [])
    {
        $vars = [];

        $options += ['suffix' => 'Id'];

        foreach ($this->getTypes() as $type) {
            if (!count($values)) {
                $value = null;
            } else {
                $value = array_shift($values);
            }
            $vars[$type.$options['suffix']] = $value;
        }

        return $vars;
    }
    /**
     * @param string $mode
     * @param array  $data
     * @param array  $options
     *
     * @return mixed
     */
    protected function validateData(array $data = [], $mode = 'create', array $options = [])
    {
        return $this->getFormService()->validate($this->getFullType(), $mode, $data, $options);
    }
    /**
     * @param mixed $model
     * @param array $options
     *
     * @return mixed
     */
    protected function refreshModel($model, array $options = [])
    {
        return $this->getMetaDataService()->refresh($model, $options);
    }
    /**
     * @param mixed $model
     * @param array $options
     *
     * @return mixed
     */
    protected function cleanModel($model, array $options = [])
    {
        return $this->getMetaDataService()->clean($model, $options);
    }
    /**
     * Convert provided model (object) to an array.
     *
     * @param mixed $model
     * @param array $options
     *
     * @return array
     */
    protected function convertToArray($model, array $options = [])
    {
        return $this->getMetaDataService()->convertObjectToArray($model, $options);
    }
    /**
     * Convert provided data (array) to a model.
     *
     * @param array $data
     * @param array $options
     *
     * @return mixed
     */
    protected function convertToModel(array $data, $options = [])
    {
        return $this->getMetaDataService()->populateObject($this->createModelInstance($options), $data, $options);
    }
    /**
     * Convert provided data (mixed) to a model property.
     *
     * @param array $data
     * @param array $propertyName
     * @param array $options
     *
     * @return mixed
     */
    protected function convertToModelProperty($data, $propertyName, $options = [])
    {
        return $this->getMetaDataService()->populateObjectProperty($this->createModelInstance($options), $data, $propertyName, $options);
    }
    /**
     * @return string
     */
    protected function getModelName()
    {
        return join('.', $this->getTypes());
    }
}
