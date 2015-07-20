<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Service;

use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Velocity\Bundle\ApiBundle\Exception\FormValidationException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

class FormService
{
    use ServiceTrait;
    /**
     * @param FormFactoryInterface $formFactory
     * @param array                $typeNamespaces
     */
    public function __construct(FormFactoryInterface $formFactory, $typeNamespaces = [])
    {
        $this->setFormFactory($formFactory);
        $this->setTypeNamespaces($typeNamespaces);
    }
    /**
     * @param string $ns
     *
     * @return FormService
     */
    public function addTypeNamespace($ns)
    {
        return $this->setTypeNamespaces(array_merge($this->getTypeNamespaces() + [$ns]));
    }
    /**
     * @param FormFactoryInterface $formFactory
     *
     * @return $this
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        return $this->setService('formFactory', $formFactory);
    }
    /**
     * @return FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->getService('formFactory');
    }
    /**
     * @return array
     */
    public function getTypeNamespaces()
    {
        return $this->getParameter('typeNamespaces', []);
    }
    /**
     * @param array $typeNamespaces
     *
     * @return $this
     */
    public function setTypeNamespaces($typeNamespaces)
    {
        return $this->setParameter('typeNamespaces', $typeNamespaces);
    }
    /**
     * @param FormValidationException $exception
     *
     * @return array
     */
    public function getFormErrorsFromException(FormValidationException $exception)
    {
        $errors = [];

        $this->populateFormErrors($exception->getForm(), $errors);

        return $errors;
    }
    /**
     * @param FormInterface $form
     * @param array         $errors
     * @param null          $prefix
     *
     * @return $this
     */
    protected function populateFormErrors(FormInterface $form, &$errors, $prefix = null)
    {
        if (null !== $prefix) {
            $currentPrefix = ($prefix ? ($prefix . '.') : '') . $form->getName();
        } else {
            $currentPrefix = '';
        }

        if (null !== $prefix) {
            foreach ($form->getErrors() as $error) {
                if (false === isset($errors[$currentPrefix])) $errors[$currentPrefix] = array();
                if (method_exists($error, 'getMessage')) {
                    $errors[$currentPrefix][] = $error->getMessage();
                } else {
                    $errors[$currentPrefix][] = (string)$error;
                }
            }
        }

        foreach($form->all() as $child) {
            $this->populateFormErrors($child, $errors, $currentPrefix);
        }

        return $this;
    }
    /**
     * @param FormValidationException $exception
     *
     * @return string
     */
    public function getErrorsAsString(FormValidationException $exception)
    {
        $t = 'Data validation errors: ' . PHP_EOL;
        foreach($this->getFormErrorsFromException($exception) as $key => $errors) {
            $t .= sprintf('  %s:', !$key ? 'general' : $key) . PHP_EOL;
            foreach($errors as $error) {
                $t .= sprintf('    - %s', $error) . PHP_EOL;
            }
        }

        return $t;
    }
    /**
     * @param string $type
     * @param string $mode
     * @param array  $data
     * @param array  $cleanData
     * @param bool   $clearMissing
     * @param array  $options
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function validate($type, $mode, $data, $cleanData = [], $clearMissing = true, $options = [])
    {
        if (!$clearMissing) {
            foreach ($data as $k => $v) {
                if (null === $v) {
                    unset($data[$k]);
                }
            }
        }

        $form = $this->createForm($type, $mode, $cleanData, $options);

        $realData = $cleanData;

        if (is_array($data)) {
            $realData += $data;
        }

        $form->submit($realData, $clearMissing);

        $valid = $form->isValid();
        $data  = $form->getData();

        if (isset($options['convert']) && is_callable($options['convert'])) {
            try {
                $data = call_user_func_array($options['convert'], [$data, $form]);
            } catch (FormValidationException $e) {
                $valid = false;
                $_form = $form;
                $errors = $this->getFormErrorsFromException($e);
                foreach($errors as $name => $_errors) {
                    $__form = $_form;
                    if ($__form->has($name)) {
                        $__form = $__form->get($name);
                    }
                    foreach($_errors as $error) {
                        if (!$error instanceof FormError) {
                            $error = new FormError($error);
                        }
                        $__form->addError($error);
                    }
                }
            }
        }
        if (isset($options['enrich']) && is_callable($options['enrich'])) {
            try {
                $data = call_user_func_array($options['enrich'], [$data, $form]);
            } catch (FormValidationException $e) {
                $valid = false;
                $_form = $form;
                $errors = $this->getFormErrorsFromException($e);
                foreach($errors as $name => $_errors) {
                    $__form = $_form;
                    if ($__form->has($name)) {
                        $__form = $__form->get($name);
                    }
                    foreach($_errors as $error) {
                        if (!$error instanceof FormError) {
                            $error = new FormError($error);
                        }
                        $__form->addError($error);
                    }
                }
            }
        }

        if (!$valid) {
            throw new FormValidationException($form);
        }

        return $data;
    }
    /**
     * @param string $type
     * @param string $mode
     * @param array  $cleanData
     *
     * @return FormBuilderInterface
     */
    public function createBuilder($type, $mode = 'create', $cleanData = [])
    {
        $builder = null;

        $namespaces =
              $this->getTypeNamespaces()
            + ['AppBundle\\Form\\Type', str_replace('/', '\\', dirname(str_replace('\\', '/', __NAMESPACE__)) . '/Form/Type')]
        ;

        foreach($namespaces as $ns) {
            $formTypeClasses = [
                sprintf('%s\\%s%sType', $ns, str_replace(' ', '', ucwords(str_replace('.', ' ', $type))), ucfirst($mode)),
                sprintf('%s\\%sType', $ns, str_replace(' ', '', ucwords(str_replace('.', ' ', $type)))),
            ];

            $builder = null;

            foreach($formTypeClasses as $formTypeClass) {
                if (class_exists($formTypeClass, true)) {
                    $builder = $this->getFormFactory()->createBuilder(new $formTypeClass($cleanData), null, [
                        'csrf_protection' => false,
                        'validation_groups' => [$mode],
                    ]);
                    break 2;
                }
            }
        }

        if (null === $builder) {
            $this->throwException(500, "Missing form type '%s' (mode: %s)", $type, $mode);
        }

        return $builder;
    }
    /**
     * @param string $type
     * @param string $mode
     * @param array  $cleanData
     * @param array  $options
     *
     * @return FormInterface
     */
    public function createForm($type, $mode = 'create', $cleanData = [], $options = [])
    {
        $builder = $this->createBuilder($type, $mode, $cleanData);

        if (!is_array($options)) {
            $options = [];
        }

        if (!isset($options['listeners']) || !is_array($options['listeners'])) {
            $options['listeners'] = [];
        }

        foreach($options['listeners'] as $eventName => $listeners) {
            foreach($listeners as $listener) {
                $builder->addEventListener($eventName, $listener);
            }
        }

        return $builder->getForm();
    }
}