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

use Symfony\Component\HttpFoundation\Request;
use Velocity\Core\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\PaymentProviderInterface;

/**
 * PaymentProvider Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class PaymentProviderService
{
    use ServiceTrait;
    use ServiceAware\ExpressionServiceAwareTrait;
    /**
     * List of payment providers.
     *
     * @var PaymentProviderInterface[]
     */
    protected $providers;
    /**
     * @var array
     */
    protected $rules;
    /**
     * Construct a new service.
     *
     * @param ExpressionService          $expressionService
     * @param PaymentProviderInterface[] $providers
     * @param array                      $rules
     */
    public function __construct(ExpressionService $expressionService, $providers = [], $rules = [])
    {
        $this->setExpressionService($expressionService);

        foreach ($providers as $name => $provider) {
            $this->addProvider($provider, $name);
        }

        foreach ($rules as $condition => $providerName) {
            $this->addRule($condition, $providerName);
        }
    }
    /**
     * Return the list of registered providers.
     *
     * @return PaymentProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }
    /**
     * Add a new provider for the specified name (replace if exist).
     *
     * @param PaymentProviderInterface $provider
     * @param string                   $name
     *
     * @return $this
     */
    public function addProvider(PaymentProviderInterface $provider, $name)
    {
        $this->providers[$name] = $provider;

        return $this;
    }
    /**
     * Add a new rule
     *
     * @param string $condition
     * @param string $providerName
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function addRule($condition, $providerName)
    {
        $this->rules[] = ['condition' => $condition, 'provider' => $providerName];

        return $this;
    }
    /**
     * Return the provider registered for the specified name.
     *
     * @param string $name
     *
     * @return PaymentProviderInterface
     *
     * @throws \Exception if no provider registered for this name
     */
    public function getProviderByName($name)
    {
        if (!isset($this->providers[$name])) {
            throw $this->createRequiredException(
                "No payment provider registered for name '%s'",
                $name
            );
        }

        return $this->providers[$name];
    }
    /**
     * @param array $data
     *
     * @return string
     *
     * @throws \Exception
     */
    public function selectProvider($data = [])
    {
        foreach ($this->rules as $rule) {
            if ($this->isValidCondition($rule['condition'], $data)) {
                return $rule['provider'];
            }
        }

        throw $this->createRequiredException("No payment provider available");
    }
    /**
     * @param string $condition
     * @param array  $data
     *
     * @return bool
     */
    public function isValidCondition($condition, $data = [])
    {
        return (bool) $this->getExpressionService()->evaluate($condition, $data);
    }
    /**
     * @param string  $provider
     * @param string  $callback
     * @param Request $request
     *
     * @return array
     *
     * @throws \Exception
     */
    public function parseCallbackRequest($provider, $callback, Request $request)
    {
        return $this->getProviderByName($provider)->parseCallbackRequest($callback, $request);
    }
}
