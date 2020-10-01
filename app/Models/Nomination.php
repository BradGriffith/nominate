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
}
