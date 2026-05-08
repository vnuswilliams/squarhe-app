<?php

use App\Enums\PayslipItemsEnum;
use App\Jobs\GenerateDeclarationJob;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Rap2hpoutre\FastExcel\Facades\FastExcel;

new class extends Component
{
    public $company;
    public $listEmployee;
    public $empContribution = [];
    public $emprContribution = [];
    public $salaries;

    public $total = [];

    #[Computed()]
    public function declaration()
    {

        return $this->company->declarations;

    }

    public function refresh()
    {
        $this->company->declarations()->delete();
        $this->dispatch('notify', type: 'success', message: 'Déclarations régénérées avec succès.');
    }


    public $toggleDeclaration;
    public string $showDeclaction = 'fiscale';
    public function updatedToggleDeclaration($value)
    {
        $this->showDeclaction = $value;
    }

    public function download()
    {
        $data = [
            'company' => $this->company,
            'listEmployee' => $this->listEmployee,
            'salaries' => $this->salaries,
            'empContribution' => $this->empContribution,
            'emprContribution' => $this->emprContribution,
            'toggleDeclaration' => $this->toggleDeclaration,
        ];

        $pdf = Pdf::loadView('pdf.payroll-declaration', $data)->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'declaration_' . $this->toggleDeclaration . '_' . now()->format('m_Y') . '.pdf');
    }

    public function export()
    {
        $rows = [];

        foreach ($this->listEmployee as $empid => $name) {
            if ($this->toggleDeclaration === 'fiscale') {
                $irpp = $this->empContribution[PayslipItemsEnum::IRPP->code()][$empid] ?? 0;
                $cac = $this->empContribution[PayslipItemsEnum::CENTIME_COMMUNAL->code()][$empid] ?? 0;
                $tdl = $this->empContribution[PayslipItemsEnum::TAXE_DEVELOPPEMENT->code()][$empid] ?? 0;
                $rav = $this->empContribution[PayslipItemsEnum::REDEVANCE_AUDIO_VISUELLE->code()][$empid] ?? 0;
                $cfcSal = $this->empContribution[PayslipItemsEnum::CREDIT_FONCIER_SALARIALE->code()][$empid] ?? 0;

                $cfcPat = $this->emprContribution[PayslipItemsEnum::CREDIT_FONCIER_PATRONALE->code()][$empid] ?? 0;
                $fne = $this->emprContribution[PayslipItemsEnum::FNE->code()][$empid] ?? 0;

                $rows[] = [
                    'Employé' => $name,
                    'Salaire Brut' => $this->salaries['gross_salary'][$empid] ?? 0,
                    'Salaire Taxable' => $this->salaries['taxable_gross_salary'][$empid] ?? 0,
                    'IRPP' => $irpp,
                    'CAC' => $cac,
                    'TDL' => $tdl,
                    'RAV' => $rav,
                    'CFC Sal.' => $cfcSal,
                    'Total Sal' => $irpp + $cac + $tdl + $rav + $cfcSal,
                    'CFC Pat.' => $cfcPat,
                    'FNE' => $fne,
                    'Total Pat.' => $cfcPat + $fne,
                ];
            } else {
                $pvSal = $this->empContribution[PayslipItemsEnum::CNPS_VIEILLESSE_SALARIALE->code()][$empid] ?? 0;

                $pvPat = $this->emprContribution[PayslipItemsEnum::CNPS_VIEILLESSE_PATRONALE->code()][$empid] ?? 0;
                $amp = $this->emprContribution[PayslipItemsEnum::CNPS_ACCIDENT_MALADIE_PRO->code()][$empid] ?? 0;
                $af = $this->emprContribution[PayslipItemsEnum::CNPS_ALLOCATION_FAMILIALE->code()][$empid] ?? 0;

                $rows[] = [
                    'Employé' => $name,
                    'Salaire Brut' => $this->salaries['gross_salary'][$empid] ?? 0,
                    'Salaire Côtisable' => $this->salaries['contributory_salary'][$empid] ?? 0,
                    'PV Sal.' => $pvSal,
                    'Total Sal' => $pvSal,
                    'PV Pat.' => $pvPat,
                    'AMP' => $amp,
                    'AF' => $af,
                    'Total Pat.' => $pvPat + $amp + $af,
                ];
            }
        }

        return new FastExcel(collect($rows))->download('declaration_' . $this->toggleDeclaration . '_' . now()->format('m_Y') . '.xlsx');
    }

    public function with()
    {
        return [
            'sumIrpp' => array_sum($this->empContribution[PayslipItemsEnum::IRPP->code()] ?? []),
            'sumCac' => array_sum($this->empContribution[PayslipItemsEnum::CENTIME_COMMUNAL->code()] ?? []),
            'sumTdl' => array_sum($this->empContribution[PayslipItemsEnum::TAXE_DEVELOPPEMENT->code()] ?? []),
            'sumRav' => array_sum($this->empContribution[PayslipItemsEnum::REDEVANCE_AUDIO_VISUELLE->code()] ?? []),
            'sumCfcSal' => array_sum($this->empContribution[PayslipItemsEnum::CREDIT_FONCIER_SALARIALE->code()] ?? []),
            'sumCfcPat' => array_sum($this->emprContribution[PayslipItemsEnum::CREDIT_FONCIER_PATRONALE->code()] ?? []),
            'sumFne' => array_sum($this->emprContribution[PayslipItemsEnum::FNE->code()] ?? []),
        ];
    }
}; ?>
<div class="card bg-base-100">

@if($this->declaration)

    <div class='card-body my-5'>
        <div class="flex            flex-wrap            items-center            justify-between            gap-4">
            <flux:heading level="1" size="lg" class="font-bold">{{ __('Déclaration mensuelle') }}</flux:heading>
            <div class="flex flex-wrap items-center gap-4">>
                <flux:select wire:model.live="toggleDeclaration" wire:target="toggleDeclaration"
                    class="w-full sm:w-auto">
                    <flux:select.option value="fiscale"> {{ 'Déclaration fiscale' }}</flux:select.option>
                    <flux:select.option value="sociale">{{ 'Déclaration sociale' }}</flux:select.option>
                </flux:select>

                <flux:button wire:click="download" icon="arrow-down-tray" variant="primary" />
                <flux:button wire:click="export" icon="arrow-up-tray" />
                <flux:button wire:click="refresh" icon="arrow-path-rounded-square" />
            </div>

        </div>

        <div class="overflow-x-auto">
            <div class="flex items-center justify-between my-6">
                <div class="max-w-2xs">
                    <flux:heading>REPUBLIQUE DU CAMEROUN </flux:heading>
                    <flux:heading>DOCUMENT D'INFORMATION SUR LE PERSONNEL EMPLOYE </flux:heading>
                </div>
                <div class="max-w-3xl text-center">
                    <flux:heading>{{ $company->name }} </flux:heading>
                    <flux:text>{{ 'NIU :' . $company->nui . ' | N° CNPS :' . $company->cnps }} </flux:text>
                    <flux:text>{{ 'BP :' . $company->adresse . ' | N° TEL :' . $company->phone }} </flux:text>
                    <flux:text>{{ 'Mois de paie' . now()->format(' M Y') }} </flux:text>

                </div>

                <div class="max-w-2xs text-right">
                    <flux:text> N° DIPE : </flux:text>
                    <flux:text>Rég CNPS : </flux:text>
                </div>


            </div>
            @if ($showDeclaction === 'fiscale')
            <div wire:transition wire:loading.class="opacity-0" wire:target='toggleDeclaration'>
                <table class="w-full text-sm text-left data-table">

                    <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 dark:text-zinc-400 font-medium">

                        <tr>
                            <th class="py-3 px-4 ">Employé</th>
                            <th class="py-3 px-4 ">Salaire Brut</th>
                            <th class="py-3 px-4 ">Salaire Taxable</th>
                            <th class="py-3 px-4 ">IRPP</th>
                            <th class="py-3 px-4 ">CAC</th>
                            <th class="py-3 px-4 ">TDL</th>
                            <th class="py-3 px-4 ">RAV</th>
                            <th class="py-3 px-4 ">CFC Sal.</th>
                            <th class="py-3 px-4 font-extrabold">Total Sal</th>
                            <th class="py-3 px-4 ">CFC Pat.</th>
                            <th class="py-3 px-4 ">FNE</th>
                            <th class="py-3 px-4 font-extrabold">Total Pat.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($listEmployee as $empid => $name)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="py-3 px-4 "> {{ $name }} </td>
                            <td class="py-3 px-4"> {{ $salaries['gross_salary'][$empid] }} </td>
                            <td class="py-3 px-4"> {{ $salaries['taxable_gross_salary'][$empid] }} </td>
                            <td class="py-3 px-4 "> {{ $empContribution[PayslipItemsEnum::IRPP->code()][$empid] }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ $empContribution[PayslipItemsEnum::CENTIME_COMMUNAL->code()][$empid] }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ $empContribution[PayslipItemsEnum::TAXE_DEVELOPPEMENT->code()][$empid] }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ $empContribution[PayslipItemsEnum::REDEVANCE_AUDIO_VISUELLE->code()][$empid] }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ $empContribution[PayslipItemsEnum::CREDIT_FONCIER_SALARIALE->code()][$empid] }}
                            </td>

                            <td class="py-3 px-4 font-extrabold">
                                {{ ($empContribution[PayslipItemsEnum::IRPP->code()][$empid] ?? 0) +
                                            ($empContribution[PayslipItemsEnum::CENTIME_COMMUNAL->code()][$empid] ?? 0) +
                                            ($empContribution[PayslipItemsEnum::TAXE_DEVELOPPEMENT->code()][$empid] ?? 0) +
                                            ($empContribution[PayslipItemsEnum::REDEVANCE_AUDIO_VISUELLE->code()][$empid] ?? 0) +
                                            ($empContribution[PayslipItemsEnum::CREDIT_FONCIER_SALARIALE->code()][$empid] ?? 0) }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ $emprContribution[PayslipItemsEnum::CREDIT_FONCIER_PATRONALE->code()][$empid] }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ $emprContribution[PayslipItemsEnum::FNE->code()][$empid] ?? 0 }}
                            </td>
                            <td class="py-3 px-4 font-extrabold">
                                {{ ($emprContribution[PayslipItemsEnum::CREDIT_FONCIER_PATRONALE->code()][$empid] ?? 0) +
                                            ($emprContribution[PayslipItemsEnum::FNE->code()][$empid] ?? 0) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="100%" class="border p-8 text-center text-gray-500">
                                Aucun bulletin approuvé pour cette période.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="font-extrabold">
                        <tr>
                            <td class="py-3 px-4">TOTAL</td>
                            <td class="py-3 px-4 "> {{ array_sum($salaries['taxable_gross_salary']) }} </td>
                            <td class="py-3 px-4 ">{{ array_sum($salaries['gross_salary']) }}</td>
                            <td class="py-3 px-4 ">
                                {{ isset($empContribution[PayslipItemsEnum::IRPP->code()]) ? array_sum($empContribution[PayslipItemsEnum::IRPP->code()]) : 0 }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ isset($empContribution[PayslipItemsEnum::CENTIME_COMMUNAL->code()]) ? array_sum($empContribution[PayslipItemsEnum::CENTIME_COMMUNAL->code()]) : 0 }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ isset($empContribution[PayslipItemsEnum::TAXE_DEVELOPPEMENT->code()]) ? array_sum($empContribution[PayslipItemsEnum::TAXE_DEVELOPPEMENT->code()]) : 0 }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ isset($empContribution[PayslipItemsEnum::REDEVANCE_AUDIO_VISUELLE->code()]) ? array_sum($empContribution[PayslipItemsEnum::REDEVANCE_AUDIO_VISUELLE->code()]) : 0 }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ isset($empContribution[PayslipItemsEnum::CREDIT_FONCIER_SALARIALE->code()]) ? array_sum($empContribution[PayslipItemsEnum::CREDIT_FONCIER_SALARIALE->code()]) : 0 }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ array_sum($empContribution[PayslipItemsEnum::IRPP->code()]) +
                                        array_sum($empContribution[PayslipItemsEnum::CENTIME_COMMUNAL->code()]) +
                                        array_sum($empContribution[PayslipItemsEnum::TAXE_DEVELOPPEMENT->code()]) +
                                        array_sum($empContribution[PayslipItemsEnum::REDEVANCE_AUDIO_VISUELLE->code()]) +
                                        array_sum($empContribution[PayslipItemsEnum::CREDIT_FONCIER_SALARIALE->code()]) }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ isset($emprContribution[PayslipItemsEnum::CREDIT_FONCIER_PATRONALE->code()]) ? array_sum($emprContribution[PayslipItemsEnum::CREDIT_FONCIER_PATRONALE->code()]) : 0 }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ isset($emprContribution[PayslipItemsEnum::FNE->code()]) ? array_sum($emprContribution[PayslipItemsEnum::FNE->code()]) : 0 }}
                            </td>


                            <td class="py-3 px-4 ">
                                {{ array_sum($emprContribution[PayslipItemsEnum::FNE->code()]) + array_sum($emprContribution[PayslipItemsEnum::CREDIT_FONCIER_PATRONALE->code()]) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-5">
                        <h4 class="font-bold mb-4 text-zinc-700 dark:text-zinc-300">Charges Salariales</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm"><span>IRPP</span><span
                                    class="font-medium">{{ number_format($sumIrpp, 0, ',', ' ') }}</span></div>
                            <div class="flex justify-between text-sm"><span>CAC</span><span
                                    class="font-medium">{{ number_format($sumCac, 0, ',', ' ') }}</span></div>
                            <div class="flex justify-between text-sm"><span>TDL</span><span
                                    class="font-medium">{{ number_format($sumTdl, 0, ',', ' ') }}</span></div>
                            <div class="flex justify-between text-sm"><span>RAV</span><span
                                    class="font-medium">{{ number_format($sumRav, 0, ',', ' ') }}</span></div>
                            <div class="flex justify-between text-sm"><span>CFC Sal.</span><span
                                    class="font-medium">{{ number_format($sumCfcSal, 0, ',', ' ') }}</span></div>
                            <div class="border-t pt-2 flex justify-between font-bold text-sm">
                                <span>Total</span><span>{{ number_format($sumIrpp + $sumCac + $sumTdl + $sumRav + $sumCfcSal, 0, ',', ' ') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-5">
                        <h4 class="font-bold mb-4 text-zinc-700 dark:text-zinc-300">Charges Patronales</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm"><span>CFC Pat.</span><span
                                    class="font-medium">{{ number_format($sumCfcPat, 0, ',', ' ') }}</span></div>
                            <div class="flex justify-between text-sm"><span>FNE</span><span
                                    class="font-medium">{{ number_format($sumFne, 0, ',', ' ') }}</span></div>
                            <div class="border-t pt-2 flex justify-between font-bold text-sm">
                                <span>Total</span><span>{{ number_format($sumCfcPat + $sumFne, 0, ',', ' ') }}</span>
                            </div>
                        </div>
                    </div>
                    <div
                        class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-5 bg-zinc-50 dark:bg-zinc-800/50">
                        <h4 class="font-bold mb-4 text-zinc-700 dark:text-zinc-300">Total à reverser</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm"><span>Total Salarial</span><span
                                    class="font-medium">{{ number_format($sumIrpp + $sumCac + $sumTdl + $sumRav + $sumCfcSal, 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between text-sm"><span>Total Patronal</span><span
                                    class="font-medium">{{ number_format($sumCfcPat + $sumFne, 0, ',', ' ') }}</span>
                            </div>
                            <div class="border-t pt-2 flex justify-between font-bold text-lg text-primary-600">
                                <span>TOTAL</span><span>{{ number_format($sumIrpp + $sumCac + $sumTdl + $sumRav + $sumCfcSal + $sumCfcPat + $sumFne, 0, ',', ' ') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if ($showDeclaction === 'sociale')
            <div wire:transition wire:loading.class="opacity-0" wire:target='toggleDeclaration'>
                <table class="w-full text-sm text-left data-table">

                    <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 dark:text-zinc-400 font-medium">

                        <tr>
                            <th class="py-3 px-4 ">Employé</th>
                            <th class="py-3 px-4 ">Salaire Brut</th>
                            <th class="py-3 px-4 ">Salaire Côtisable</th>
                            <th class="py-3 px-4 ">PV Sal.</th>
                            <th class="py-3 px-4 font-extrabold">Total Sal</th>
                            <th class="py-3 px-4 ">PV Pat.</th>
                            <th class="py-3 px-4 ">AMP</th>
                            <th class="py-3 px-4 ">AF</th>
                            <th class="py-3 px-4 font-extrabold">Total Pat.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @forelse($listEmployee as $empid => $name)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="py-3 px-4 "> {{ $name }} </td>
                            <td class="py-3 px-4"> {{ $salaries['gross_salary'][$empid] }} </td>
                            <td class="py-3 px-4"> {{ $salaries['contributory_salary'][$empid] }} </td>
                            <td class="py-3 px-4 ">
                                {{ $empContribution[PayslipItemsEnum::CNPS_VIEILLESSE_SALARIALE->code()][$empid] }}
                            </td>

                            <td class="py-3 px-4 font-extrabold">
                                {{ $empContribution[PayslipItemsEnum::CNPS_VIEILLESSE_SALARIALE->code()][$empid] }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ $emprContribution[PayslipItemsEnum::CNPS_VIEILLESSE_PATRONALE->code()][$empid] }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ $emprContribution[PayslipItemsEnum::CNPS_ALLOCATION_FAMILIALE->code()][$empid] ?? 0 }}
                            <td class="py-3 px-4 ">
                                {{ $emprContribution[PayslipItemsEnum::CNPS_ACCIDENT_MALADIE_PRO->code()][$empid] ?? 0 }}
                            </td>
                            <td class="py-3 px-4 font-extrabold">
                                {{ ($emprContribution[PayslipItemsEnum::CNPS_VIEILLESSE_PATRONALE->code()][$empid] ?? 0) +
                                            ($emprContribution[PayslipItemsEnum::CNPS_ALLOCATION_FAMILIALE->code()][$empid] ?? 0) +
                                            ($emprContribution[PayslipItemsEnum::CNPS_ACCIDENT_MALADIE_PRO->code()][$empid] ?? 0) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="100%" class="border p-8 text-center text-gray-500">
                                Aucun bulletin approuvé pour cette période.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="font-extrabold">
                        <tr>
                            <td class="py-3 px-4">TOTAL</td>
                            <td class="py-3 px-4 "> {{ array_sum($salaries['taxable_gross_salary']) }} </td>
                            <td class="py-3 px-4 ">{{ array_sum($salaries['contributory_salary']) }}</td>
                            <td class="py-3 px-4 ">
                                {{ isset($empContribution[PayslipItemsEnum::CNPS_VIEILLESSE_SALARIALE->code()]) ? array_sum($empContribution[PayslipItemsEnum::CNPS_VIEILLESSE_SALARIALE->code()]) : 0 }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ array_sum($empContribution[PayslipItemsEnum::CNPS_VIEILLESSE_SALARIALE->code()]) }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ isset($emprContribution[PayslipItemsEnum::CNPS_VIEILLESSE_PATRONALE->code()]) ? array_sum($emprContribution[PayslipItemsEnum::CNPS_VIEILLESSE_PATRONALE->code()]) : 0 }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ isset($emprContribution[PayslipItemsEnum::CNPS_ALLOCATION_FAMILIALE->code()]) ? array_sum($emprContribution[PayslipItemsEnum::CNPS_ALLOCATION_FAMILIALE->code()]) : 0 }}
                            </td>
                            <td class="py-3 px-4 ">
                                {{ isset($emprContribution[PayslipItemsEnum::CNPS_ACCIDENT_MALADIE_PRO->code()]) ? array_sum($emprContribution[PayslipItemsEnum::CNPS_ACCIDENT_MALADIE_PRO->code()]) : 0 }}
                            </td>


                            <td class="py-3 px-4 ">
                                {{ array_sum($emprContribution[PayslipItemsEnum::CNPS_VIEILLESSE_PATRONALE->code()]) + array_sum($emprContribution[PayslipItemsEnum::CNPS_ALLOCATION_FAMILIALE->code()]) + array_sum($emprContribution[PayslipItemsEnum::CNPS_ACCIDENT_MALADIE_PRO->code()]) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                    @php
                    $sumPvSal = array_sum(
                    $empContribution[PayslipItemsEnum::CNPS_VIEILLESSE_SALARIALE->code()] ?? [],
                    );
                    $totalSalSocial = $sumPvSal;

                    $sumPvPat = array_sum(
                    $emprContribution[PayslipItemsEnum::CNPS_VIEILLESSE_PATRONALE->code()] ?? [],
                    );
                    $sumAf = array_sum(
                    $emprContribution[PayslipItemsEnum::CNPS_ALLOCATION_FAMILIALE->code()] ?? [],
                    );
                    $sumAmp = array_sum(
                    $emprContribution[PayslipItemsEnum::CNPS_ACCIDENT_MALADIE_PRO->code()] ?? [],
                    );
                    $totalPatSocial = $sumPvPat + $sumAf + $sumAmp;
                    @endphp
                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-5">
                        <h4 class="font-bold mb-4 text-zinc-700 dark:text-zinc-300">Charges Salariales</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm"><span>P. Vieillesse Sal.</span><span
                                    class="font-medium">{{ number_format($sumPvSal, 0, ',', ' ') }}</span></div>
                            <div class="border-t pt-2 flex justify-between font-bold text-sm">
                                <span>Total</span><span>{{ number_format($totalSalSocial, 0, ',', ' ') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-5">
                        <h4 class="font-bold mb-4 text-zinc-700 dark:text-zinc-300">Charges Patronales</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm"><span>P. Vieillesse Pat.</span><span
                                    class="font-medium">{{ number_format($sumPvPat, 0, ',', ' ') }}</span></div>
                            <div class="flex justify-between text-sm"><span>Alloc. Familiales</span><span
                                    class="font-medium">{{ number_format($sumAf, 0, ',', ' ') }}</span></div>
                            <div class="flex justify-between text-sm"><span>Acc. Travail / Mal. Pro</span><span
                                    class="font-medium">{{ number_format($sumAmp, 0, ',', ' ') }}</span></div>
                            <div class="border-t pt-2 flex justify-between font-bold text-sm">
                                <span>Total</span><span>{{ number_format($totalPatSocial, 0, ',', ' ') }}</span>
                            </div>
                        </div>
                    </div>
                    <div
                        class="border border-zinc-200 dark:border-zinc-700 rounded-lg p-5 bg-zinc-50 dark:bg-zinc-800/50">
                        <h4 class="font-bold mb-4 text-zinc-700 dark:text-zinc-300">Total à reverser</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm"><span>Total Salarial</span><span
                                    class="font-medium">{{ number_format($totalSalSocial, 0, ',', ' ') }}</span>
                            </div>
                            <div class="flex justify-between text-sm"><span>Total Patronal</span><span
                                    class="font-medium">{{ number_format($totalPatSocial, 0, ',', ' ') }}</span>
                            </div>
                            <div class="border-t pt-2 flex justify-between font-bold text-lg text-primary-600">
                                <span>TOTAL
                                    CNPS</span><span>{{ number_format($totalSalSocial + $totalPatSocial, 0, ',', ' ') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

@else

   
<div class="flex flex-col items-center justify-center h-64 text-zinc-500">
        <svg class="w-16 h-16 mb-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="text-lg font-medium text-zinc-900 dark:text-white">Les déclarations seront générées une fois les bulletin générés et validés.</p>
        <p class="text-sm">Pour générer et valider les bulletins cliquez  <a href="{{ route('pay.check.payslips') }}" class="text-blue-500 underline">ici</a>.</p>
    </div>
    @endif
</div>