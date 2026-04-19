@if($employee && $employee->payslip)
    @php
        $salaries = $salaries ?? $employee->payslip->formatted_salaries ?? [];
        $contributions = $contributions ?? $employee->payslip->formatted_contributions ?? [];
    @endphp
    <!-- Main Container -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl overflow-hidden shadow-xl border border-zinc-200 dark:border-zinc-800 transition-colors duration-200 font-sans text-zinc-900 dark:text-zinc-100">
        
        <!-- En-tête / Header - Using Table for consistent 2-col layout in PDF -->
        <div @class([
            'text-white transition-colors duration-200',
            'bg-green-600 dark:bg-green-700' => $employee->payslip->status ===  App\Enums\StatusEnum::APPROVED,
        'bg-blue-600 dark:bg-blue-700' => $employee->payslip->status ===  App\Enums\StatusEnum::PENDING ])>
            <div class="p-6">
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-bold tracking-tight">BULLETIN DE PAIE</h1>
                    <p class="text-sm opacity-90">Période du 01/{{ now()->format('m/Y') }} au {{ now()->endOfMonth()->format('d/m/Y') }}</p>
                    <p class="text-sm opacity-90">Paiement le {{ now()->endOfMonth()->format('d/m/Y') }} par
                        {{ $employee->payslip['company_data']['paymentMethod'] }}</p>
                </div>

                <!-- Layout Table for Header Details -->
                <table class="w-full" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="align-top text-left" style="width: 50%; vertical-align: top;">
                            <h2 class="text-xl font-semibold">{{ $employee->payslip['company_data']['name'] }}</h2>
                            <p class="text-sm opacity-90 leading-relaxed">
                                {{ $employee->payslip['company_data']['city'] }}<br>
                                {{ $employee->payslip['company_data']['address'] }}
                            </p>
                        </td>
                        <td class="align-top text-right" style="width: 50%; vertical-align: top; text-align: right;">
                            <div class="space-y-1">
                                <p><span class="font-semibold">NIU :</span> {{ $employee->payslip['company_data']['nui'] ?? '' }}</p>
                                <p><span class="font-semibold">N° CNPS :</span> {{ $employee->payslip['company_data']['cnps'] ?? '' }}</p>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="mt-6 text-center pt-4 border-t border-blue-500/30">
                    <p class="text-xs uppercase tracking-wider opacity-75 mb-1">Droit applicable</p>
                    <p class="font-medium">{{ $employee->payslip['company_data']['applicable_law'] ?? '' }}</p>
                </div>
            </div>
        </div>

        <!-- Informations employé / Employee Info - Table for Grid simulation -->
        <div class="p-6 border-b border-zinc-200 dark:border-zinc-800">
            <table class="w-full" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td class="align-top" style="width: 25%; vertical-align: top; padding-right: 15px; padding-bottom: 20px;">
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white">{{ $employee->payslip['employee_data']['name'] ?? '' }}</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $employee->payslip['employee_data']['job_title'] ?? '' }}</p>
                    </td>
                    <td class="align-top" style="width: 25%; vertical-align: top; padding-right: 15px; padding-bottom: 20px;">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Date embauche</p>
                        <p class="font-medium">{{ $employee->payslip['employee_data']['start_date'] ?? '' }}</p>
                    </td>
                    <td class="align-top" style="width: 25%; vertical-align: top; padding-right: 15px; padding-bottom: 20px;">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Ancienneté</p>
                        <p class="font-medium">
                            {{ $employee->payslip['employee_data']['start_date_raw'] }}
                        </p>
                    </td>
                    <td class="align-top" style="width: 25%; vertical-align: top; padding-bottom: 20px;">
                         <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">N° CNPS Salarié</p>
                        <p class="font-medium">{{ $employee->payslip['employee_data']['cnps'] ?? '' }}</p>
                    </td>
                </tr>
                <tr>
                    <td class="align-top" style="width: 25%; vertical-align: top; padding-right: 15px;">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Horaire</p>
                        <p class="font-medium">{{ $employee->payslip['company_data']['labour_hours'] ?? '' }}</p>
                    </td>
                     <td class="align-top" style="width: 25%; vertical-align: top; padding-right: 15px;">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">Catégorie pro.</p>
                        <p class="font-medium">{{ $employee->payslip['employee_data']['professional_category'] ?? '' }}</p>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        </div>

        <!-- Tableau des éléments de paie -->
        <div class="p-6">
            <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-800">
                <table class="w-full text-sm text-left data-table">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 dark:text-zinc-400 font-medium">
                        <tr>
                            <th class="py-3 px-4 w-16">N°</th>
                            <th class="py-3 px-4">Désignation</th>
                            <th class="py-3 px-4 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @if(isset($employee->payslip['elements_data']))
                            @foreach ($employee->payslip['elements_data'] as $item)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">{{ $item['code'] ?? '' }}</td>
                                    <td class="py-3 px-4 font-medium">{{ $item['label'] ?? '' }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums">{{ $item['amount'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        @endif

                        <tr class="bg-zinc-100 dark:bg-zinc-800 font-bold text-zinc-900 dark:text-white">
                            <td></td>
                            <td class="py-3 px-4 text-right">Total Brut</td>
                            <td class="py-3 px-4 text-right tabular-nums">{{ $salaries['gross_salary']['amount'] ?? '0' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tableau des cotisations -->
        <div class="p-6 pt-0">
            <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-800">
                <table class="w-full text-sm text-left data-table">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 dark:text-zinc-400 font-medium">
                        <tr>
                            <th class="py-3 px-4 w-16">N°</th>
                            <th class="py-3 px-4">Désignation</th>
                            <th class="py-3 px-4 text-right">Base</th>
                            <th class="py-3 px-4 text-right">Taux</th>
                            <th class="py-3 px-4 text-right">Part salariale</th>
                            <th class="py-3 px-4 text-right">Part patronale</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @if(isset($contributions))
                            @foreach($contributions as $row)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">{{ $row['code'] ?? '' }}</td>
                                    <td class="py-3 px-4 font-medium">{{ $row['label'] ?? '' }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums text-zinc-500 dark:text-zinc-400"></td>
                                    <td class="py-3 px-4 text-right tabular-nums text-zinc-500 dark:text-zinc-400"></td>
                                    <td class="py-3 px-4 text-right tabular-nums">{{ $row['employee'] ?? '0' }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums">{{ $row['employer'] ?? '0' }}</td>
                                </tr>
                            @endforeach
                        @endif

                        <tr class="bg-zinc-100 dark:bg-zinc-800 font-bold text-zinc-900 dark:text-white">
                            <td colspan="4" class="py-3 px-4 text-right">Total Cotisations</td>
                            <td class="py-3 px-4 text-right tabular-nums text-red-600 dark:text-red-400">
                                {{ isset($employee->payslip['employee_contribution']) ? array_sum(array_column($employee->payslip['employee_contribution'], 'amount')) : '0' }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-zinc-500 dark:text-zinc-400">
                                {{ isset($employee->payslip['employer_contribution']) ? array_sum(array_column($employee->payslip['employer_contribution'], 'amount')) : '0' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if(!empty($employee->payslip['retenues_data']))
            <div class="p-6">
                <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-800">
                    <table class="w-full text-sm text-left data-table">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 dark:text-zinc-400 font-medium">
                            <tr>
                                <th class="py-3 px-4 w-16">N°</th>
                                <th class="py-3 px-4">Désignation</th>
                                <th class="py-3 px-4 text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach ($employee->payslip['retenues_data'] as $item)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">{{ $item['code'] ?? '' }}</td>
                                    <td class="py-3 px-4 font-medium">{{ $item['label'] ?? '' }}</td>
                                    <td class="py-3 px-4 text-right tabular-nums">{{ $item['amount'] ?? '' }}</td>
                                </tr>
                            @endforeach

                            <tr class="bg-zinc-100 dark:bg-zinc-800 font-bold text-zinc-900 dark:text-white">
                                <td></td>
                                <td class="py-3 px-4 text-right">Total Retenues</td>
                                <td class="py-3 px-4 text-right tabular-nums">
                                    {{ array_sum(array_column($employee->payslip['retenues_data'], 'amount')) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Période et compteurs / Period and Counters -->
        <div class="p-6 bg-zinc-50 dark:bg-zinc-900/50 border-t border-zinc-200 dark:border-zinc-800">
            <!-- Net Pay Box -->
            <div class="bg-zinc-800 text-white p-6 rounded-2xl shadow-lg mb-8">
                 <table class="w-full" style="width: 100%; border-collapse: collapse;">
                    <tr>
                         <td class="align-middle" style="vertical-align: middle;">
                            <h3 class="text-xl md:text-2xl font-bold">Net à payer</h3>
                         </td>
                         <td class="align-middle text-right" style="vertical-align: middle; text-align: right;">
                             <p class="text-3xl md:text-4xl font-bold tabular-nums">{{ $salaries['nap']['amount'] ?? '0' }}</p>
                         </td>
                    </tr>
                 </table>
            </div>

            <!-- Counters Grid -> Table -->
            <table class="w-full" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td class="align-top" style="width: 50%; vertical-align: top; padding-right: 20px;">
                        <h4 class="font-bold text-zinc-900 dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-2 mb-4">Cumuls</h4>
                        
                        <table class="w-full text-sm">
                             <tr>
                                <td class="py-2"><p class="text-zinc-500 dark:text-zinc-400">Salaire brut</p></td>
                                <td class="py-2 text-right"><p class="font-medium text-lg">{{ $salaries['gross_salary']['amount'] ?? '0' }}</p></td>
                            </tr>
                             <tr>
                                <td class="py-2"><p class="text-zinc-500 dark:text-zinc-400">Avantages en nature</p></td>
                                <td class="py-2 text-right"><p class="font-medium text-lg">{{ $employee->payslip['employee_data']['sum_advnats'] ?? '0' }}</p></td>
                            </tr>
                            <tr>
                                <td class="py-2"><p class="text-zinc-500 dark:text-zinc-400">Net imposable</p></td>
                                <td class="py-2 text-right"><p class="font-medium text-lg">{{ $salaries['taxable_gross_salary']['amount'] ?? '0' }}</p></td>
                            </tr>
                            <tr>
                                <td class="py-2"><p class="text-zinc-500 dark:text-zinc-400">Charges salariales</p></td>
                                <td class="py-2 text-right"><p class="font-medium text-lg text-red-600 dark:text-red-400">{{ isset($employee->payslip['employee_contribution']) ? array_sum(array_column($employee->payslip['employee_contribution'], 'amount')) : '0' }}</p></td>
                            </tr>
                             <tr>
                                <td class="py-2"><p class="text-zinc-500 dark:text-zinc-400">Charges patronales</p></td>
                                <td class="py-2 text-right"><p class="font-medium text-lg text-zinc-500 dark:text-zinc-400">{{ isset($employee->payslip['employer_contribution']) ? array_sum(array_column($employee->payslip['employer_contribution'], 'amount')) : '0' }}</p></td>
                            </tr>
                        </table>
                    </td>
                    <td class="align-top" style="width: 50%; vertical-align: top; padding-left: 20px;">
                        <h4 class="font-bold text-zinc-900 dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-2 mb-4">Compteurs</h4>
                        <table class="w-full text-sm">
                              <tr>
                                <td class="py-2"><p class="text-zinc-500 dark:text-zinc-400">Jours travaillés</p></td>
                                <td class="py-2 text-right"><p class="font-medium text-lg">{{  $employee->payslip['employee_data']['day_worked'] ??  '0' }}</p></td>
                            </tr>
                            <tr>
                                <td class="py-2"><p class="text-zinc-500 dark:text-zinc-400">Heures Supp.</p></td>
                                <td class="py-2 text-right"><p class="font-medium text-lg">{{  $employee->payslip['employee_data']['overtimes_taken'] ??  '0' }}</p></td>
                            </tr>
                             <tr>
                                <td class="py-2"><p class="text-zinc-500 dark:text-zinc-400">Solde congé</p></td>
                                <td class="py-2 text-right"><p class="font-medium text-lg">{{ $employee->payslip['employee_data']['leaves_balance']  ??  '0'}}</p></td>
                            </tr>
                             <tr>
                                <td class="py-2"><p class="text-zinc-500 dark:text-zinc-400">Congés pris</p></td>
                                <td class="py-2 text-right"><p class="font-medium text-lg">{{ $employee->payslip['employee_data']['leave_taken'] ??  '0'}}</p></td>
                            </tr>
                             <tr>
                                <td class="py-2"><p class="text-zinc-500 dark:text-zinc-400">Congés restant(s)</p></td>
                                <td class="py-2 text-right"><p class="font-medium text-lg">{{ $employee->payslip['employee_data']['leaves_still'] ?? 0 }} </p></td> 
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Pied de page -->
        <div class="bg-zinc-50 dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-800 p-6 text-center text-xs text-zinc-400 dark:text-zinc-500">
            <p class="mb-1">Pour vous aider à faire valoir vos droits, conservez ce bulletin sans limitation de durée.</p>
            <p>Document généré le {{ now()->format('d/m/Y') }} - SQUARHE - Tous droits réservés</p>
        </div>
    </div>
@endif