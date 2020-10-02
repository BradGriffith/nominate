<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nomination extends Model
{
    use HasFactory;

    public function getNameAttribute() {
      return $this->last_name . ', ' . $this->first_name;
    }

    public $appends = [
      'name',
    ];

    public function votes() {
      return $this->hasMany('App\Models\Vote');
    }

    public function ranks() {
      return $this->hasMany('App\Models\Ranking');
    }
}
