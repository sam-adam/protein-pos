<?php

namespace App\DataObjects;

use App\Models\Branch as BranchModel;

/**
 * Class Branch
 *
 * @package App\DataObjects
 */
class Branch extends ModelDataObjects
{
    public function __construct(BranchModel $branch)
    {
        $this->setModel($branch);
        $this->guarded = array_merge($this->guarded, ['licensed_at', 'activated_at']);
    }
}