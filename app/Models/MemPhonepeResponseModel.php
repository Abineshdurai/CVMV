<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemPhonepeResponseModel extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'mem_phonepe_response';

    protected $fillable = [
        'success', 
        'code', 
        'message', 
        'merchant_id', 
        'merchant_transaction_id', 
        'transaction_id', 
        'amount', 
        'state', 
        'response_code', 
        'type', 
        'utr', 
        'card_type', 
        'pg_transation_id', 
        'bank_transaction_id', 
        'pg_authorization_code', 
        'arn', 
        'bank_id', 
        'pg_service_transaction_id',
        'status',
        'created_at', 
        'updated_at'
    ];
    protected $casts = [
        'success' => 'boolean',
    ];
}
