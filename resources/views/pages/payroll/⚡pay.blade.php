<?php

use App\Enums\StatusEnum;
use App\Models\Employee;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Paie')]  class extends Component

{
public function mount()
{
    
}

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
    <div class="bg-white/4 border border-white/8 rounded-2xl p-5 sm:p-6 my-6">


        <div class="overflow-x-auto pb-1">
            <div class="min-w-170">
                @php
                $steps = [
                ['label' => 'Bulletins générés et val.', 'date' => '01 juil.', 'status' => 'done'],
                ['label' => 'Livre de paie généré et val.', 'date' => '05 juil.', 'status' => 'done'],
                ['label' => 'Déclaration générée et val.', 'date' => '10 juil.', 'status' => 'pending'],
                ['label' => 'Clôture de paie', 'date' => '15 juil.', 'status' => 'pending'],
                ];
                @endphp

                <div class="relative flex items-start">
                    {{-- Connecting line --}}
                    <div class="absolute top-5 left-5 right-5 h-px bg-white/8 z-0"></div>

                    @foreach ($steps as $step)
                    <div class="flex-1 flex flex-col items-center relative z-10">
                        @if ($step['status'] === 'done')
                        <div
                            class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center ring-4 ring-[#0f1117] shadow-lg shadow-emerald-500/20">
                            <flux:icon.check class="w-4 h-4 text-white" />
                        </div>
                        @elseif($step['status'] === 'current')
                        <div
                            class="w-10 h-10 rounded-full bg-amber-500 flex items-center justify-center ring-4 ring-[#0f1117] shadow-lg shadow-amber-500/25">
                            <flux:icon.clock class="w-4 h-4 text-white" />
                        </div>
                        @else
                        <div
                            class="w-10 h-10 rounded-full bg-white/6 border border-white/10 flex items-center justify-center ring-4 ring-[#0f1117]">
                            <flux:icon.ellipsis-horizontal class="w-4 h-4 text-white/25" />
                        </div>
                        @endif

                        <div class="mt-3 text-center px-1">
                            <p
                                class="text-[11px] font-semibold leading-tight
                                    @if ($step['status'] === 'done') text-emerald-400
                                    @elseif($step['status'] === 'current') text-amber-400
                                    @else text-white/30 @endif
                                ">
                                {{ $step['label'] }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Progress bar --}}
                <div class="mt-6 h-1.5 bg-white/6 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-linear-to-r from-emerald-500 via-emerald-400 to-amber-400 transition-all duration-700"
                        style="width: 55%"></div>
                </div>
                <div class="flex justify-between mt-1.5 text-[10px]">
                    <span class="text-white/25">Début du mois</span>
                    <span class="text-amber-400 font-semibold">Aujourd'hui — 55% complété</span>
                    <span class="text-white/25">Clôture le 15</span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-center flex-wrap gap-3 mt-1">
            <div>
                <h2 class="text-sm font-semibold text-white">Processus de paie — {{ now()->translatedFormat('F Y') }}
                </h2>
                <p class="text-xs text-white/40 mt-0.5">Étape 4 sur 7 · Clôture prévue le 15</p>
            </div>
        </div>
    </div>

<x-tabs :tabs="['Vue d\'ensemble', 'Bulletin de paie', 'Livre de paie', 'Déclarations']">
        <x-slot:tab1>
            <livewire:payroll.payroll-general :company="$this->company" />
        </x-slot:tab1>
        <x-slot:tab2>
            <livewire:payroll.payroll-payslips :company="$this->company" />
        </x-slot:tab2>

        <x-slot:tab3>
            <livewire:payroll.payroll-book :company="$this->company" />

        </x-slot:tab3>

        <x-slot:tab4>
            <livewire:payroll.payroll-declaration :company="$this->company" />
        </x-slot:tab4>

        {{--<x-slot:tab5>
            <livewire:payroll.payroll-archive />
        </x-slot:tab5>--}}

    </x-tabs>



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
