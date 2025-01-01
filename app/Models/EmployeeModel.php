<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\WithUUID;

class EmployeeModel extends Model
{
    use HasFactory, WithUUID;

    protected $table = 'employee';

    protected $fillable = [
        'name',
        'image',
        'phone',
        'position',
        'divisi_id'
    ];

    public function divisi() {
        return $this->belongsTo(DivisiModel::class, 'divisi_id');
    }
}
