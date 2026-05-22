<?php

use App\Enums\StatusEnum;
use App\Models\Employee;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Paie')]  class extends Component

{


    #[Computed()]
    public function company()
    {
        return auth()
            ->user()
            ->company()
            ->with([
                'employees.payslip',
                'declarations',
                'payrollBook',
            ])
            ->withCount('employees')
            ->first();

            
    }

    public function closePayroll()
    {
        return $this->redirect(route('pay.close.payroll', ["company" => $this->company]), navigate: true);
    }
};
?>

<div>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <flux:heading size="xl"> Payroll </flux:heading>
            <flux:text variant="subtle">
                 Visualisez, approuvez et suivez la clôture de votre paie. 
            </flux:text>
        </div>
        <div class="flex items-center justify-end gap-2">
            <flux:button variant="primary" wire:click="closePayroll">
                 Close the payroll 
            </flux:button>
            <flux:button href="{{ route('pay.check.payslips') }}" wire:navigate>
                 Validate payslip 
            </flux:button>
        </div>
    </div>
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
        <x-ui.tab label="Bulletin de paie" icon="document" />
        <x-ui.tab label="Livre de paie" icon="book-open" />
        <x-ui.tab label="Déclarations" icon="building-office-2" />
    </x-ui.tab.group>
    <x-ui.tab.panel>
    <livewire:payroll.payroll-general :company="$this->company" />

    </x-ui.tab.panel>
    <x-ui.tab.panel>
    <livewire:payroll.payroll-payslips :company="$this->company" />

    </x-ui.tab.panel>
    <x-ui.tab.panel>
    <livewire:payroll.payroll-book :company="$this->company" />

    </x-ui.tab.panel>
    <x-ui.tab.panel>
    <livewire:payroll.payroll-declaration :company="$this->company" />


    </x-ui.tab.panel>
</x-ui.tabs>



    <flux:modal name="close-payroll" name="close-payroll" class="min-w-100">
        <div class="space-y-6 pt-5">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Clôturer la paie') }}</flux:heading>
    </div>
    <div>
        <flux:text variant="subtle">
            La clôture de la paie est une étape cruciale dans le processus de gestion de la paie. Elle marque la fin d'une période de paie et permet de finaliser les calculs, les déclarations et les paiements associés.

            Pour clôturer votre paie, Au plus tôt le 20 du mois en cours, vous devez d'abord générer et valider tous les bulletins de paie de vos employés, ainsi que le livre de paie et les déclarations associées. Assurez-vous que tous les éléments sont en ordre avant de procéder à la clôture.
        </flux:text>
    </div>

    <div class="flex">
        <flux:spacer />
        <flux:modal.close>
            <flux:button variant="primary">
                {{ __('D\'accord, j\'ai compris') }}
            </flux:button>
        </flux:modal.close>
    </div>

    </div>
    </flux:modal>
@else
    <x-no-company />
    @endif
</div>
