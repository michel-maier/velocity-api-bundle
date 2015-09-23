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
use Velocity\Bundle\ApiBundle\DocumentInterface;
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

        if ($data instanceof DocumentInterface) {
            $content = $data->getContent();
            $contentType = $data->getContentType();
        } else {
            $contentType = $format;
            $content     = $this->getFormatterService()->format('application/json', $data, $options);
            if ($format !== 'application/json' && $format === 'text/json') {
                $content = $this->getFormatterService()->format($format, json_decode($content, true), $options);
            }
            if ($content instanceof DocumentInterface) {
                $contentType = $content->getContentType();
                $content = $content->getContent();
            }
        }

        return new Response($content, $code, ['Content-Type' => $contentType] + $headers);
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
            $this->filterValidErrorContentTypes($acceptableContentTypes),
            $info['data'],
            (100 < $info['code'] && 599 > $info['code']) ? $info['code'] : 500,
            $info['headers']
        );
    }
    /**
     * @param array $contentTypes
     *
     * @return array
     */
    protected function filterValidErrorContentTypes($contentTypes)
    {
        $valids = [
            'text/json' => true,
            'application/json' => true,
            'text/xml' => true,
            'application/xml' => true,
            'text/plain' => true,
            'text/yaml' => true,
            'application/x-yaml' => true,
        ];

        foreach ($contentTypes as $k => $contentType) {
            if (!is_array($contentType)) {
                $contentType = ['value' => $contentType];
            }

            if (!isset($valids[$contentType['value']])) {
                unset($contentTypes[$k]);
            }
        }

        return $contentTypes;
    }
}
