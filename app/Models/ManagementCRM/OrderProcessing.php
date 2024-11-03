<?php

namespace App\Models\ManagementCRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class OrderProcessing extends Model
{
    use HasFactory, Notifiable, SoftDeletes; 
}
