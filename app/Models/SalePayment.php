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
}