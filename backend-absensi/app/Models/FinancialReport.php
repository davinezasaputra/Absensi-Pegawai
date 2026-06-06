<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialReport extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'user_id',
        'report_date',
        'amount',
        'description',
    ];
    public function user()
    {
        return $this->belongsTo(User::class); 
}
}