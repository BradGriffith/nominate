<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Events\PositionEvent;
use App\Events\ResultsEvent;

class Position extends Model
{
    use HasFactory;

    public $fillable = [
        'name',
        'is_default',
        'status',
    ];

    public static function getDefault() {
        return static::where('is_default', 1)->first();
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(function ($model) {
            event(new PositionEvent($model));
            event(new ResultsEvent());
        });
    }
}
