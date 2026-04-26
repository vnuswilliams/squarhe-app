<?php

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
            ->companies()
            ->with([
                'employees.payslip',
                'declarations',
                'payrollBook',
            ])
            ->first();
    }
};
?>

<div>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <flux:heading size="xl">{{ Payroll }}</flux:heading>
            <flux:text variant="subtle">
                {{ Visualisez, approuvez et suivez la clôture de votre paie. }}
            </flux:text>
        </div>
        <div class="flex items-center justify-end gap-2">
            <flux:button variant="primary" wire:click="closePayroll">
                {{ Close the payroll }}
            </flux:button>
            <flux:link href="{{ route('pay.check.payslips') }}">
                {{ Validate payslip }}
            </flux:link>
        </div>
    </div>
    {{-- ===================== TIMELINE PROCESSUS PAIE ===================== --}}
    <div class="bg-white/[0.04] border border-white/[0.08] rounded-2xl p-5 sm:p-6">


        <div class="overflow-x-auto pb-1">
            <div class="min-w-[680px]">
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
                    <div class="absolute top-5 left-5 right-5 h-px bg-white/[0.08] z-0"></div>

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
                            class="w-10 h-10 rounded-full bg-white/[0.06] border border-white/10 flex items-center justify-center ring-4 ring-[#0f1117]">
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
                <div class="mt-6 h-1.5 bg-white/[0.06] rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 via-emerald-400 to-amber-400 transition-all duration-700"
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
          
        

        </x-slot:tab1>
        <x-slot:tab2>
            <livewire:payroll.payroll-payslips />
        </x-slot:tab2>

        <x-slot:tab3>
            <livewire:payroll.payroll-book />

        </x-slot:tab3>

        <x-slot:tab4>
            <livewire:payroll.payroll-declaration />
        </x-slot:tab4>

        {{--<x-slot:tab5>
            <livewire:payroll.payroll-archive />
        </x-slot:tab5>--}}

    </x-tabs>




</div>