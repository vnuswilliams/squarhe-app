<?php

use App\Enums\LawEnum;
use App\Enums\PaymentEnum;
use App\Livewire\Forms\SettingsCompanyForm;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Modifier les paramètres de la société')]  class extends Component
{

//TODO REvoir le systeme de modifications des données des entreprise 
    public $company;
    public SettingsCompanyForm $form;
    public array $fixedHolidays = [];
    public array $defaultFixedHolidays = [];
    public $labourHoursOptions;

    public function mount()
    {
        $this->company = auth()->user()->company;

        if (!$this->company) :
            $this->redirect(route('settings.company.add'), navigate: true);
        endif;

        $this->form->setCompany($this->company);
        $this->defaultFixedHolidays = config('squarhe.fixedHolidays', []);
        $companyFixedHolidays = collect($this->company->data['fixedHolidays'] ?? [])
            ->map(fn ($date) => date('Y-m-d', strtotime($date)))
            ->toArray();

        $this->fixedHolidays = array_values(array_unique(array_merge(
            array_map(fn($date) => date('Y-m-d', strtotime($date)), $this->defaultFixedHolidays),
            $companyFixedHolidays
        )));

        $this->labourHoursOptions = config('squarhe.settingsCompany.labourHours', []);
    }

    public function save()
    {
        $this->form->save();

        Flux::toast(variant: 'success', text: 'Vous avez mis à jour les paramètres de la compagnie');
    }

    public function addHoliday()
    {
        $this->fixedHolidays[] = date('Y-m-d');
    }

    public function updatedFixedHolidays()
    {
        $this->saveHolidays();
    }

    public function removeHoliday($index)
    {
        $holidayToRemove = date('m-d', strtotime($this->fixedHolidays[$index]));
        if (in_array($holidayToRemove, $this->defaultFixedHolidays)) {
            Flux::toast(variant: 'danger', text: 'Vous ne pouvez pas supprimer un jour férié par défaut.');
            return;
        }

        unset($this->fixedHolidays[$index]);
        $this->fixedHolidays = array_values($this->fixedHolidays);
        $this->saveHolidays();
    }

    private function saveHolidays()
    {
        $this->validate([
            'fixedHolidays' => 'array',
            'fixedHolidays.*' => 'required|date'
        ]);

        $settings = $this->company->data ?? [];
        
        $holidaysToSave = collect($this->fixedHolidays)
            ->map(fn ($date) => date('m-d', strtotime($date)))
            ->filter(fn ($date) => !in_array($date, $this->defaultFixedHolidays))
            ->unique()
            ->values()
            ->toArray();

        $settings['fixedHolidays'] = $holidaysToSave;
        $this->company->data = $settings;
        $this->company->save(); // Use save() on the model instance, not on $this->company->data
        Flux::toast('Vous avez mis à jour les jours fériés');
    }
};
?>


<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('settings.settings.company.title')" :subheading="__('Changer les paramètres de votre compagnie')">
        <div>
            <form wire:submit.prevent="save" class="space-y-6">
                {{-- Tax & Charges Configuration --}}
                <div>
                     <flux:heading>Configuration Fiscale & Sociale</flux:heading>
                     <p class="text-sm text-gray-500 mb-4">Activez ou désactivez les éléments fiscaux applicables à votre société.</p>
                     
                     <div class="space-y-4">
                        <flux:switch :label="__('RAV (Redevance Audio Visuel)')" wire:model.live="form.rav" />
                        <flux:switch :label="__('TDL (Taxe de Développement Local)')" wire:model.live="form.tdl" />
                        <flux:switch :label="__('IRPP (Impôt sur le Revenu)')" wire:model.live="form.irpp" />
                     </div>
                </div>

                <flux:separator />

                {{-- Holidays --}}
                <div>
                    <div>
                        <flux:heading>Jours Fériés</flux:heading>
                        <p class="text-sm text-gray-500 mb-4">Gérez les jours fériés de votre entreprise.</p>
                    </div>
                    <div class="space-y-4 mt-2">
                        @foreach($fixedHolidays as $index => $holiday)
                            <div class="flex items-center gap-2">
                                <flux:input type="date" wire:model.blur="fixedHolidays.{{ $index }}" />
                                <flux:button variant="danger" icon="trash" wire:click="removeHoliday({{ $index }})" />
                            </div>
                        @endforeach
                        <flux:button size="sm" icon="plus" wire:click="addHoliday">Ajouter un jour férié</flux:button>
                    </div>
                </div>

                <flux:separator />

                {{-- Leaves --}}
                <div>
                    <div>
                        <flux:heading>Congés</flux:heading>
                    </div>
                    <div class="space-y-4 mt-2">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <flux:input :label="__('Congé mensuel')" wire:model="form.leaves.monthlyLeave" type="number" step="0.1" />
                            <flux:input :label="__('Congé ancienneté')" wire:model="form.leaves.seniorLeave" type="number" step="0.1" />
                            <flux:input :label="__('Congé enfant')" wire:model="form.leaves.childLeave" type="number" step="0.1" />
                        </div>
                    </div>
                </div>

                {{-- Labour Hours --}}
                <div>
                    <div>
                        <flux:heading>Heures de travail</flux:heading>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                         <flux:select label="Heures mensuelles" wire:model="form.labourHours">
                            <flux:select.option value="">Choisir...</flux:select.option>
                            @foreach ($labourHoursOptions as $key => $value)
                                <flux:select.option value="{{ $value }}">{{ ucfirst($key) }} ({{ $value }})</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                {{-- Seniority Bonus --}}
                <div>
                    <div>
                        <flux:heading>Prime d'ancienneté</flux:heading>
                    </div>
                    <div class="space-y-4 mt-2">
                        <flux:switch :label="__('Activer')" wire:model.live="form.seniorityBonus.enabled" />
                        @if($form->seniorityBonus['enabled'] ?? false)
                            <flux:input :label="__('Taux')" wire:model="form.seniorityBonus.rate" type="number" step="0.01" />
                        @endif
                    </div>
                </div>

                {{-- Old Age Pension --}}
                <div>
                    <div>
                        <flux:heading>Pension vieillesse</flux:heading>
                    </div>
                    <div class="space-y-4 mt-2">
                        <flux:switch :label="__('Activer')" wire:model.live="form.oldAgePension.enabled" />
                        @if($form->oldAgePension['enabled'] ?? false)
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <flux:input :label="__('Part employeur')" wire:model="form.oldAgePension.employerShare" type="number" step="0.001" />
                                <flux:input :label="__('Part employé')" wire:model="form.oldAgePension.employeeShare" type="number" step="0.001" />
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Family Allowances --}}
                <div>
                    <div>
                        <flux:heading>Allocations familiales</flux:heading>
                    </div>
                    <div class="space-y-4 mt-2">
                        <flux:switch :label="__('Activer')" wire:model.live="form.familyAllowances.enabled" />
                        @if($form->familyAllowances['enabled'] ?? false)
                            <flux:input :label="__('Taux')" wire:model="form.familyAllowances.rate" type="number" step="0.01" />
                        @endif
                    </div>
                </div>

                {{-- Accident --}}
                <div>
                    <div>
                        <flux:heading>Accident de travail</flux:heading>
                    </div>
                    <div class="space-y-4 mt-2">
                        <flux:switch :label="__('Activer')" wire:model.live="form.accident.enabled" />
                        @if($form->accident['enabled'] ?? false)
                            <flux:input :label="__('Taux')" wire:model="form.accident.rate" type="number" step="0.0001" />
                        @endif
                    </div>
                </div>

                {{-- CFC --}}
                <div>
                    <div>
                        <flux:heading>CFC</flux:heading>
                    </div>
                    <div class="space-y-4 mt-2">
                        <flux:switch :label="__('Activer')" wire:model.live="form.cfc.enabled" />
                        @if($form->data['cfc']['enabled'] ?? false)
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <flux:input :label="__('Part employeur')" wire:model="form.data.cfc.employerShare" type="number" step="0.001" />
                                <flux:input :label="__('Part employé')" wire:model="form.data.cfc.employeeShare" type="number" step="0.001" />
                            </div>
                        @endif
                    </div>
                </div>

                {{-- CAC --}}
                <div>
                    <div>
                        <flux:heading>CAC</flux:heading>
                    </div>
                    <div class="space-y-4 mt-2">
                        <flux:switch :label="__('Activer')" wire:model.live="form.data.cac.enabled" />
                        @if($form->data['cac']['enabled'] ?? false)
                            <flux:input :label="__('Part employé')" wire:model="form.data.cac.employeeShare" type="number" step="0.01" />
                        @endif
                    </div>
                </div>

                {{-- FNE --}}
                <div>
                    <div>
                        <flux:heading>FNE</flux:heading>
                    </div>
                    <div class="space-y-4 mt-2">
                        <flux:switch :label="__('Activer')" wire:model.live="form.data.fne.enabled" />
                        @if($form->data['fne']['enabled'] ?? false)
                            <flux:input :label="__('Part employeur')" wire:model="form.data.fne.employerShare" type="number" step="0.01" />
                        @endif
                    </div>
                </div>
                </div>

                <flux:separator />

                <div>
                    <div>
                        <flux:heading>Paiement</flux:heading>
                    </div>
                    <div class="space-y-4 mt-2">
                       <flux:select label="Paiement des salaires" description="Moyen de paiement par défaut" wire:model="form.data.paymentMethod">
                           <flux:select.option value="">Choisir une option</flux:select.option>
                           @foreach (PaymentEnum::options() as $item)
                            <flux:select.option value="{{ $item['label'] }}">{{ $item['label'] }}</flux:select.option>
                           @endforeach
                        </flux:select>
                    </div>
                    <div>
                        <flux:heading>Droit applicable</flux:heading>
                    </div>
                    <div class="space-y-4 mt-2">
                       <flux:select label="Paiement des salaires" description="Droit applicable" wire:model="form.data.applicable_law">
                           <flux:select.option value="">Choisir une option</flux:select.option>
                           @foreach (LawEnum::options() as $item)
                            <flux:select.option value="{{ $item['label'] }}">{{ $item['label'] }}</flux:select.option>
                           @endforeach
                        </flux:select>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <flux:button variant="primary" type="submit">
                        {{(__('Enregistrer'))}}
                    </flux:button>
                </div>
            </form>
        </div>
    </x-settings.layout>
    {{-- It is not the man who has too little, but the man who craves more, that is poor. - Seneca --}}
</section>