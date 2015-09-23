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

use Symfony\Component\HttpFoundation\Response;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;

/**
 * Response Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ResponseService
{
    use ServiceTrait;
    use ServiceAware\FormatterServiceAwareTrait;
    use ServiceAware\ExceptionServiceAwareTrait;
    /**
     * @param FormatterService $formatterService
     * @param ExceptionService $exceptionService
     */
    public function __construct(FormatterService $formatterService, ExceptionService $exceptionService)
    {
        $this->setFormatterService($formatterService);
        $this->setExceptionService($exceptionService);
    }
    /**
     * @param array  $acceptableContentTypes
     * @param mixed  $data
     * @param int    $code
     * @param array  $headers
     * @param array  $options
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function create($acceptableContentTypes, $data = null, $code = 200, array $headers = [], array $options = [])
    {
        $format = 'application/json';

        foreach ($acceptableContentTypes as $acceptableContentType) {
            $type = is_array($acceptableContentType) ? $acceptableContentType['value'] : (string) $acceptableContentType;
            if ($this->getFormatterService()->has($type)) {
                $format = $type;
                break;
            }
        }

        return new Response(
            $this->getFormatterService()->format($format, $data, $options),
            $code,
            ['Content-Type' => $format] + $headers
        );
    }
    /**
     * @param array      $acceptableContentTypes
     * @param \Exception $exception
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function createFromException($acceptableContentTypes, \Exception $exception)
    {
        $info = $this->getExceptionService()->describe($exception);

        return $this->create(
            $acceptableContentTypes,
            $info['data'],
            (100 < $info['code'] && 599 > $info['code']) ? $info['code'] : 500,
            $info['headers']
        );
    }
}
