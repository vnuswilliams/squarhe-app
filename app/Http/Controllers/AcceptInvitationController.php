<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Notifications\InvitationAcceptedRecipientNotification;
use App\Notifications\InvitationAcceptedSenderNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AcceptInvitationController extends Controller
{
    public function __invoke(string $company_code, Invitation $invitation): RedirectResponse
    {

        
        // Vérifier que le company_code correspond bien à cette invitation
        if ($invitation->company_code !== $company_code) {
            abort(404);
        }

        if ($invitation->isExpired()) {
            return redirect()->route('dashboard')->with('error', __('This invitation link has expired.'));
        }

        if ($invitation->isAccepted()) {
            return redirect()->route('dashboard')->with('error', __('This invitation has already been used.'));
        }

        if (auth()->id() !== $invitation->recipient_id) {
            abort(403, __('This invitation does not belong to you.'));
        }

        if (auth()->user()->company_id !== null) {
            return redirect()->route('dashboard')->with('error', __('You already belong to a company.'));
        }
        DB::transaction(function () use ($invitation) {
            $invitation->recipient->update([
                'company_id' => $invitation->company_id,
            ]);
        
            $invitation->recipient->assignRole($invitation->role);
        
            $invitation->update(['accepted_at' => now()]);
        });
        
        // Notifier les deux parties
        $invitation->sender->notify(new InvitationAcceptedSenderNotification($invitation));
        $invitation->recipient->notify(new InvitationAcceptedRecipientNotification($invitation));
        
        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                __('You have successfully joined :company!', [
                    'company' => $invitation->company->name,
                ]),
            );
    }
}
