<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PersistenceException
 *
 * @package App\Exceptions
 */
class PersistenceException extends \RuntimeException
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }
}