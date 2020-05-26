<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceStatus extends Model
{
    protected $table = 'invoice_statuses';
    protected $primaryKey = 'status_id';
    protected $guarded = [];
}
