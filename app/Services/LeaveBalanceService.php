<?php

namespace App\Services;

use App\Enums\LeaveTypeEnum;
use App\Models\Leave;

class LeaveBalanceService
{
    /**
     * Handle leave balance updates.
     * 
     * For ANNUAL/UNPAID leaves: Calculate new balance as previous_balance - new_days.
     * For other leave types: Copy balance values from the previous leave record.
     */
    public function updateLeaveBalance(Leave $leave): void
    {
        if ($this->isBalanceTrackingType($leave)) {
            $this->updateTrackingLeaveBalance($leave);
        } else {
            $this->copyPreviousLeaveBalance($leave);
        }
    }

    /**
     * Update balance for ANNUAL/UNPAID leave types.
     */
    private function updateTrackingLeaveBalance(Leave $leave): void
    {
        $previousLeave = $this->getPreviousLeaveOfType($leave);

        if ($previousLeave) {
            // Balance is previous balance minus new days
            $leave->leaves_balance = $previousLeave->leaves_balance - $leave->days;
        } else {
            // No previous record: subtract days from current balance
            $leave->leaves_balance -= $leave->days;
        }

        // Always update last_leave to current leave's created_at
        $leave->last_leave = $leave->created_at;

        $leave->saveQuietly();
    }

    /**
     * Copy balance values from previous leave record for non-tracking leave types.
     * Maintains leaves_balance, last_leave, leaves_majority, leaves_seniority, leaves_child.
     */
    private function copyPreviousLeaveBalance(Leave $leave): void
    {
        $previousLeave = $this->getPreviousLeave($leave);

        if ($previousLeave) {
            $leave->leaves_balance = $previousLeave->leaves_balance;
            $leave->last_leave = $previousLeave->last_leave;
            $leave->leaves_majority = $previousLeave->leaves_majority;
            $leave->leaves_seniority = $previousLeave->leaves_seniority;
            $leave->leaves_child = $previousLeave->leaves_child;
            $leave->saveQuietly();
        }
    }

    /**
     * Check if this leave type tracks balance.
     */
    private function isBalanceTrackingType(Leave $leave): bool
    {
        return $leave->type === LeaveTypeEnum::ANNUAL
            || $leave->type === LeaveTypeEnum::UNPAID;
    }

    /**
     * Get the most recent leave of the same type for this employee.
     */
    private function getPreviousLeaveOfType(Leave $leave): ?Leave
    {
        return Leave::query()
            ->where('employee_id', $leave->employee_id)
            ->where('type', $leave->type)
            ->where('id', '!=', $leave->id) // Exclude current leave
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Get the most recent leave for this employee (any type).
     */
    private function getPreviousLeave(Leave $leave): ?Leave
    {
        return Leave::query()
            ->where('employee_id', $leave->employee_id)
            ->where('id', '!=', $leave->id) // Exclude current leave
            ->orderByDesc('created_at')
            ->first();
    }
}
