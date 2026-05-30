<?php
// Svn/OVOID.php

namespace Svn;

use Svn\HTTP\Curl;
use Svn\Meta\Meta;
use Svn\Meta\ActionMark;

/**
 * OVOID - Fixed Version
 * 
 * @author lintangtimur
 * @package library
 * @license MIT
 */
class OVOID
{
    /**
     * Base OVO ENDPOINT
     */
    const BASE_ENDPOINT = 'https://api.ovo.id/';
    const AWS           = 'https://apigw01.aws.ovo.id/';
    const OVO_API_AWS   = 'https://agw.ovo.id/';

    /**
     * Authorization Token
     *
     * @var string
     */
    private $authToken;
    
    /**
     * Device ID
     *
     * @var string
     */
    private $deviceId;

    /**
     * Headers for request
     *
     * @var array
     */
    private $headers = [
        'OS'           => 'Android',
        'OS-Version'   => '11',
        'client-id'    => 'ovo_android',
        'device-id'    => '',
        'App-Version'  => Meta::APP_VERSION,
        'User-Agent'   => 'okhttp/4.9.0',
    ];

    /**
     * Constructor
     *
     * @param string|null $authToken
     * @param string $deviceId
     */
    public function __construct($authToken = null, $deviceId)
    {
        $this->authToken = $authToken;
        $this->_setDeviceId($deviceId);
    }

    /**
     * Get device ID
     *
     * @return string
     */
    private function getDeviceId()
    {
        return $this->deviceId;
    }

    /**
     * Set device ID
     *
     * @param string $deviceId
     */
    private function _setDeviceId($deviceId)
    {
        $this->deviceId = $deviceId;
        $this->headers['device-id'] = $deviceId;
    }

    /**
     * Get additional headers with authorization
     *
     * @param bool $bearer
     * @return array
     */
    private function _additionalHeader($bearer = false)
    {
        $headers = $this->headers;
        
        if ($this->authToken) {
            if ($bearer) {
                $headers['Authorization'] = 'Bearer ' . $this->authToken;
            } else {
                $headers['Authorization'] = $this->authToken;
            }
        }
        
        return $headers;
    }

    // ==================== AUTHENTICATION ====================

    /**
     * Kirim OTP
     *
     * @param string $noTelp
     * @return \Svn\Response\OTPResponse
     */
    public function OTP($noTelp)
    {
        $ch = new Curl;

        $data = [
            'channel_code' => 'ovo_android',
            'device_id'    => $this->headers['device-id'],
            'msisdn'       => $noTelp,
            'otp'          => [
                'locale'    => 'ID',
                'sms_hash'  => 'm9mj4ctIVR8'
            ]
        ];

        return $ch->post(OVOID::OVO_API_AWS . 'v3/user/accounts/otp', $data, $this->headers)->getResponse();
    }

    /**
     * OTP Validation
     *
     * @param string $noTelp
     * @param string $otpRefId
     * @param string $otp
     * @return \Svn\Response\OTPValidationResponse
     */
    public function OTPValidation($noTelp, $otpRefId, $otp)
    {
        $ch = new Curl;

        $data = [
            'channel_code' => 'ovo_android',
            'device_id'    => $this->headers['device-id'],
            'msisdn'       => $noTelp,
            'otp'          => [
                'otp'        => $otp,
                'otp_ref_id' => $otpRefId,
                'type'       => 'LOGIN'
            ]
        ];

        return $ch->post(OVOID::OVO_API_AWS . 'v3/user/accounts/otp/validation', $data, $this->headers)->getResponse();
    }

    /**
     * Mendapatkan public key untuk enkripsi proses berikutnya
     *
     * @return \Svn\Response\PublicKeyResponse
     */
    public function getPublicKeys()
    {
        $ch = new Curl;
        
        $headers = [
            'OS'          => 'Android',
            'OS-Version'  => '11',
            'client-id'   => 'ovo_android',
            'device-id'   => $this->headers['device-id'],
            'host'        => 'agw.ovo.id',
            'User-Agent'  => 'okhttp/4.9.0',
            'Connection'  => 'close',
        ];

        return $ch->get(OVOID::OVO_API_AWS . 'v3/user/public_keys', null, $headers)->getResponse();
    }

    /**
     * Encrypt password for login
     *
     * @param string $noTelp
     * @param string $otpRefId
     * @param string $securityCode
     * @return string
     */
    private function _encryptPassword($noTelp, $otpRefId, $securityCode)
    {
        $publicKeyResponse = $this->getPublicKeys();
        $publicKey = $publicKeyResponse->getPublicKey();
        
        if (!$publicKey) {
            throw new \Exception("Failed to get public key");
        }
        
        // Clean public key
        $publicKey = str_replace(['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\n", "\r"], '', $publicKey);
        $publicKey = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($publicKey, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        
        $deviceId = $this->headers['device-id'];
        $currentTimeInMillis = (string) (time() * 1000);
        $payload = "LOGIN|{$securityCode}|{$currentTimeInMillis}|{$deviceId}|{$noTelp}|{$deviceId}|{$otpRefId}";
        
        $encrypted = '';
        openssl_public_encrypt($payload, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        
        return base64_encode($encrypted);
    }

    /**
     * Account Login
     *
     * @param string $noTelp format +62xxxx
     * @param string $otpRefId
     * @param string $otpToken
     * @param string $securityCode 6 digit security code
     * @return \Svn\Response\AccountLoginResponse
     */
    public function accountLogin($noTelp, $otpRefId, $otpToken, $securityCode)
    {
        $ch = new Curl;

        $data = [
            'channel_code' => 'ovo_android',
            'device_id'    => $this->headers['device-id'],
            'credentials'  => [
                'otp_token' => $otpToken,
                'password'  => [
                    'format' => 'rsa',
                    'value'  => $this->_encryptPassword($noTelp, $otpRefId, $securityCode) . "\n"
                ]
            ],
            'msisdn'                => $noTelp,
            'push_notification_id'  => 'fs-DYcGaRbKERLhF4hkQ92:APA91bEjjUFzzFvadIKtdrrqsyrGH26xLRR5-Oyym2l9Ybv0O1cnvqA14ghuTbXz0ogazN-Kw6iGxW2klakANBaVXoFCLrT4hWJJ5FCGOz2o5bGE7RX6XpxndNkcxnqpWat449vBvYSa'
        ];

        $response = $ch->post(OVOID::OVO_API_AWS . 'v3/user/accounts/login', $data, $this->headers)->getResponse();
        
        // Set auth token for subsequent requests
        $accessToken = $response->getAccessToken();
        if ($accessToken) {
            $this->authToken = $accessToken;
        }
        
        return $response;
    }

    // ==================== BALANCE MODEL ====================

    /**
     * Get OVO Balance (Front Model)
     *
     * @return \Svn\Response\FrontResponse
     */
    public function balanceModel()
    {
        $ch = new Curl;
        return $ch->get(OVOID::BASE_ENDPOINT . 'v3.0/api/front', null, $this->_additionalHeader())->getResponse();
    }

    /**
     * Get Payment Method
     *
     * @return array
     */
    public function getPaymentMethod()
    {
        $balance = $this->balanceModel();
        return $balance->getPaymentMethod();
    }

    /**
     * Get Card Balance
     *
     * @param string $paymentMethod
     * @return array|null
     */
    public function getCardBalance($paymentMethod = 'OVO')
    {
        $balance = $this->balanceModel();
        return $balance->getCardBalance($paymentMethod);
    }

    /**
     * Get Card Number
     *
     * @param string $paymentMethod
     * @return string|null
     */
    public function getCardNo($paymentMethod = 'OVO')
    {
        $balance = $this->balanceModel();
        return $balance->getCardNo($paymentMethod);
    }

    /**
     * Get complete balance
     *
     * @return array
     */
    public function getFullBalance()
    {
        $balance = $this->balanceModel();
        return $balance->getAllBalances();
    }

    /**
     * Get simple balance (wallet inquiry)
     *
     * @return \Svn\Response\BalanceResponse
     */
    public function balance()
    {
        $ch = new Curl;
        return $ch->get(OVOID::BASE_ENDPOINT . 'wallet/inquiry', null, $this->_additionalHeader())->getResponse();
    }

    // ==================== PROFILE ====================

    /**
     * Get budget detail
     *
     * @return \Svn\Response\BudgetResponse
     */
    public function getBudget()
    {
        $ch = new Curl;
        return $ch->get(OVOID::BASE_ENDPOINT . 'v1.0/budget/detail', null, $this->_additionalHeader())->getResponse();
    }

    // ==================== TRANSFER ====================

    /**
     * Check apakah nomor OVO
     *
     * @param int $totalAmount
     * @param string $mobilePhone
     * @return \Svn\Response\isOVOResponse
     */
    public function isOVO($totalAmount, $mobilePhone)
    {
        $ch = new Curl;
        $data = [
            'totalAmount' => $totalAmount,
            'mobile'      => $mobilePhone
        ];

        return $ch->post(OVOID::BASE_ENDPOINT . 'v1.1/api/auth/customer/isOVO', $data, $this->_additionalHeader())->getResponse();
    }

    /**
     * Generate transaction ID
     *
     * @param int $amount
     * @param string $actionMark
     * @return \Svn\Response\GenTrxIdResponse
     */
    private function generateTrxId($amount, $actionMark)
    {
        $ch = new Curl;
        $data = [
            'actionMark' => $actionMark,
            'amount'     => $amount
        ];

        return $ch->post(OVOID::BASE_ENDPOINT . 'v1.0/api/auth/customer/genTrxId', $data, $this->_additionalHeader())->getResponse();
    }

    /**
     * Generate signature for unlockAndValidateTrxId
     *
     * @param string $trxId
     * @param int $amount
     * @return string
     */
    private function signatureUnlockAndValidateTrxId($trxId, $amount)
    {
        return sha1($trxId . '||' . $amount . '||' . $this->deviceId);
    }

    /**
     * Unlock and validate transaction ID (for multiple transfers)
     *
     * @param string $trxId
     * @param int $amount
     * @param string $securityCode
     * @return array
     */
    protected function unlockAndValidateTrxId($trxId, $amount, $securityCode)
    {
        $ch = new Curl;

        $data = [
            'trxId'        => $trxId,
            'signature'    => $this->signatureUnlockAndValidateTrxId($trxId, $amount),
            'appVersion'   => Meta::APP_VERSION,
            'securityCode' => $securityCode
        ];

        return $ch->post(OVOID::BASE_ENDPOINT, 'v1.0/api/auth/customer/unlockAndValidateTrxId', $data, $this->_additionalHeader())->getResponse();
    }

    /**
     * Transfer sesama OVO
     *
     * NOTE: Untuk transfer lebih dari 2x, perlu menggunakan unlockAndValidateTrxId
     *
     * @param string $to_mobilePhone
     * @param int $amount
     * @param string $message
     * @param int $transferCount Untuk tracking multiple transfers
     * @return \Svn\Response\CustomerTransferResponse
     * @throws \Svn\Exception\AmountException
     */
    public function transferOvo($to_mobilePhone, $amount, $message = '', $transferCount = 1)
    {
        if ($amount < 10000) {
            throw new \Svn\Exception\AmountException('Minimal 10.000');
        }

        $ch = new Curl;
        
        // Generate trxId dengan transfer count untuk multiple transfers
        $trxIdResponse = $this->generateTrxId($amount, ActionMark::TRANSFER_OVO);
        $trxId = $trxIdResponse->getTrxId();
        
        // Untuk transfer ke-3 atau lebih, perlu signature
        if ($transferCount >= 3) {
            // Butuh security code untuk unlock
            // Ini akan membutuhkan input dari user
            throw new \Exception("Untuk transfer ke-3 atau lebih, diperlukan security code dan unlock. Gunakan method transferOvoWithUnlock()");
        }
        
        $data = [
            'amount'   => $amount,
            'message'  => $message == '' ? 'Sent from OVOID' : $message,
            'to'       => $to_mobilePhone,
            'trxId'    => $trxId
        ];

        return $ch->post(OVOID::BASE_ENDPOINT . 'v1.0/api/customers/transfer', $data, $this->_additionalHeader())->getResponse();
    }
    
    /**
     * Transfer OVO dengan unlock (untuk multiple transfers)
     *
     * @param string $to_mobilePhone
     * @param int $amount
     * @param string $securityCode
     * @param string $message
     * @return \Svn\Response\CustomerTransferResponse
     */
    public function transferOvoWithUnlock($to_mobilePhone, $amount, $securityCode, $message = '')
    {
        if ($amount < 10000) {
            throw new \Svn\Exception\AmountException('Minimal 10.000');
        }
        
        // Generate trxId
        $trxIdResponse = $this->generateTrxId($amount, ActionMark::TRANSFER_OVO);
        $trxId = $trxIdResponse->getTrxId();
        
        // Unlock transaction
        $unlockResult = $this->unlockAndValidateTrxId($trxId, $amount, $securityCode);
        
        // Proceed with transfer
        $ch = new Curl;
        $data = [
            'amount'   => $amount,
            'message'  => $message == '' ? 'Sent from OVOID' : $message,
            'to'       => $to_mobilePhone,
            'trxId'    => $trxId
        ];

        return $ch->post(OVOID::BASE_ENDPOINT . 'v1.0/api/customers/transfer', $data, $this->_additionalHeader())->getResponse();
    }

    /**
     * Transfer antar bank
     *
     * @param string $accountName
     * @param string $accountNo
     * @param string $accountNoDestination
     * @param int $amount
     * @param string $bankCode
     * @param string $bankName
     * @param string $message
     * @param string $notes
     * @return \Svn\Response\TransferDirectResponse
     * @throws \Svn\Exception\AmountException
     */
    public function transferBank($accountName, $accountNo, $accountNoDestination, $amount, $bankCode, $bankName, $message, $notes)
    {
        if ($amount < 10000) {
            throw new \Svn\Exception\AmountException('Minimal 10.000');
        }

        $ch = new Curl;

        $data = [
            'accountName'          => $accountName,
            'accountNo'            => $accountNo,
            'accountNoDestination' => $accountNoDestination,
            'amount'               => $amount,
            'bankCode'             => $bankCode,
            'bankName'             => $bankName,
            'message'              => $message,
            'notes'                => $notes,
            'transactionId'        => $this->generateTrxId($amount, ActionMark::TRANSFER_BANK)->getTrxId()
        ];

        return $ch->post(OVOID::BASE_ENDPOINT . 'transfer/direct', $data, $this->_additionalHeader())->getResponse();
    }

    /**
     * Transfer inquiry
     *
     * @param string $accountNo
     * @param int $amount
     * @param string $bankCode
     * @param string $bankName
     * @param string $message
     * @return \Svn\Response\TransferInquiryResponse
     */
    public function transferInquiry($accountNo, $amount, $bankCode, $bankName, $message)
    {
        $ch = new Curl;

        $data = [
            'accountNo' => $accountNo,
            'amount'    => $amount,
            'bankCode'  => $bankCode,
            'bankName'  => $bankName,
            'message'   => $message
        ];

        return $ch->post(OVOID::BASE_ENDPOINT . 'transfer/inquiry', $data, $this->_additionalHeader())->getResponse();
    }

    /**
     * Get bank reference list
     *
     * @return \Svn\Response\Ref_BankResponse
     */
    public function getRefBank()
    {
        $ch = new Curl;
        return $ch->get(OVOID::BASE_ENDPOINT . 'v1.0/reference/master/ref_bank', null, $this->_additionalHeader())->getResponse();
    }

    // ==================== TRANSACTION HISTORY ====================

    /**
     * Wallet Transaction
     *
     * @param int $page
     * @param int $limit
     * @return \Svn\Response\WalletTransactionResponse
     */
    public function getWalletTransaction($page, $limit = 10)
    {
        $ch = new Curl;
        
        $url = OVOID::BASE_ENDPOINT . 'wallet/v2/transaction?page=' . $page . '&limit=' . $limit . '&productType=001';
        
        return $ch->get($url, null, $this->_additionalHeader())->getResponse();
    }

    /**
     * Detail History
     *
     * @param string $merchantId
     * @param string $merchantInvoice
     * @return \Svn\Response\DetailHistoryResponse
     */
    public function detailHistory($merchantId, $merchantInvoice)
    {
        $ch = new Curl;
        
        $url = OVOID::BASE_ENDPOINT . 'wallet/transaction/' . $merchantId . '/' . $merchantInvoice;
        
        return $ch->get($url, null, $this->_additionalHeader())->getResponse();
    }

    // ==================== NOTIFICATIONS ====================

    /**
     * Get total unread notification count
     *
     * @return \Svn\Response\NotificationUnreadResponse
     */
    public function unreadHistory()
    {
        $ch = new Curl;
        return $ch->get(OVOID::BASE_ENDPOINT . 'v1.0/notification/status/count/UNREAD', null, $this->_additionalHeader())->getResponse();
    }

    /**
     * Get all notifications
     *
     * @return \Svn\Response\NotificationAllResponse
     */
    public function allNotification()
    {
        $ch = new Curl;
        return $ch->get(OVOID::BASE_ENDPOINT . 'v1.0/notification/status/all', null, $this->_additionalHeader())->getResponse();
    }

    // ==================== BILL PAYMENT ====================

    /**
     * Get billers list
     *
     * @return \Svn\Response\BillpayResponse
     */
    public function getBillers()
    {
        $ch = new Curl;
        return $ch->get(OVOID::AWS . 'gpdm/ovo/ID/v2/billpay/get-billers?categoryID=5C6', null, $this->_additionalHeader())->getResponse();
    }

    /**
     * Get denominations by product ID
     *
     * @param int $product_id
     * @return \Svn\Response\DenominationsReponse
     */
    public function getDenominationByProductId($product_id)
    {
        $ch = new Curl;
        return $ch->get(OVOID::AWS . 'gpdm/ovo/ID/v1/billpay/get-denominations/' . $product_id, null, $this->_additionalHeader())->getResponse();
    }

    /**
     * Inquiry bill payment
     *
     * @param string $billerId
     * @param string $customerId
     * @param string $denomId
     * @param string $productId
     * @return \Svn\Response\InquiryResponse
     */
    public function inquiry($billerId, $customerId, $denomId, $productId)
    {
        $ch = new Curl;
        $data = [
            'biller_id'        => (string)$billerId,
            'customer_id'      => $customerId,
            'denomination_id'  => $denomId,
            'payment_method'   => ['001'],
            'phone_number'     => $customerId,
            'product_id'       => (string)$productId,
            'period'           => 0
        ];

        return $ch->post(OVOID::AWS . 'gpdm/ovo/ID/v1/billpay/inquiry', $data, $this->_additionalHeader())->getResponse();
    }

    /**
     * Unlock customer for payment
     *
     * @param string $securityCode
     * @return \Svn\Response\CustomerUnlockResponse
     */
    public function customerUnlock($securityCode)
    {
        $ch = new Curl;
        $data = [
            'appVersion'   => Meta::APP_VERSION,
            'securityCode' => $securityCode
        ];

        return $ch->post(OVOID::BASE_ENDPOINT . 'v1.0/api/auth/customer/unlock', $data, $this->_additionalHeader())->getResponse();
    }

    /**
     * Pay bill
     *
     * @param string $billerId
     * @param string $customerId
     * @param string $order_id
     * @param string $productId
     * @return \Svn\Response\PayResponse
     */
    public function pay($billerId, $customerId, $order_id, $productId)
    {
        $ch = new Curl;
        $data = [
            'biller_id'      => $billerId,
            'customer_id'    => $customerId,
            'order_id'       => $order_id,
            'payment_method' => ['001'],
            'phone_number'   => $customerId,
            'product_id'     => $productId
        ];

        return $ch->post(OVOID::AWS . 'gpdm/ovo/ID/v1/billpay/pay', $data, $this->_additionalHeader())->getResponse();
    }

    /**
     * Check payment status
     *
     * @param string $orderId
     * @return \Svn\Response\PayCheckStatusResponse
     */
    public function payCheckStatus($orderId)
    {
        $ch = new Curl;
        $data = ['order_reference' => $orderId];

        return $ch->post(OVOID::AWS . 'gpdm/ovo/ID/v1/billpay/checkstatus', $data, $this->_additionalHeader())->getResponse();
    }

    // ==================== AUTH (Legacy 2FA) ====================

    /**
     * Login 2FA (Legacy)
     *
     * @param string $mobile_phone
     * @return \Svn\Response\Login2FAResponse
     */
    public function login2FA($mobile_phone)
    {
        $ch = new Curl;
        $headers = [
            'OS'          => 'Android',
            'OS-Version'  => '11',
            'client-id'   => 'ovo_android',
            'device-id'   => $this->headers['device-id'],
            'app-id'      => Meta::APP_ID,
            'App-Version' => Meta::APP_VERSION
        ];

        $data = [
            'deviceId' => gen_uuid(),
            'mobile'   => $mobile_phone
        ];

        return $ch->post(OVOID::BASE_ENDPOINT . 'v2.0/api/auth/customer/login2FA', $data, $headers)->getResponse();
    }

    /**
     * Verify login 2FA
     *
     * @param string $refId
     * @param string $verificationCode
     * @param string $mobilePhone
     * @return \Svn\Response\Login2FAVerifyResponse
     */
    public function login2FAVerify($refId, $verificationCode, $mobilePhone)
    {
        $ch = new Curl;

        $data = [
            'appVersion'         => Meta::APP_VERSION,
            'deviceId'           => gen_uuid(),
            'macAddress'         => gen_mac(),
            'mobile'             => $mobilePhone,
            'osName'             => Meta::OS_NAME,
            'osVersion'          => Meta::OS_VERSION,
            'pushNotificationId' => 'FCM|f4OXYs_ZhuM:APA91bGde-ie2YBhmbALKPq94WjYex8gQDU2NMwJn_w9jYZx0emAFRGKHD2NojY6yh8ykpkcciPQpS0CBma-MxTEjaet-5I3T8u_YFWiKgyWoH7pHk7MXChBCBRwGRjMKIPdi3h0p2z7',
            'refId'              => $refId,
            'verificationCode'   => $verificationCode
        ];

        return $ch->post(OVOID::BASE_ENDPOINT . 'v2.0/api/auth/customer/login2FA/verify', $data, $this->headers)->getResponse();
    }

    /**
     * Login with security code (Legacy)
     *
     * @param string $securityCode
     * @param string $updateAccessToken
     * @return \Svn\Response\LoginSecurityCodeResponse
     */
    public function loginSecurityCode($securityCode, $updateAccessToken)
    {
        $ch = new Curl;
        $data = [
            'deviceUnixtime'     => time(),
            'securityCode'       => $securityCode,
            'updateAccessToken'  => $updateAccessToken,
            'message'            => ''
        ];

        return $ch->post(OVOID::BASE_ENDPOINT . 'v2.0/api/auth/customer/loginSecurityCode/verify', $data, $this->headers)->getResponse();
    }

    // ==================== LOGOUT ====================

    /**
     * Logout from OVO
     *
     * @return \Svn\Response\LogoutResponse
     */
    public function logout()
    {
        $ch = new Curl;
        return $ch->get(OVOID::BASE_ENDPOINT . 'v1.0/api/auth/customer/logout', null, $this->_additionalHeader())->getResponse();
    }
}