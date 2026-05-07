<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'order_id',
    'admin_id',
    'action_type',
    'old_value',
    'new_value',
    'comment',
])]
class OrderHistory extends Model
{
    //
}
