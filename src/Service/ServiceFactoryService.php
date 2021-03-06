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

use MangoPay\MangoPayApi;

/**
 * Service Factory Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ServiceFactoryService
{
    /**
     * Create a new MangoPayApi instance.
     *
     * @param string $environment
     * @param string $clientId
     * @param string $clientPassword
     * @param array  $options
     *
     * @return MangoPayApi
     */
    public function createMangoPayApi($environment, $clientId, $clientPassword, array $options = [])
    {
        $api = new MangoPayApi();

        // @codingStandardsIgnoreStart
        $api->Config->ClientId        = $clientId;
        $api->Config->ClientPassword  = $clientPassword;
        $api->Config->TemporaryFolder = sys_get_temp_dir();
        // @codingStandardsIgnoreEnd

        switch ($environment) {
            case 'live':
                $url = 'https://api.mangopay.com';
                break;
            case 'sandbox':
                $url = 'https://api.sandbox.mangopay.com';
                break;
            default:
                $url = $environment;
                break;
        }

        // @codingStandardsIgnoreLine
        $api->Config->BaseUrl = $url;

        foreach ($options as $key => $value) {
            // @codingStandardsIgnoreLine
            $api->Config->$key = $value;
        }

        return $api;
    }
}
