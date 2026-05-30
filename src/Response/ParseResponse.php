<?php
// Svn/ParseResponse.php

namespace Svn;

use Svn\OVOID;

class ParseResponse
{
    /**
     * Store Class mapping for exact URLs
     *
     * @var array
     */
    public $storeClass = [
        OVOID::BASE_ENDPOINT . 'v2.0/api/auth/customer/login2FA'                       => 'Svn\Response\Login2FAResponse',
        OVOID::BASE_ENDPOINT . 'v2.0/api/auth/customer/login2FA/verify'                => 'Svn\Response\Login2FAVerifyResponse',
        OVOID::BASE_ENDPOINT . 'v2.0/api/auth/customer/loginSecurityCode/verify'       => 'Svn\Response\LoginSecurityCodeResponse',
        OVOID::BASE_ENDPOINT . 'v3.0/api/front'                                        => 'Svn\Response\FrontResponse',
        OVOID::BASE_ENDPOINT . 'v1.0/budget/detail'                                    => 'Svn\Response\BudgetResponse',
        OVOID::BASE_ENDPOINT . 'v1.0/api/customers/transfer'                           => 'Svn\Response\CustomerTransferResponse',
        OVOID::BASE_ENDPOINT . 'v1.0/api/auth/customer/genTrxId'                       => 'Svn\Response\GenTrxIdResponse',
        OVOID::BASE_ENDPOINT . 'v1.0/notification/status/count/UNREAD'                 => 'Svn\Response\NotificationUnreadResponse',
        OVOID::BASE_ENDPOINT . 'v1.0/notification/status/all'                          => 'Svn\Response\NotificationAllResponse',
        OVOID::BASE_ENDPOINT . 'v1.0/api/auth/customer/logout'                         => 'Svn\Response\LogoutResponse',
        OVOID::AWS . 'gpdm/ovo/ID/v2/billpay/get-billers?categoryID=5C6'               => 'Svn\Response\BillpayResponse',
        OVOID::AWS . 'gpdm/ovo/ID/v1/billpay/inquiry'                                  => 'Svn\Response\InquiryResponse',
        OVOID::BASE_ENDPOINT . 'v1.0/api/auth/customer/unlock'                         => 'Svn\Response\CustomerUnlockResponse',
        OVOID::AWS . 'gpdm/ovo/ID/v1/billpay/pay'                                      => 'Svn\Response\PayResponse',
        OVOID::AWS . 'gpdm/ovo/ID/v1/billpay/checkstatus'                              => 'Svn\Response\PayCheckStatusResponse',
        OVOID::BASE_ENDPOINT . 'v1.0/reference/master/ref_bank'                        => 'Svn\Response\Ref_BankResponse',
        OVOID::BASE_ENDPOINT . 'transfer/inquiry'                                      => 'Svn\Response\TransferInquiryResponse',
        OVOID::BASE_ENDPOINT . 'transfer/direct'                                       => 'Svn\Response\TransferDirectResponse',
        OVOID::BASE_ENDPOINT . 'v1.1/api/auth/customer/isOVO'                          => 'Svn\Response\isOVOResponse',
        OVOID::OVO_API_AWS . 'v3/user/accounts/otp'                                    => 'Svn\Response\OTPResponse',
        OVOID::OVO_API_AWS . 'v3/user/accounts/otp/validation'                         => 'Svn\Response\OTPValidationResponse',
        OVOID::OVO_API_AWS . 'v3/user/public_keys'                                     => 'Svn\Response\PublicKeyResponse',
        OVOID::OVO_API_AWS . 'v3/user/accounts/login'                                  => 'Svn\Response\AccountLoginResponse',
        OVOID::BASE_ENDPOINT . 'wallet/inquiry'                                        => 'Svn\Response\BalanceResponse',
    ];

    private $response;

    /**
     * Parse response constructor
     *
     * @param mixed $chResult
     * @param string $url
     * @throws \Svn\Exception\OvoidException
     */
    public function __construct($chResult, $url)
    {
        $jsonDecodeResult = json_decode($chResult);
        
        // Check if there's an error from OVO Response
        if (isset($jsonDecodeResult->code) && $jsonDecodeResult->code != '0000') {
            $message = isset($jsonDecodeResult->message) ? $jsonDecodeResult->message : 'Unknown error';
            throw new \Svn\Exception\OvoidException($message . ' - ' . $url, (int)$jsonDecodeResult->code);
        }

        $parts = parse_url($url);
        $path = $parts['path'] ?? '';
        $uriSegments = explode('/', trim($path, '/'));

        // Dynamic route matching
        if ($path == '/wallet/v2/transaction') {
            $this->response = new \Svn\Response\WalletTransactionResponse($jsonDecodeResult);
        } 
        elseif ($path == '/payment/orders/v1/list') {
            $this->response = new \Svn\Response\PaymentOrdersResponse($jsonDecodeResult);
        } 
        elseif (strpos($path, '/gpdm/ovo/ID/v1/billpay/get-denominations/') !== false) {
            $this->response = new \Svn\Response\DenominationsReponse($jsonDecodeResult);
        } 
        elseif (isset($uriSegments[0]) && $uriSegments[0] == 'wallet' && isset($uriSegments[1]) && $uriSegments[1] == 'transaction' && count($uriSegments) >= 4) {
            $this->response = new \Svn\Response\DetailHistoryResponse($jsonDecodeResult);
        }
        elseif (strpos($path, 'v1.0/api/auth/customer/unlockAndValidateTrxId') !== false) {
            $this->response = new \Svn\Response\UnlockAndValidateResponse($jsonDecodeResult);
        }
        else {
            // Check if URL exists in mapping
            if (isset($this->storeClass[$url])) {
                $className = $this->storeClass[$url];
                $this->response = new $className($jsonDecodeResult);
            } else {
                // Fallback to generic response
                $this->response = new \Svn\Response\BaseResponse($jsonDecodeResult);
            }
        }
    }

    /**
     * Get response
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}