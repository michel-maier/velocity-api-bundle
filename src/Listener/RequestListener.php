<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Listener;

use Velocity\Core\Traits\ServiceTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Request Listener.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class RequestListener
{
    use ServiceTrait;
    /**
     * @param array  $allowedLocales
     * @param string $defaultLocale
     */
    public function __construct(array $allowedLocales, $defaultLocale)
    {
        $this->allowLocales($allowedLocales);
        $this->setDefaultLocale($defaultLocale);
    }
    /**
     * @param string $defaultLocale
     *
     * @return $this
     */
    public function setDefaultLocale($defaultLocale)
    {
        return $this->setParameter('defaultLocale', $defaultLocale);
    }
    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getDefaultLocale()
    {
        return $this->getParameter('defaultLocale');
    }
    /**
     * @param array $locales
     *
     * @return $this
     */
    public function allowLocales(array $locales)
    {
        foreach ($locales as $locale) {
            $this->pushArrayParameterKeyItem('allowedLocales', strtolower($locale), true);
        }

        return $this;
    }
    /**
     * @param string $locale
     *
     * @return bool
     */
    public function isAllowedLocale($locale)
    {
        return $this->hasArrayParameterKey('allowedLocales', strtolower($locale));
    }
    /**
     * Kernel request event callback.
     *
     * @param GetResponseEvent $event
     *
     * @return void
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($event->isMasterRequest()) {
            $request->setLocale($this->getDefaultLocale());

            foreach ($request->getLanguages() as $locale) {
                if ($this->isAllowedLocale($locale)) {
                    $request->setLocale($locale);
                    break;
                }
            }
        }

        if ('json' !== $request->getContentType()) {
            return;
        }

        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $event->setResponse(new JsonResponse(
                [
                    'code'    => 412,
                    'status'  => 'exception',
                    'message' => 'Malformed data (json syntax)',
                ],
                412
            ));
        } elseif (is_array($data)) {
            $request->request->replace($data);
        }
    }
}
