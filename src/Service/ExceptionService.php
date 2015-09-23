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

use Exception;
use Velocity\Core\Traits\ArrayizerTrait;
use Velocity\Core\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Symfony\Component\HttpFoundation\RequestStack;
use Velocity\Bundle\ApiBundle\Exception\FormValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Velocity\Bundle\ApiBundle\Exception\UnsupportedAccountTypeException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Exception Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ExceptionService
{
    use ServiceTrait;
    use ArrayizerTrait;
    use ServiceAware\FormServiceAwareTrait;
    /**
     * @param RequestStack $requestStack
     * @param FormService  $formService
     */
    public function __construct(RequestStack $requestStack, FormService $formService)
    {
        $this->setRequestStack($requestStack);
        $this->setFormService($formService);
    }
    /**
     * @param RequestStack $requestStack
     *
     * @return $this
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        return $this->setService('requestStack', $requestStack);
    }
    /**
     * @return RequestStack
     */
    public function getRequestStack()
    {
        return $this->getService('requestStack');
    }
    /**
     * @param Exception $e
     *
     * @return array
     */
    public function describe(Exception $e)
    {
        $code    = (100 < $e->getCode() && 600 > $e->getCode()) ? $e->getCode() : 500;
        $headers = [];

        if ($e instanceof HttpExceptionInterface) {
            $code = $e->getStatusCode();
            $headers += $e->getHeaders();
        }

        $data = [
            'code'    => $e->getCode() > 0 ? $e->getCode() : 500,
            'status'  => 'exception',
            'type'    => lcfirst(basename(str_replace('\\', '/', get_class($e)))),
            'message' => $e->getMessage(),
        ];

        switch (true) {
            case $e instanceof MethodNotAllowedHttpException:
                $code = 403;
                $data['code'] = 403;
                $data['message'] = 'Method not allowed on resource';
                break;
            case $e instanceof NotFoundHttpException:
                $code = 404;
                $data['code'] = 404;
                $data['message'] = 'Resource not found';
                break;
            case $e instanceof UnsupportedAccountTypeException:
                $code = 403;
                $data['code'] = 403;
                break;
            case $e instanceof AuthenticationException:
                $code = 401;
                $data['code'] = 401;
                break;
            case $e instanceof FormValidationException:
                $code = 412;
                $data['type'] = 'form';
                $data['errors'] = $this->getFormService()->getFormErrorsFromException($e);
                break;
        }

        if ($this->isDebug()) {
            $data['debug'] = $this->arrayize($e->getTrace());
        }

        return [
            'code'    => $code,
            'data'    => $data,
            'headers' => $headers,
        ];
    }
    /**
     * @return bool
     */
    protected function isDebug()
    {
        return
               $this->getRequestStack()->getMasterRequest()->headers->has('x-api-debug')
            || ($this->getRequestStack()->getMasterRequest()->query->has('debug')
            && 1 === intval($this->getRequestStack()->getMasterRequest()->query->get('debug')));
    }
}
