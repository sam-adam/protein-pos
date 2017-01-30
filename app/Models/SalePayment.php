<?php

namespace App\Models;

/**
 * Class SalePayment
 *
 * @package App\Models
 */
class SalePayment extends BaseModel
{
    const PAYMENT_METHOD_CASH = 'CASH';
    const PAYMENT_METHOD_CREDIT_CARD = 'CREDIT_CARD';

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function calculateTotal()
    {
        return $this->sale->calculateTotal() * (100 - $this->card_tax) / 100;
    }

    public function getNetPaid()
    {
        return $this->amount * (100 - $this->card_tax) / 100;
    }

    public function getChange()
    {
        return $this->payment_method === self::PAYMENT_METHOD_CREDIT_CARD
            ? 0
            : $this->amount - $this->calculateTotal();
    }
}