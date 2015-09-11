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

use MangoPay\Money;
use MangoPay\PayIn;
use MangoPay\Wallet;
use MangoPay\Transfer;
use MangoPay\MangoPayApi;
use MangoPay\BankAccount;
use MangoPay\UserNatural;
use MangoPay\BankAccountDetailsIBAN;
use MangoPay\BankAccountDetailsOTHER;
use MangoPay\PayInPaymentDetailsCard;
use MangoPay\PayInExecutionDetailsWeb;
use MangoPay\Exception as MangoPayException;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\MangoPayApiAwareTrait;

/**
 * MangoPay Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class MangoPayService
{
    use ServiceTrait;
    use MangoPayApiAwareTrait;
    /**
     * Constructs a new service
     *
     * @param MangoPayApi $mangoPayApi
     */
    public function __construct(MangoPayApi $mangoPayApi)
    {
        $this->setMangoPayApi($mangoPayApi);
    }
    /**
     * Create a new MangoPay user.
     *
     * @param array $data
     *
     * @return array
     *
     * @throws MangoPayException
     */
    public function createUser(array $data)
    {
        $user = new UserNatural();

        // @codingStandardsIgnoreStart
        $user->FirstName          = $data['firstName'];
        $user->LastName           = $data['lastName'];
        $user->Email              = $data['email'];
        $user->Birthday           = (new \DateTime($data['birthday']))->getTimestamp();
        $user->CountryOfResidence = $data['countryOfResidence'];
        $user->Nationality        = $data['nationality'];

        return $this->prepareResult($this->getMangoPayApi()->Users->Create($user));
        // @codingStandardsIgnoreEnd
    }
    /**
     * Create a new MangoPay wallet.
     *
     * @param array $data
     *
     * @return array
     *
     * @throws MangoPayException
     */
    public function createWallet(array $data)
    {
        $wallet = new Wallet();

        // @codingStandardsIgnoreStart
        $wallet->Currency    = $data['currency'];
        $wallet->Description = $data['description'];
        $wallet->Owners      = $data['owners'];

        return $this->prepareResult($this->getMangoPayApi()->Wallets->Create($wallet));
        // @codingStandardsIgnoreEnd
    }
    /**
     * Create a new MangoPay pay-in.
     *
     * @param array $data
     *
     * @return array
     *
     * @throws MangoPayException
     */
    public function createPayIn(array $data)
    {
        $payIn = new PayIn();

        // @codingStandardsIgnoreStart
        $payIn->AuthorId         = $data['authorId'];
        $payIn->CreditedUserId   = $data['creditedUserId'];
        $payIn->CreditedWalletId = $data['creditedWalletId'];
        $payIn->DebitedFunds     = new Money();
        $payIn->Fees             = new Money();
        $payIn->PaymentDetails   = new PayInPaymentDetailsCard();
        $payIn->ExecutionDetails = new PayInExecutionDetailsWeb();

        $payIn->DebitedFunds->Currency = $data['currency'];
        $payIn->DebitedFunds->Amount   = round($data['debitedFunds'] * 100, 0); // cents

        $payIn->Fees->Currency = $data['currency'];
        $payIn->Fees->Amount   = round($data['fees'] * 100, 0); // cents

        $payIn->PaymentDetails->CardType = $data['cardType'];

        $payIn->ExecutionDetails->Culture    = $data['culture'];
        $payIn->ExecutionDetails->ReturnURL  = $data['returnUrl'];
        $payIn->ExecutionDetails->SecureMode = $data['secureMode'];

        return $this->prepareResult($this->getMangoPayApi()->PayIns->Create($payIn));
        // @codingStandardsIgnoreEnd
    }
    /**
     * Create a new MangoPay transfer.
     *
     * @param array $data
     *
     * @return array
     *
     * @throws MangoPayException
     */
    public function createTransfer(array $data)
    {
        $transfer = new Transfer();

        // @codingStandardsIgnoreStart
        $transfer->AuthorId         = $data['authorId'];
        $transfer->CreditedUserId   = $data['creditedUserId'];
        $transfer->CreditedWalletId = $data['creditedWalletId'];
        $transfer->DebitedWalletId  = $data['debitedWalletId'];
        $transfer->DebitedFunds     = new Money();
        $transfer->Fees             = new Money();

        $transfer->DebitedFunds->Currency = $data['currency'];
        $transfer->DebitedFunds->Amount   = round($data['debitedFunds'] * 100, 0); // cents

        $transfer->Fees->Currency = $data['currency'];
        $transfer->Fees->Amount   = round($data['fees'] * 100, 0); // cents

        return $this->prepareResult($this->getMangoPayApi()->Transfers->Create($transfer));
        // @codingStandardsIgnoreEnd
    }
    /**
     * Create a MangoPay bank account.
     *
     * @param array $data
     *
     * @return array
     *
     * @throws MangoPayException
     */
    public function createBankAccount(array $data)
    {
        $bankAccount = new BankAccount();

        // @codingStandardsIgnoreStart
        $bankAccount->OwnerName    = $data['ownerName'];
        $bankAccount->OwnerAddress = $data['ownerAddress'];
        $bankAccount->UserId       = $data['userId'];

        switch ($data['type']) {
            case 'iban':
                $bankAccount->Type = 'IBAN';

                $details = new BankAccountDetailsIBAN();

                $details->BIC  = $data['bic'];
                $details->IBAN = $data['iban'];

                $bankAccount->Details = $details;
                break;
            case 'other';
                $bankAccount->Type = 'OTHER';

                $details = new BankAccountDetailsOTHER();

                $details->BIC           = $data['bic'];
                $details->AccountNumber = $data['account'];
                $details->Country       = $data['country'];

                $bankAccount->Details = $details;
                break;
            default:
                /** @noinspection PhpMethodParametersCountMismatchInspection */
                throw $this->createUnexpectedException("Unsupported bank account type '%s'", $data['type']);
        }

        return $this->prepareResult(
            $this->getMangoPayApi()->Users->CreateBankAccount($data['userId'], $bankAccount)
        );
        // @codingStandardsIgnoreEnd
    }
    /**
     * Convert the specified raw MangoPay result to array.
     *
     * @param mixed $raw
     *
     * @return array
     */
    protected function prepareResult($raw)
    {
        $raw = (array) $raw;

        return array_combine(
            array_map(function ($k) {
                return lcfirst($k);
            }, array_keys($raw)),
            array_values($raw)
        );
    }
}
