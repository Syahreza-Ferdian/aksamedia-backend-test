<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\WithUUID;

class DivisiModel extends Model
{
    use HasFactory;
    use WithUUID;

    protected $table = 'divisi';

    protected $fillable = [
        'name'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function employee() {
        return $this->hasMany(EmployeeModel::class, 'divisi_id');
    }
}
