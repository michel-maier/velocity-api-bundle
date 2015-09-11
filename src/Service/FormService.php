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

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Exception\FormValidationException;

/**
 * Form Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class FormService
{
    use ServiceTrait;
    /**
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->setFormFactory($formFactory);
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
     * @param FormValidationException $exception
     *
     * @return string
     */
    public function getErrorsAsString(FormValidationException $exception)
    {
        $t = 'Data validation errors: '.PHP_EOL;
        foreach ($this->getFormErrorsFromException($exception) as $key => $errors) {
            $t .= sprintf('  %s:', !$key ? 'general' : $key).PHP_EOL;
            foreach ($errors as $error) {
                $t .= sprintf('    - %s', $error).PHP_EOL;
            }
        }

        return $t;
    }
    /**
     * @param string $type
     * @param string $mode
     * @param array  $data
     * @param array  $options
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function validate($type, $mode, $data, $options = [])
    {
        $options += ['clearMissing' => true, 'cleanData' => []];

        $clearMissing = $options['clearMissing'];
        $cleanData    = $options['cleanData'];

        if (!$clearMissing) {
            foreach ($data as $k => $v) {
                if (null === $v) {
                    unset($data[$k]);
                }
            }
        }

        $form = $this->createForm($type, $mode, $cleanData, $options);

        $form->submit($cleanData + (is_array($data) ? $data : []), $clearMissing);

        if (!$form->isValid()) {
            throw new FormValidationException($form);
        }

        return $form->getData();
    }
    /**
     * @param string $type
     * @param string $mode
     * @param array  $cleanData
     *
     * @return FormBuilderInterface
     *
     * @throws \Exception
     */
    public function createBuilder($type, $mode = 'create', $cleanData = [])
    {
        $builder = null;

        $namespaces =
            ['AppBundle\\Form\\Type', str_replace('/', '\\', dirname(str_replace('\\', '/', __NAMESPACE__)).'/Form/Type')]
        ;

        foreach ($namespaces as $ns) {
            $formTypeClasses = [
                sprintf('%s\\%s%sType', $ns, str_replace(' ', '', ucwords(str_replace('.', ' ', $type))), ucfirst($mode)),
                sprintf('%s\\%sType', $ns, str_replace(' ', '', ucwords(str_replace('.', ' ', $type)))),
            ];

            $builder = null;

            foreach ($formTypeClasses as $formTypeClass) {
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
            throw $this->createRequiredException("Missing form type '%s' (mode: %s)", $type, $mode);
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
        unset($options);

        return $this->createBuilder($type, $mode, $cleanData)->getForm();
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
            $currentPrefix = ($prefix ? ($prefix.'.') : '').$form->getName();
        } else {
            $currentPrefix = '';
        }

        if (null !== $prefix) {
            foreach ($form->getErrors() as $error) {
                if (false === isset($errors[$currentPrefix])) {
                    $errors[$currentPrefix] = array();
                }
                if (method_exists($error, 'getMessage')) {
                    $errors[$currentPrefix][] = $error->getMessage();
                } else {
                    $errors[$currentPrefix][] = (string) $error;
                }
            }
        }

        foreach ($form->all() as $child) {
            $this->populateFormErrors($child, $errors, $currentPrefix);
        }

        return $this;
    }
}
