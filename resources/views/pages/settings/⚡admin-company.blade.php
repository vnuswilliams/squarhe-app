<?php

use App\Enums\{CompanyRoleEnum,StatusEnum};
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use App\Models\{User, Invitation};
use App\Notifications\CompanyInvitationNotification;
use Illuminate\Validation\Rule;
use Livewire\WithPagination;
use Livewire\Component;

new #[Title('Manager les admins de votre entreprise')] class extends Component {
    use WithPagination;

    public string $email = '';
    public string $role = '';
    public array $editingRoles = [];
    public function mount(): void
    {
        if (!$this->company) {
            Flux::toast(variant: 'success', text: __('toast.createCompany'));
            $this->redirect(route('settings.company.add'), navigate: true);
        }
    }
    #[Computed]
    public function company()
    {
        return auth()->user()->company;
    }
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'role' => ['required', 'string', Rule::notIn([CompanyRoleEnum::OWNER->value])],
            'editingRoles.*' => ['required', 'string', Rule::notIn([CompanyRoleEnum::OWNER->value])],
        ];
    }

    public function messages(): array
    {
        return [
            'role.not_in' => __('You cannot assign the Owner role.'),
            'editingRoles.*.not_in' => __('You cannot assign the Owner role.'),
        ];
    }

   
    public function sendInvitation(): void
    {
        $this->validateOnly('email');
        $this->validateOnly('role');

        $recipient = User::where('email', $this->email)->first();

        if (!$recipient) {
            Flux::toast(text: __('No account found for this email address.'), variant: 'danger');
            return;
        }

        if ($recipient->company_id !== null) {
            Flux::toast(text: __('This user already belongs to a company.'), variant: 'warning');
            return;
        }

        $sender = auth()->user();

        // Supprimer les invitations en attente déjà envoyées à ce destinataire
        Invitation::where('recipient_id', $recipient->id)->where('company_id', $this->company->id)->whereNull('accepted_at')->where('expires_at', '>', now())->delete();

        $invitation = Invitation::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'company_id' => $this->company->id,
            'company_code' => $this->company->company_code,
            'role' => $this->role,
            'expires_at' => now()->addHours(),
        ]);

        $recipient->notify(new CompanyInvitationNotification($invitation));

        Flux::toast(text: __('Invitation sent successfully.'), variant: 'success');

        $this->reset('email', 'role');
    }

    public function updateRole(int $userId): void
    {
        $this->validateOnly("editingRoles.{$userId}");

        $user = User::where('id', $userId)->where('company_id', $this->company->id)->firstOrFail();

        if ($user->hasRole(CompanyRoleEnum::OWNER->value)) {
            Flux::toast(text: __('You cannot change the Owner role.'), variant: 'danger');
            return;
        }

        // Remplace l'ancien rôle par le nouveau
        $user->syncRoles([$this->editingRoles[$userId]]);

        Flux::toast(text: __('Role updated successfully.'), variant: 'success');
    }

    public function removeFromCompany(int $userId): void
    {
        $user = User::where('id', $userId)->where('company_id', $this->company->id)->firstOrFail();

        if ($user->hasRole(CompanyRoleEnum::OWNER->value)) {
            Flux::toast(text: __('You cannot remove the Owner from the company.'), variant: 'danger');
            return;
        }

        DB::transaction(function () use ($user) {
            $user->syncRoles([]); // révoque tous les rôles Spatie
            $user->update(['company_id' => null]);
        });

        Flux::toast(text: __('User removed from company.'), variant: 'success');
    }
    public function with(): array
    {
        $members = User::where('company_id', $this->company->id)
            ->where('id', '!=', auth()->id())            
            ->paginate(10, pageName: 'mem_page');

        foreach ($members as $member) {
            if (!isset($this->editingRoles[$member->id])) {
                $this->editingRoles[$member->id] = $member->getRoleNames()->first() ?? '';
            }
        }

        $invitations = Invitation::with('recipient')
            ->where('sender_id', auth()->id())
            ->latest()
            ->paginate(10, pageName: 'inv_page');

        return compact('members', 'invitations');
    }
}; ?>
<section class="w-full">
    @include('partials.settings-heading')
    <x-settings.layout :heading="__('setting.settingaddheading')" :subheading="__('setting.settingaddsubheading')">
        <div class="space-y-10">

            {{-- ─── FORMULAIRE ───────────────────────────────────────────── --}}
            <div>
                <flux:heading size="lg" class="mb-4">{{ __('Invite a user') }}</flux:heading>

                <form wire:submit="sendInvitation" class="space-y-5">
                    <div>
                        <flux:input :label="__('Email address')" wire:model="email" type="email"
                            :placeholder="__('ex: user@company.com')" class="w-full" />
                        @error('email')
                            <flux:text class="text-xs text-red-500 mt-1">{{ $message }}</flux:text>
                        @enderror
                    </div>

                    <div>
                        <flux:select :label="__('Role to assign')" wire:model="role">
                            <flux:select.option value="">{{ __('Choose a role') }}</flux:select.option>
                            @foreach (CompanyRoleEnum::cases() as $rol)
                                @if ($rol !== CompanyRoleEnum::OWNER)
                                    <flux:select.option value="{{ $rol->value }}">
                                        {{ $rol->value }}
                                    </flux:select.option>
                                @endif
                            @endforeach
                        </flux:select>
                        @error('role')
                            <flux:text class="text-xs text-red-500 mt-1">{{ $message }}</flux:text>
                        @enderror
                    </div>

                    <flux:button variant="primary" type="submit" class="w-full">
                        {{ __('Send invitation') }}
                    </flux:button>
                </form>
            </div>

            <x-tabs :tabs="['Membre de la compagnie', 'Status des invitations']">
                <x-slot:tab1>

                    {{-- ─── MEMBRES DE LA COMPAGNIE ──────────────────────────────── --}}
                    <div>
                        <flux:heading size="sm" class="mb-3">{{ __('Company members') }}</flux:heading>

                        @if ($members->isEmpty())
                            <flux:text class="text-sm text-zinc-400">{{ __('No members yet.') }}</flux:text>
                        @else
                            <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700 text-sm">
                                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-medium text-zinc-500">
                                                {{ __('Member') }}
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium text-zinc-500">
                                                {{ __('Role') }}
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium text-zinc-500">
                                                {{ __('Joined on') }}
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium text-zinc-500">
                                                {{ __('Actions') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-zinc-100 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                                        @foreach ($members as $member)
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <div class="font-medium text-zinc-800 dark:text-zinc-100">
                                                        {{ $member->name }}</div>
                                                    <div class="text-xs text-zinc-400">{{ $member->email }}</div>
                                                </td>

                                                <td class="px-4 py-3">
                                                    @if ($member->role === CompanyRoleEnum::OWNER->value)
                                                        <flux:badge variant="pill" color="purple">owner</flux:badge>
                                                    @else
                                                        <flux:select wire:model="editingRoles.{{ $member->id }}"
                                                            wire:change="updateRole({{ $member->id }})"
                                                            class="text-sm">
                                                            @foreach (CompanyRoleEnum::cases() as $rol)
                                                                @if ($rol !== CompanyRoleEnum::OWNER)
                                                                    <flux:select.option value="{{ $rol->value }}">
                                                                        {{ $rol->value }}
                                                                    </flux:select.option>
                                                                @endif
                                                            @endforeach
                                                        </flux:select>
                                                        @error("editingRoles.{$member->id}")
                                                            <flux:text class="text-xs text-red-500 mt-1">
                                                                {{ $message }}
                                                            </flux:text>
                                                        @enderror
                                                    @endif
                                                </td>

                                                <td class="px-4 py-3 text-zinc-500">
                                                    {{ $member->updated_at->format('d M Y') }}
                                                </td>

                                                <td class="px-4 py-3">
                                                    @if ($member->role !== CompanyRoleEnum::OWNER->value)
                                                        <flux:button variant="danger" size="sm"
                                                            wire:click="removeFromCompany({{ $member->id }})"
                                                            wire:confirm="{{ __('Remove this user from the company?') }}">
                                                            {{ __('Remove') }}
                                                        </flux:button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $members->links() }}</div>
                        @endif
                    </div>
                </x-slot:tab1>

                <x-slot:tab2>
                    {{-- ─── INVITATIONS ENVOYÉES ──────────────────────────────────── --}}
                    <div>
                        <flux:heading size="sm" class="mb-3">{{ __('Sent invitations') }}</flux:heading>

                        @if ($invitations->isEmpty())
                            <flux:text class="text-sm text-zinc-400">{{ __('No invitations sent yet.') }}</flux:text>
                        @else
                            <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700 text-sm">
                                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-medium text-zinc-500">
                                                {{ __('Recipient') }}
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium text-zinc-500">
                                                {{ __('Role') }}
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium text-zinc-500">
                                                {{ __('Sent on') }}
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium text-zinc-500">
                                                {{ __('Expires on') }}
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium text-zinc-500">
                                                {{ __('Status') }}
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium text-zinc-500">
                                                {{ __('Accepted on') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-zinc-100 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                                        @foreach ($invitations as $invitation)
                                            @php $status = $invitation->status() @endphp
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <div class="font-medium text-zinc-800 dark:text-zinc-100">
                                                        {{ $invitation->recipient->name }}
                                                    </div>
                                                    <div class="text-xs text-zinc-400">
                                                        {{ $invitation->recipient->email }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                                    {{ $invitation->role }}
                                                </td>
                                                <td class="px-4 py-3 text-zinc-500">
                                                    {{ $invitation->created_at->format('d M Y, H:i') }}</td>
                                                <td class="px-4 py-3 text-zinc-500">
                                                    {{ $invitation->expires_at->format('d M Y, H:i') }}</td>
                                                <td class="px-4 py-3">
                                                    <flux:badge variant="pill"
                                                        color="{{ match ($status) {
                                                            StatusEnum::APPROVED->value => 'green',
                                                            StatusEnum::REJECTED->value => 'red',
                                                            StatusEnum::PENDING->value => 'yellow',
                                                        } }}">
                                                        {{ __($status) }}
                                                    </flux:badge>
                                                </td>
                                                <td class="px-4 py-3 text-zinc-500">
                                                    {{ $invitation->accepted_at?->format('d M Y, H:i') ?? '—' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $invitations->links() }}</div>
                        @endif
                    </div>
                </x-slot:tab2>
            </x-tabs>
        </div>
    </x-settings.layout>


</section>
