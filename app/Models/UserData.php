<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserData extends Model
{
    use HasFactory;

    protected $table = 'user_data';

    protected $fillable = [
        'nombre',
        'apellido',
        'sexo',
        'email',
        'fecha_nac',
        'telefono',
        'ruta_foto',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
