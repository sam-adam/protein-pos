<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Class BaseModel
 *
 * @package App\Models
 */
abstract class BaseModel extends Model
{
    /** {@inheritDoc} */
    protected static function boot()
    {
        parent::boot();

        self::saving(function (BaseModel $model) {
            if (!$model->created_by) {
                $creator = Auth::user();

                if (!$creator) {
                    throw new \Exception('Creator required');
                }

                $model->created_by = $creator->id;
                $model->saveOrFail();
            }
        });

        self::updating(function (BaseModel $model) {
            if (!$model->updated_by) {
                $updater = Auth::user();

                if (!$updater) {
                    throw new \Exception('Updater required');
                }

                $model->updated_by = $updater->id;
                $model->saveOrFail();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}