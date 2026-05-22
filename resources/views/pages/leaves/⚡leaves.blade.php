<?php

use App\Enums\StatusEnum;
use App\Livewire\Forms\EmployeeLeaveForm;
use App\Models\Employee;
use App\Services\CalculateDays;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Enums\LeaveTypeEnum;

new #[Title('Absences et congés')] class extends Component
{

    public EmployeeLeaveForm $form;
    public array $ids = [];
public function mount()
{
    $this->ids = $this->employees->pluck('id')->toArray();
}
   

    #[Computed]
    public function company()
    {
        return auth()->user()->company()->with('employees')->first();
    }

    #[Computed]
    public function employees()
    {

        return Employee::whereCompanyId(auth()->user()?->company_id)->get(['id', 'name']);
    }

   

    public function save()
    {
        $this->form->status = StatusEnum::APPROVED->value;
        $this->form->days = (int) app(CalculateDays::class)->calculateDays($this->form->start_date, $this->form->end_date);
        $this->form->create();
        $this->showAddLeavesForm = false;
        Flux::toast(variant: 'success', text: __("L' absences ou le congé a été ajouté été ajouté avec  succès."));
        $this->form->reset();
    }

    public bool $showAddLeavesForm = false;

    public function toggleFormAddLeaves()
    {
        $this->showAddLeavesForm = ! $this->showAddLeavesForm;
    }
};
?>

<div>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <flux:heading size="xl"> Absences et congés </flux:heading>
            <flux:text variant="subtle">
                 Visualisez, approuvez et suivez la clôture de votre paie. 
            </flux:text>
        </div>
        <div class="flex items-center justify-end gap-2">
        
            <flux:button variant="primary" icon="plus" tooltip="{{ __('Ajouter des absences et congés') }}" wire:navigate wire:click="toggleFormAddLeaves" />
            <flux:button icon="arrow-down-tray" tooltip="{{ __('Importer des absences et congés') }}" square href="{{ route('employees.import.leaves') }}" wire:navigate />
            
        </div>
    </div>
    {{-- ─── FORM : ADD LEAVE ─── --}}
    @if($showAddLeavesForm)
        <x-container wire:transition>
            <flux:heading level="1" size="lg" class="mb-5">{{ __("Ajouter une absence ou un congé") }}        </flux:heading>
            <form wire:submit="save">
                <div class="py-4 grid grid-cols-1 md:grid-cols-3 gap-4">

                    <flux:select wire:model="form.employee_id" label="Ajouter une absencse ou congés à ?">
                        <option value="">Choisir un collaborateur</option>
                        @foreach ($this->employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->shortName }}</option>
                        @endforeach

                    </flux:select>
                    <flux:select label="{{ __('Type d\'absence') }}" wire:model.live="form.type">
                        <option>{{ __("Choisir un type") }}</option>
                        @foreach (LeaveTypeEnum::options() as $case)
                            <option value="{{ $case['value'] }}">{{ $case["label"] }}</option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="form.approved_by" label="{{ __('Approuvé par') }}" />

                    @if (in_array($form->type, [LeaveTypeEnum::ANNUAL->value]))
                        <flux:input wire:model="form.last_leave" type="date"
                            label="{{ __('Date du dernier congé annuel (optionnel)') }}" />
                    @endif

                    <flux:input wire:model="form.start_date" type="date" label="{{ __('Date de début') }}" />
                    <flux:input wire:model="form.end_date" type="date" label="{{ __('Date de fin') }}" />
                </div>

                <div class="md:col-span-2">
                    <flux:textarea label="{{ __('Notes') }}" wire:model="form.notes"
                        placeholder="{{ __('Motif du congé, détails...') }}" />
                </div>

                <div class="flex items-center justify-end gap-2 mt-4">
                    <flux:button wire:click="toggleFormAddLeaves" >{{ __("Cancel") }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __("Enregistrer") }}</flux:button>
                </div>
            </form>
        </x-container>
    @endif

    @if($this->company)
    {{-- ===================== TIMELINE PROCESSUS PAIE ===================== --}}
    <x-delta-card :cards='[
            [
                "label" => "Bulletin générés et validés",
                "current" =>  $this->company->employees->where("status", "!=", StatusEnum::TERMINATED->value)->count(),
                "delta" => "",
                "color" => "blue",
            ],
            [
                "label" => "Livre de paie ",
                "current" =>  $this->company->employees->where("status", "!=", StatusEnum::TERMINATED->value)->count(),
                "delta" => "",
                "color" => "blue",
            ],
            [
                "label" => "Effectif total",
                "current" =>  $this->company->employees->where("status", "!=", StatusEnum::TERMINATED->value)->count(),
                "delta" => "",
                "color" => "blue",
            ],
            [
                "label" => "Effectif total",
                "current" =>  $this->company->employees->where("status", "!=", StatusEnum::TERMINATED->value)->count(),
                "delta" => "",
                "color" => "blue",
            ],
            ]'
            />
    <x-ui.tabs variant="non-contained">
        <x-ui.tab.group>
            <x-ui.tab label="Vue d'ensemble" icon="globe-alt" />
            <x-ui.tab label="Tous les absences" icon="clock" />
            <x-ui.tab label="Archives" icon="document" />
        </x-ui.tab.group>
        <x-ui.tab.panel>
            <livewire:leaves.leaves-general :company="$this->company" />
        </x-ui.tab.panel>
        <x-ui.tab.panel>
            <livewire:leaves.leaves-all :ids="$ids" :employees="$this->employees" />
            </x-ui.tab.panel>
        <x-ui.tab.panel>
            <livewire:leaves.leaves-archive :ids="$ids" :employees="$this->employees" />
        </x-ui.tab.panel>
    </x-ui.tabs>


@else
    <x-no-company />
    @endif
</div>
