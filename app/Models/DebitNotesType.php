<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitNotesType extends Model
{
    use HasFactory;

    protected $table = 'debit_notes_type';

    protected $fillable = [
        'branch_id',
        'transaction_type',
        'order_id',
        'purchase_id',
        'user_id',
        'create_note_id',
        'invoice_number',
        'grand_total',
        'remaning_amount',
        'total_paid',
        'settlement_amount',
        'reason',
        'total',
        'isDeleted',
        'client_uuid',
    ];
}
