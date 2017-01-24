<?php

namespace App\Services;

use App\Exceptions\SuspendedShiftException;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class ShiftService
 *
 * @package App\Services
 */
class ShiftService
{
    public function openShift(User $user, $openingBalance)
    {
        if ($suspendedShift = Shift::inBranch($user->branch)->where('opened_by_user_id', $user->id)->suspended()->first()) {
            throw new SuspendedShiftException($suspendedShift);
        }

        $newShift                      = new Shift();
        $newShift->branch_id           = $user->branch_id;
        $newShift->opened_at           = Carbon::now();
        $newShift->opened_by_user_id   = $user->id;
        $newShift->opened_cash_balance = $openingBalance;
        $newShift->saveOrFail();

        return $newShift;
    }

    public function closeShift(Shift $shift, User $closedBy, $closingBalance, $remark = null)
    {
        if ($shift->isClosed()) {
            return $shift;
        }

        $shift->closed_at           = Carbon::now();
        $shift->closed_by_user_id   = $closedBy->id;
        $shift->closed_cash_balance = $closingBalance;
        $shift->remark              = $remark;
        $shift->saveOrFail();

        return $shift;
    }
}