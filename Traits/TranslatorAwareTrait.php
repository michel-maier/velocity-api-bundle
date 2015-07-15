<?php

namespace Velocity\Bundle\ApiBundle\Traits;

use Symfony\Component\Translation\TranslatorInterface;

trait TranslatorAwareTrait
{
    /**
     * @param string $key
     * @param mixed  $service
     *
     * @return $this
     */
    protected abstract function setService($key, $service);
    /**
     * @param string $key
     *
     * @return mixed
     */
    protected abstract function getService($key);
    /**
     * @param TranslatorInterface $translator
     *
     * @return $this
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        return $this->setService('translator', $translator);
    }
    /**
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->getService('translator');
    }
    /**
     * @param string $pattern
     * @param array  $params
     *
     * @return string
     */
    protected function translate($pattern, $params = [])
    {
        return $this->getTranslator()->trans($pattern, $params);
    }
}