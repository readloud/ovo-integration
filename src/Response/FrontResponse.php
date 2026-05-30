<?php
// Svn/Response/FrontResponse.php

namespace Svn\Response;

class FrontResponse extends BaseResponse
{
    public function getPaymentMethod()
    {
        return $this->data->paymentMethod ?? [];
    }
    
    public function getCardBalance($paymentMethod = 'OVO')
    {
        foreach ($this->data->paymentMethod ?? [] as $method) {
            if ($method->type === $paymentMethod) {
                return [
                    'balance' => $method->balance ?? 0,
                    'cash_balance' => $method->cashBalance ?? 0,
                    'points_balance' => $method->pointsBalance ?? 0,
                ];
            }
        }
        return null;
    }
    
    public function getCardNo($paymentMethod = 'OVO')
    {
        foreach ($this->data->paymentMethod ?? [] as $method) {
            if ($method->type === $paymentMethod) {
                return $method->cardNo ?? null;
            }
        }
        return null;
    }
    
    public function getAllBalances()
    {
        $balances = [];
        foreach ($this->data->paymentMethod ?? [] as $method) {
            $balances[] = [
                'type' => $method->type ?? null,
                'card_no' => $method->cardNo ?? null,
                'balance' => $method->balance ?? 0,
                'cash_balance' => $method->cashBalance ?? 0,
                'points_balance' => $method->pointsBalance ?? 0,
            ];
        }
        return $balances;
    }
}