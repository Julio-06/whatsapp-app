<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    //RELACION UNO A MUCHOS
    public function messages(){
        return $this->hasMany(Message::class);
    }

    //RELACIÓN MUCHOS A MUCHOS
    public function users(){
        return $this->belongsToMany(User::class);
    }
}
