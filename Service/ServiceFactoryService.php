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

        $api->Config->ClientId        = $clientId;
        $api->Config->ClientPassword  = $clientPassword;
        $api->Config->TemporaryFolder = sys_get_temp_dir();

        switch ($environment) {
            case 'live':
                $api->Config->BaseUrl = 'https://api.mangopay.com';
                break;
            case 'sandbox':
                $api->Config->BaseUrl = 'https://api.sandbox.mangopay.com';
                break;
            default:
                $api->Config->BaseUrl = $environment;
                break;
        }

        foreach ($options as $key => $value) {
            $api->Config->$key = $value;
        }

        return $api;
    }
}
