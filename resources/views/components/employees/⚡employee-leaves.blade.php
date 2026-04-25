<?php

use App\Enums\LeaveTypeEnum;
use App\Enums\StatusEnum;
use App\Livewire\Forms\EmployeeLeaveForm;
use App\Models\Leave;
use App\Services\CalculateDays;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public $employee;
    public $showImportLeave = false;
    public $showAddLeaveForm = false;
    public $editingLeaveId;


    public $previewData = [];
    public $importErrors = [];
    public $importFile;
    public $validationStep = false;
    public EmployeeLeaveForm $form;
    public function mount($employee)
    {
        $this->employee = $employee;
    }
    #[Computed]
    public function leaves()
    {
        return $this->employee->leaves;
    }

    public function save()
    {
        $this->form->employee_id = $this->employee->id;
        $this->form->status = StatusEnum::APPROVED->value;

        $this->form->days = (new CalculateDays)->calculateDays($this->form->start_date, $this->form->end_date);
        $this->form->create();

        $this->form->reset();
        $this->showAddLeaveForm = false;
        Flux::toast(variant: 'success', text: 'Votre absences ou congés a été ajouté avec succès.');
    }

    public function edit($leaveId)
    {
        $leaveToUpdate  = Leave::whereId($leaveId)  
        ->whereEmployeeId($this->employee->id)
            ->firstOrFail();
        $this->form->setLeave($leaveToUpdate);
        Flux::modal('edit-leave-modal')->show();
    }


    public function update()
    {
        $this->form->days = (new CalculateDays)->calculateDays($this->form->start_date, $this->form->end_date);
        $this->form->update();
        $this->form->reset();
        Flux::modal('edit-leave-modal')->close();
        Flux::toast(variant: 'success', text: 'Votre absence ou congé a été mis à jour avec succès.');
    }

    public $leaveToDelete = null;
    public function confirmBeforeDelete($idLeaveWeWantToDelete)
    {
        $this->leaveToDelete = Leave::whereId($idLeaveWeWantToDelete)
            ->whereEmployeeId($this->employee->id)
            ->firstOrFail();
        Flux::modal('delete-leave-modal')->show();
    }
    public function delete()
    {
        if ($this->leaveToDelete):
            Gate::authorize('delete', [Leave::class, $this->leaveToDelete]);
            $this->leaveToDelete->delete();
            Flux::toast(variant: 'success', text: 'Votre absence ou congé a été supprimé avec succès.');
            Flux::modal('delete-leave-modal')->close();
            $this->leaveToDelete = null;
        endif;
    }

    public function toggleFormAddLeave()
    {
        $this->showAddLeaveForm = !$this->showAddLeaveForm;
        $this->showImportLeave = false;
    }


    public function toggleImportLeave()
    {
        $this->showImportLeave = !$this->showImportLeave;
        $this->showAddLeaveForm = false;
    }

};
?>

<div>
    <div class="flex items-center justify-between">
        <div>
            <flux:heading level="1" class="font-bold">{{ __('Gérer les congés et absences') }}</flux:heading>
            <flux:text class="text-gray-300">{{ __('Consultez le solde et enregistrez les absences de votre collaborateur.') }}</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:button wire:click="toggleFormAddLeave" variant="primary">
                {{ __('Ajouter une absence') }}
            </flux:button>


            <flux:dropdown>
                <flux:button icon="bars-3" />
                <flux:menu>

                    <flux:menu.item wire:click="toggleImportLeave">
                        {{ __('Importer des absences et congés') }}
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    @if($showAddLeaveForm)
    <x-container wire:transition>
        <form wire:submit="save">
            <div class="py-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:select label="{{ __('Type d\'absence') }}" wire:model.live="form.type">
                    <option>{{ __('Choisir un type') }}</option>
                    @foreach (LeaveTypeEnum::options() as $case)
                    <option value="{{ $case['value'] }}">
                        {{ $case['label'] }}
                    </option>
                    @endforeach
                </flux:select>
                @if(in_array($form->type, [LeaveTypeEnum::ANNUAL->value]))
                <flux:input wire:model="form.last_leave" type="date" label="Date du dernier congé annuel (optionnel) "></flux:input>
                @endif
                <flux:input wire:model="form.start_date" type="date" label="Date de début"></flux:input>
                <flux:input wire:model="form.end_date" type="date" label="Date de fin"></flux:input>
            </div>
            <div class="md:col-span-2">
                <flux:textarea label="{{ __('Notes') }}" wire:model="form.notes"
                    placeholder="{{ __('Motif du congé, détails...') }}"> </flux:textarea>
            </div>


            <div class="flex items-center justify-end gap-2 mt-4">

                <flux:button wire:click="toggleFormAddLeave">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Enregistrer') }}
                </flux:button>
            </div>
        </form>
    </x-container>
    @endif
    

    <x-container>
        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-700">
                <tr>

                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Type') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Date de début') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Date de fin') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Jours') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Statut') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Actions') }}
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse ($this->leaves as $leave)
                <tr wire:key="{{ $leave->id }}">

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        <flux:heading class="flex items-center gap-2">
                            {{ $leave->type->label() }}
                            <flux:tooltip toggleable>
                                <flux:button icon="information-circle" size="sm" variant="ghost" />
                                <flux:tooltip.content>
                                    {{ $leave->notes }}
                                </flux:tooltip.content>
                            </flux:tooltip>

                        </flux:heading>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ Carbon::parse($leave->start_date)->translatedFormat('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ Carbon::parse($leave->end_date)->translatedFormat('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $leave->days }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        <flux:badge color="{{ $leave->status->color() }}">{{ $leave->status->label() }}</flux:badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <flux:button wire:click="edit({{ $leave->id }})" size="sm" variant="ghost" icon="pencil" />


                            <flux:button wire:click="confirmBeforeDelete({{ $leave->id }})"
                                size="sm"
                                variant="ghost" icon="trash" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-8">
                        <x-empty-state message=" 
                    {{ __('Aucune absences ou congés trouvés.') }}" />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </x-container>


    <flux:modal name="edit-leave-modal" class="min-w-225">
        <div class="space-y-6 pt-5">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Mettre à jour un congé ou une absence</flux:heading>
            </div>
            <form wire:submit="update" class="container mx-auto p-4 max-w-4xl space-y-4">
                <div class="grid sm:grid-cols-3 gap-4">
                    <flux:select label="{{ __('Type de congé') }}" wire:model="form.type">
                        <option>{{ __('Choisir un type') }}</option>
                        @foreach (LeaveTypeEnum::options() as $case)
                        <option value="{{ $case['value'] }}">
                            {{ $case['label'] }}
                        </option>
                        @endforeach
                    </flux:select>

                    @if(in_array($form->type, [LeaveTypeEnum::ANNUAL->value]))
                    <flux:input wire:model="form.last_leave" type="date" label="Date du dernier congé annuel (optionnel) "></flux:input>
                    @endif
                    <flux:input type="date" label="Date de début" wire:model="form.start_date" />
                    <flux:input type="date" label="Date de fin" wire:model="form.end_date" />
                </div>
                <flux:textarea label="Notes" wire:model="form.notes" />


                <div class="flex justify-end gap-2  pt-4">
                    <flux:button type="submit" variant="primary">Enregistrer</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
    <flux:modal name="delete-leave-modal">
        <div class="space-y-6 pt-5">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Supprimer ce congé ou absence</flux:heading>
            </div>
            @if($leaveToDelete)
            <p>
                Voulez vous vraiment supprimer {{$leaveToDelete->type->label()}} allant du {{ $leaveToDelete->start_date?->translatedFormat('d M Y') }} au {{ $leaveToDelete->end_date?->translatedFormat('d M Y') }} ?
            </p>
            <p>Cette action est irréversiblee.</p>
            @endif

            <div class="flex justify-end gap-2  pt-4">
                <flux:modal.close>
                    <flux:button>Annuler</flux:button>

                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">Oui, j'en suis sûr</flux:button>
            </div>
        </div>
    </flux:modal>
</div>