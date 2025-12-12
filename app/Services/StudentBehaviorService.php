<?php

namespace App\Services;

use App\Models\StudentBehavior;
use App\Models\StudentBehaviorsPointAction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StudentBehaviorService
{
    public function addPointAction(
        StudentBehavior $behavior,
        int $reasonId,
        int $value,
        string $actionType,
        string $note = null
    ): StudentBehaviorsPointAction {
        return $behavior->pointActions()->create([
            'reason_id' => $reasonId,
            'value' => $value,
            'action_type' => $actionType,
            'note' => $note,
            'created_by' => Auth::id(),
            'canceled' => false,
        ]);
    }

    public function cancelPointAction(StudentBehaviorsPointAction $action, string $cancelReason = null): StudentBehaviorsPointAction
    {
        $action->update([
            'canceled' => true,
            'canceled_by' => Auth::id(),
            'canceled_at' => Carbon::now(),
            'cancel_reason' => $cancelReason,
        ]);

        return $action;
    }
}
