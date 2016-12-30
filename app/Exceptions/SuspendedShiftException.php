<?php

namespace App\Exceptions;

use App\Models\Shift;

/**
 * Class SuspendedShiftException
 *
 * @package App\Exceptions
 */
class SuspendedShiftException extends \RuntimeException
{
    protected $shift;

    public function __construct(Shift $shift)
    {
        $this->shift = $shift;
    }

    public function getShift()
    {
        return $this->shift;
    }
}