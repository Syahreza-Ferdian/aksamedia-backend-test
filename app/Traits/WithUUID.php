<?php

namespace App\Traits;
use Illuminate\Support\Str;

trait WithUUID {

    protected static function bootWithUUID() {
        static::creating(function ($model) {
            if (empty($model->getKey())) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function getIncrementing() {
        return false;
    }

    public function getKeyType() {
        return 'string';
    }

}
