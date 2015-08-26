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
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\LoggerAwareTrait;
use Velocity\Bundle\ApiBundle\Security\ApiUserProvider;
use Velocity\Bundle\ApiBundle\Traits\ClientProviderAwareTrait;

/**
 * Request Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class RequestService
{
    use ServiceTrait;
    use LoggerAwareTrait;
    use ClientProviderAwareTrait;
    /**
     * @var string
     */
    protected $clientHeaderKey = 'X-Api-Client';
    /**
     * @var string
     */
    protected $userHeaderKey = 'X-Api-User';
    /**
     * @var string
     */
    protected $sudoHeaderKey = 'X-Api-Sudo';
    /**
     * @var string
     */
    protected $clientSecret = 'thisIsTheSuperLongSecret@Api2014!';
    /**
     * @var string
     */
    protected $userSecret = 'thisIsAnOtherTheSuperLongSecret@Api2014!';
    /**
     * @var string
     */
    protected $clientTokenCreationUriPattern = ',^.*/client\-tokens$,';
    /**
     * @var string
     */
    protected $userTokenCreationUriPattern = ',^.*/user\-tokens$,';
    /**
     * @var \Closure
     */
    protected $clientTokenCreationFunction;
    /**
     * @var \Closure
     */
    protected $userTokenCreationFunction;
    /**
     * @var \Closure
     */
    protected $clientHeaderParsingFunction;
    /**
     * @var \Closure
     */
    protected $userHeaderParsingFunction;
    /**
     * @var \Closure
     */
    protected $sudoHeaderParsingFunction;
    /**
     * @param ApiUserProvider $provider
     *
     * @return $this
     */
    public function setUserProvider(ApiUserProvider $provider)
    {
        return $this->setService('userProvider', $provider);
    }
    /**
     * @return ApiUserProvider
     */
    public function getUserProvider()
    {
        return $this->getService('userProvider');
    }
    /**
     * @param ApiUserProvider $userProvider
     * @param string          $clientSecret
     * @param string          $userSecret
     */
    public function __construct(ApiUserProvider $userProvider, $clientSecret = null, $userSecret = null)
    {
        $this->setClientTokenCreationFunction(function ($id, $expire, $secret) {
            return base64_encode(sha1($id.$expire.$secret));
        });
        $this->setUserTokenCreationFunction(function ($id, $expire, $secret) {
            return base64_encode(sha1($id.$expire.$secret));
        });
        $this->setClientHeaderParsingFunction(function ($header) {
            if (is_array($header)) {
                $header = array_shift($header);
            }
            $parts = [];
            foreach (preg_split("/\\s*,\\s*/", trim($header)) as $t) {
                if (false === strpos($t, ':')) {
                    break;
                }
                list($key, $value) = explode(':', $t, 2);
                $key   = trim($key);
                $value = trim($value);
                if (0 < strlen($value)) {
                    $parts[$key] = $value;
                }
            }
            return array_merge(['id' => null, 'expire' => null, 'token' => null], $parts);
        });
        $this->setUserHeaderParsingFunction(function ($header) {
            if (is_array($header)) {
                $header = array_shift($header);
            }
            $parts = [];
            foreach (preg_split("/\\s*,\\s*/", trim($header)) as $t) {
                if (false === strpos($t, ':')) {
                    break;
                }
                list($key, $value) = explode(':', $t, 2);
                $key   = trim($key);
                $value = trim($value);
                if (0 < strlen($value)) {
                    $parts[$key] = $value;
                }
            }
            return array_merge(['id' => null, 'password' => null, 'expire' => null, 'token' => null], $parts);
        });
        $this->setSudoHeaderParsingFunction(function ($header) {
            if (is_array($header)) {
                $header = array_shift($header);
            }
            $parts = [];
            foreach (preg_split("/\\s*,\\s*/", trim($header)) as $t) {
                if (false === strpos($t, ':')) {
                    break;
                }
                list($key, $value) = explode(':', $t, 2);
                $key   = trim($key);
                $value = trim($value);
                if (0 < strlen($value)) {
                    $parts[$key] = $value;
                }
            }
            return array_merge(['id' => null], $parts);
        });
        $this->setUserProvider($userProvider);

        if (null !== $clientSecret) {
            $this->setClientSecret($clientSecret);
        }

        if (null !== $userSecret) {
            $this->setUserSecret($userSecret);
        }
    }
    /**
     * @param string $clientHeaderKey
     *
     * @return $this
     */
    public function setClientHeaderKey($clientHeaderKey)
    {
        $this->clientHeaderKey = $clientHeaderKey;

        return $this;
    }
    /**
     * @return string
     */
    public function getClientHeaderKey()
    {
        return $this->clientHeaderKey;
    }
    /**
     * @param string $userHeaderKey
     *
     * @return $this
     */
    public function setUserHeaderKey($userHeaderKey)
    {
        $this->userHeaderKey = $userHeaderKey;

        return $this;
    }
    /**
     * @return string
     */
    public function getUserHeaderKey()
    {
        return $this->userHeaderKey;
    }
    /**
     * @param string $sudoHeaderKey
     *
     * @return $this
     */
    public function setSudoHeaderKey($sudoHeaderKey)
    {
        $this->sudoHeaderKey = $sudoHeaderKey;

        return $this;
    }
    /**
     * @return string
     */
    public function getSudoHeaderKey()
    {
        return $this->sudoHeaderKey;
    }
    /**
     * @param string $clientSecret
     *
     * @return $this
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }
    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }
    /**
     * @param string $userSecret
     *
     * @return $this
     */
    public function setUserSecret($userSecret)
    {
        $this->userSecret = $userSecret;

        return $this;
    }
    /**
     * @return string
     */
    public function getUserSecret()
    {
        return $this->userSecret;
    }
    /**
     * @param string $clientTokenCreationUriPattern
     *
     * @return $this
     */
    public function setClientTokenCreationUriPattern($clientTokenCreationUriPattern)
    {
        $this->clientTokenCreationUriPattern = $clientTokenCreationUriPattern;

        return $this;
    }
    /**
     * @return string
     */
    public function getClientTokenCreationUriPattern()
    {
        return $this->clientTokenCreationUriPattern;
    }
    /**
     * @param string $userTokenCreationUriPattern
     *
     * @return $this
     */
    public function setUserTokenCreationUriPattern($userTokenCreationUriPattern)
    {
        $this->userTokenCreationUriPattern = $userTokenCreationUriPattern;

        return $this;
    }
    /**
     * @return string
     */
    public function getUserTokenCreationUriPattern()
    {
        return $this->userTokenCreationUriPattern;
    }
    /**
     * @param callable $clientHeaderParsingFunction
     *
     * @return $this
     */
    public function setClientHeaderParsingFunction($clientHeaderParsingFunction)
    {
        $this->clientHeaderParsingFunction = $clientHeaderParsingFunction;

        return $this;
    }
    /**
     * @return callable
     */
    public function getClientHeaderParsingFunction()
    {
        return $this->clientHeaderParsingFunction;
    }
    /**
     * @param callable $userHeaderParsingFunction
     *
     * @return $this
     */
    public function setUserHeaderParsingFunction($userHeaderParsingFunction)
    {
        $this->userHeaderParsingFunction = $userHeaderParsingFunction;

        return $this;
    }
    /**
     * @return callable
     */
    public function getUserHeaderParsingFunction()
    {
        return $this->userHeaderParsingFunction;
    }
    /**
     * @param callable $sudoHeaderParsingFunction
     *
     * @return $this
     */
    public function setSudoHeaderParsingFunction($sudoHeaderParsingFunction)
    {
        $this->sudoHeaderParsingFunction = $sudoHeaderParsingFunction;

        return $this;
    }
    /**
     * @return callable
     */
    public function getSudoHeaderParsingFunction()
    {
        return $this->sudoHeaderParsingFunction;
    }
    /**
     * @param callable $clientTokenCreationFunction
     *
     * @return $this
     */
    public function setClientTokenCreationFunction($clientTokenCreationFunction)
    {
        $this->clientTokenCreationFunction = $clientTokenCreationFunction;

        return $this;
    }
    /**
     * @return callable
     */
    public function getClientTokenCreationFunction()
    {
        return $this->clientTokenCreationFunction;
    }
    /**
     * @param callable $userTokenCreationFunction
     *
     * @return $this
     */
    public function setUserTokenCreationFunction($userTokenCreationFunction)
    {
        $this->userTokenCreationFunction = $userTokenCreationFunction;

        return $this;
    }
    /**
     * @return callable
     */
    public function getUserTokenCreationFunction()
    {
        return $this->userTokenCreationFunction;
    }
    /**
     * @param Request $request
     *
     * @return array
     */
    public function parse(Request $request)
    {
        return [
            'client' => $this->getRequestClient($request),
            'user'   => $this->getRequestUser($request),
            'sudo'   => $this->getRequestSudo($request),
        ];
    }
    /**
     * @param Request $request
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function getRequestClient(Request $request)
    {
        $function = $this->getClientHeaderParsingFunction();

        return $function($request->headers->get(strtolower($this->getClientHeaderKey())));
    }
    /**
     * @param Request $request
     *
     * @return string
     */
    protected function getRequestUser(Request $request)
    {
        $function = $this->getUserHeaderParsingFunction();

        return $function($request->headers->get(strtolower($this->getUserHeaderKey())));
    }
    /**
     * @param Request $request
     *
     * @return string
     */
    protected function getRequestSudo(Request $request)
    {
        $function = $this->getSudoHeaderParsingFunction();

        return $function($request->headers->get(strtolower($this->getSudoHeaderKey())));
    }
    /**
     * @param string $id
     * @param string $expire
     * @param string $secret
     *
     * @return string
     */
    public function buildClientToken($id, $expire, $secret)
    {
        $function = $this->getClientTokenCreationFunction();

        return $function($id, $expire, $secret);
    }
    /**
     * @param string $id
     * @param string $expire
     * @param string $secret
     *
     * @return string
     */
    public function buildUserToken($id, $expire, $secret)
    {
        $function = $this->getUserTokenCreationFunction();

        return $function($id, $expire, $secret);
    }
    /**
     * @param \DateTime $date
     * @param \DateTime $expirationDate
     *
     * @return bool
     */
    public function isDateExpired(\DateTime $date, \DateTime $expirationDate)
    {
        return $date > $expirationDate;
    }
    /**
     * @param string $expire
     *
     * @return \DateTime
     */
    public function convertStringToDateTime($expire)
    {
        return \DateTime::createFromFormat(\DateTime::ISO8601, $expire);
    }
    /**
     * @param Request $request
     *
     * @return string
     */
    public function createClientTokenFromRequestAndReturnHeaders(Request $request)
    {
        $headers = $request->headers->all();

        if (!isset($headers[strtolower($this->getClientHeaderKey())])) {
            $headers[strtolower($this->getClientHeaderKey())] = null;
        }

        $function = $this->getClientHeaderParsingFunction();
        $parts    = $function($headers[strtolower($this->getClientHeaderKey())]);

        $now    = new \DateTime();
        $expire = $now->add(new \DateInterval('P1D'));

        return $this->buildGenericTokenExpirableHeaders(
            $this->getClientHeaderKey(),
            $parts['id'],
            $expire,
            $this->createClientToken($parts['id'], $expire)
        );
    }
    /**
     * @param Request $request
     *
     * @return string
     *
     * @throws \Exception
     */
    public function createUserTokenFromRequestAndReturnHeaders(Request $request)
    {
        $headers = $request->headers->all();

        if (!isset($headers[strtolower($this->getClientHeaderKey())])) {
            throw $this->createException(401, "Client authentication required");
        }

        if (!isset($headers[strtolower($this->getUserHeaderKey())])) {
            throw $this->createException(401, "User identity required");
        }

        $function = $this->getUserHeaderParsingFunction();
        $parts    = $function($headers[strtolower($this->getUserHeaderKey())]);

        $now    = new \DateTime();
        $expire = $now->add(new \DateInterval('P1D'));

        if (!isset($parts['id'])) {
            throw $this->createException(401, "User identity required");
        }
        if (!isset($parts['password'])) {
            throw $this->createException(401, "User identity required");
        }

        $account = $this->getUserProvider()->getAccount($parts['id']);

        $password = null;

        if (true === isset($account['password'])) {
            $password = (string) $account['password'];
        }

        $expectedEncodedPassword = $password;
        $actualEncodedPassword   = $parts['password'];

        if ($expectedEncodedPassword !== $actualEncodedPassword) {
            throw $this->createException(403, "Bad credentials for user '%s'", $parts['id']);
        }

        return $this->buildGenericTokenExpirableHeaders(
            $this->getUserHeaderKey(),
            $parts['id'],
            $expire,
            $this->createUserToken($parts['id'], $expire)
        );
    }
    /**
     * @param \DateTime $expire
     *
     * @return string
     */
    protected function convertDateTimeToString(\DateTime $expire)
    {
        return $expire->format(\DateTime::ISO8601);
    }
    /**
     * @param string    $id
     * @param \DateTime $expire
     *
     * @return string
     */
    protected function createClientToken($id, \DateTime $expire)
    {
        $this->getClientProvider()->get($id, ['id']);

        return $this->buildClientToken($id, $this->convertDateTimeToString($expire), $this->getClientSecret());
    }
    /**
     * @param string    $id
     * @param \DateTime $expire
     *
     * @return string
     */
    protected function createUserToken($id, \DateTime $expire)
    {
        return $this->buildUserToken($id, $this->convertDateTimeToString($expire), $this->getUserSecret());
    }
    /**
     * @param string    $headerKey
     * @param string    $id
     * @param \DateTime $expire
     * @param string    $token
     *
     * @return array
     */
    protected function buildGenericTokenExpirableHeaders($headerKey, $id, \DateTime $expire, $token)
    {
        return [
            $headerKey => sprintf(
                'id: %s, expire: %s, token: %s',
                $id,
                $this->convertDateTimeToString($expire),
                $token
            )
        ];
    }
    /**
     * @param Request $request
     * @return array
     */
    public function fetchQueryCriteria(Request $request)
    {
        $v = $request->get('criteria', []);

        if (!is_array($v)) {
            $v = [];
        }

        return $v;
    }
    /**
     * @param Request $request
     *
     * @return array
     */
    public function fetchQueryFields(Request $request)
    {
        $v = $request->get('fields', []);

        if (!is_array($v) || !count($v)) {
            return [];
        }

        $fields = [];

        foreach ($v as $field) {
            if ('!' === substr($field, 0, 1)) {
                $fields[substr($field, 1)] = false;
            } else {
                $fields[$field] = true;
            }
        }

        return $fields;
    }
    /**
     * @param Request $request
     *
     * @return null|int
     */
    public function fetchQueryLimit(Request $request)
    {
        $v = $request->get('limit', null);

        return strlen($v) ? intval($v) : null;
    }
    /**
     * @param Request $request
     *
     * @return int
     */
    public function fetchQueryOffset(Request $request)
    {
        $v = intval($request->get('offset', 0));

        return 0 > $v ? 0 : $v;
    }
    /**
     * @param Request $request
     *
     * @return array
     */
    public function fetchQuerySorts(Request $request)
    {
        $v = $request->get('sorts', []);

        if (!is_array($v) || !count($v)) {
            return [];
        }

        return array_map(
            function ($a) {
                return (int) $a;
            },
            $v
        );
    }
    /**
     * @param Request $request
     *
     * @return array
     */
    public function fetchRequestData(Request $request)
    {
        return $request->request->all();
    }
    /**
     * @param Request $request
     * @param string  $parameter
     *
     * @return mixed
     */
    public function fetchRouteParameter(Request $request, $parameter)
    {
        return $request->attributes->get($parameter);
    }
}
