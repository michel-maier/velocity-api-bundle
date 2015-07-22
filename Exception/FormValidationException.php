<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Exception;

use Symfony\Component\Form\FormInterface;

/**
 * FormValidationException.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class FormValidationException extends \RuntimeException
{
    /**
     * @var FormInterface
     */
    protected $form;
    /**
     * Construct the exception
     *
     * @param FormInterface $form
     */
    public function __construct(FormInterface $form)
    {
        parent::__construct('Malformed data', 412);

        $this->form = $form;
    }
    /**
     * Return the underlying form.
     *
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}