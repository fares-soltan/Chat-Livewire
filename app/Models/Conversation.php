<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['receiver_id','sender_id'];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }


}
