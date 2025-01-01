<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeModel extends Model
{
    use HasFactory;

    protected $table = 'employee';
    protected $keyType = 'string';
    public $incrementing = false;

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
