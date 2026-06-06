<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportComment extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'user_id',
        'report_type',
        'report_id',
        'comment',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
