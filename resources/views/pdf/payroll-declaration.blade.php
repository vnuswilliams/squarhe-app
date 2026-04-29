<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Déclaration {{ ucfirst($toggleDeclaration) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #444; padding: 4px; text-align: right; }
        th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        td:first-child { text-align: left; }
        
        /* C'est ici que la magie opère pour répéter le header sur chaque page */
        thead { display: table-header-group; }
        tfoot { display: table-row-group; page-break-inside: avoid; }
        tr { page-break-inside: avoid; }

        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .mb-4 { margin-bottom: 1rem; }
        
        /* Résumés */
        .summary-container { width: 100%; margin-top: 20px; page-break-inside: avoid; }
        .summary-box { float: left; width: 32%; border: 1px solid #444; padding: 5px; margin-right: 1%; box-sizing: border-box; }
        .summary-box:last-child { margin-right: 0; }
        .summary-row { clear: both; margin-bottom: 5px; }
        .summary-label { float: left; }
        .summary-value { float: right; font-weight: bold; }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>
    <div class="text-center mb-4">
        <h2 class="uppercase" style="margin: 0; font-size: 14px;">République du Cameroun</h2>
        <h3 class="uppercase" style="margin: 5px 0; font-size: 12px;">Document d'information sur le personnel employé</h3>
        <div class="font-bold text-lg" style="margin-top: 10px; font-size: 14px;">{{ $company->name }}</div>
        <div>NIU : {{ $company->niu }} | N° CNPS : {{ $company->cnps }}</div>
        <div>BP : {{ $company->adresse }} | N° TEL : {{ $company->phone }}</div>
        <div>Mois de paie : {{ now()->format('F Y') }}</div>
        <div style="margin-top: 10px; font-weight: bold; text-decoration: underline;">DÉCLARATION {{ strtoupper($toggleDeclaration) }}</div>
    </div>

    @php
        use App\Enums\PayslipItems;
    @endphp

    @if ($toggleDeclaration === 'fiscale')
        <table>
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Salaire Brut</th>
                    <th>Salaire Taxable</th>
                    <th>IRPP</th>
                    <th>CAC</th>
                    <th>TDL</th>
                    <th>RAV</th>
                    <th>CFC Sal.</th>
                    <th>Total Sal</th>
                    <th>CFC Pat.</th>
                    <th>FNE</th>
                    <th>Total Pat.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($listEmployee as $empid => $name)
                    <tr>
                        <td>{{ $name }}</td>
                        <td>{{ number_format($salaries['gross_salary'][$empid] ?? 0, 0, ',', ' ') }}</td>
                        <td>{{ number_format($salaries['taxable_gross_salary'][$empid] ?? 0, 0, ',', ' ') }}</td>
                        <td>{{ number_format($empContribution[PayslipItems::IRPP->code()][$empid] ?? 0, 0, ',', ' ') }}</td>
                        <td>{{ number_format($empContribution[PayslipItems::CENTIME_COMMUNAL->code()][$empid] ?? 0, 0, ',', ' ') }}</td>
                        <td>{{ number_format($empContribution[PayslipItems::TAXE_DEVELOPPEMENT->code()][$empid] ?? 0, 0, ',', ' ') }}</td>
                        <td>{{ number_format($empContribution[PayslipItems::REDEVANCE_AUDIO_VISUELLE->code()][$empid] ?? 0, 0, ',', ' ') }}</td>
                        <td>{{ number_format($empContribution[PayslipItems::CREDIT_FONCIER_SALARIALE->code()][$empid] ?? 0, 0, ',', ' ') }}</td>
                        <td>
                            {{ number_format(
                                ($empContribution[PayslipItems::IRPP->code()][$empid] ?? 0) +
                                ($empContribution[PayslipItems::CENTIME_COMMUNAL->code()][$empid] ?? 0) +
                                ($empContribution[PayslipItems::TAXE_DEVELOPPEMENT->code()][$empid] ?? 0) +
                                ($empContribution[PayslipItems::REDEVANCE_AUDIO_VISUELLE->code()][$empid] ?? 0) +
                                ($empContribution[PayslipItems::CREDIT_FONCIER_SALARIALE->code()][$empid] ?? 0),
                                0, ',', ' ') }}
                        </td>
                        <td>{{ number_format($emprContribution[PayslipItems::CREDIT_FONCIER_PATRONALE->code()][$empid] ?? 0, 0, ',', ' ') }}</td>
                        <td>{{ number_format($emprContribution[PayslipItems::FNE->code()][$empid] ?? 0, 0, ',', ' ') }}</td>
                        <td>
                            {{ number_format(
                                ($emprContribution[PayslipItems::CREDIT_FONCIER_PATRONALE->code()][$empid] ?? 0) +
                                ($emprContribution[PayslipItems::FNE->code()][$empid] ?? 0),
                                0, ',', ' ') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="text-center">Aucune donnée</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                 <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td>TOTAL</td>
                    <td>{{ number_format(array_sum($salaries['gross_salary'] ?? []), 0, ',', ' ') }}</td>
                    <td>{{ number_format(array_sum($salaries['taxable_gross_salary'] ?? []), 0, ',', ' ') }}</td>
                    <td>{{ number_format(array_sum($empContribution[PayslipItems::IRPP->code()] ?? []), 0, ',', ' ') }}</td>
                    <td>{{ number_format(array_sum($empContribution[PayslipItems::CENTIME_COMMUNAL->code()] ?? []), 0, ',', ' ') }}</td>
                    <td>{{ number_format(array_sum($empContribution[PayslipItems::TAXE_DEVELOPPEMENT->code()] ?? []), 0, ',', ' ') }}</td>
                    <td>{{ number_format(array_sum($empContribution[PayslipItems::REDEVANCE_AUDIO_VISUELLE->code()] ?? []), 0, ',', ' ') }}</td>
                    <td>{{ number_format(array_sum($empContribution[PayslipItems::CREDIT_FONCIER_SALARIALE->code()] ?? []), 0, ',', ' ') }}</td>
                    <td>
                        {{ number_format(
                            array_sum($empContribution[PayslipItems::IRPP->code()] ?? []) +
                            array_sum($empContribution[PayslipItems::CENTIME_COMMUNAL->code()] ?? []) +
                            array_sum($empContribution[PayslipItems::TAXE_DEVELOPPEMENT->code()] ?? []) +
                            array_sum($empContribution[PayslipItems::REDEVANCE_AUDIO_VISUELLE->code()] ?? []) +
                            array_sum($empContribution[PayslipItems::CREDIT_FONCIER_SALARIALE->code()] ?? []),
                            0, ',', ' ') }}
                    </td>
                    <td>{{ number_format(array_sum($emprContribution[PayslipItems::CREDIT_FONCIER_PATRONALE->code()] ?? []), 0, ',', ' ') }}</td>
                    <td>{{ number_format(array_sum($emprContribution[PayslipItems::FNE->code()] ?? []), 0, ',', ' ') }}</td>
                    <td>
                        {{ number_format(
                            array_sum($emprContribution[PayslipItems::CREDIT_FONCIER_PATRONALE->code()] ?? []) +
                            array_sum($emprContribution[PayslipItems::FNE->code()] ?? []),
                            0, ',', ' ') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        @php
            $sumIrpp = array_sum($empContribution[PayslipItems::IRPP->code()] ?? []);
            $sumCac = array_sum($empContribution[PayslipItems::CENTIME_COMMUNAL->code()] ?? []);
            $sumTdl = array_sum($empContribution[PayslipItems::TAXE_DEVELOPPEMENT->code()] ?? []);
            $sumRav = array_sum($empContribution[PayslipItems::REDEVANCE_AUDIO_VISUELLE->code()] ?? []);
            $sumCfcSal = array_sum($empContribution[PayslipItems::CREDIT_FONCIER_SALARIALE->code()] ?? []);
            $totalSal = $sumIrpp + $sumCac + $sumTdl + $sumRav + $sumCfcSal;

            $sumCfcPat = array_sum($emprContribution[PayslipItems::CREDIT_FONCIER_PATRONALE->code()] ?? []);
            $sumFne = array_sum($emprContribution[PayslipItems::FNE->code()] ?? []);
            $totalPat = $sumCfcPat + $sumFne;
        @endphp
        
        <div class="summary-container clearfix">
            <div class="summary-box">
                <div class="text-center font-bold mb-4">Charges Salariales</div>
                <div class="summary-row clearfix"><span class="summary-label">IRPP</span><span class="summary-value">{{ number_format($sumIrpp, 0, ',', ' ') }}</span></div>
                <div class="summary-row clearfix"><span class="summary-label">CAC</span><span class="summary-value">{{ number_format($sumCac, 0, ',', ' ') }}</span></div>
                <div class="summary-row clearfix"><span class="summary-label">TDL</span><span class="summary-value">{{ number_format($sumTdl, 0, ',', ' ') }}</span></div>
                <div class="summary-row clearfix"><span class="summary-label">RAV</span><span class="summary-value">{{ number_format($sumRav, 0, ',', ' ') }}</span></div>
                <div class="summary-row clearfix"><span class="summary-label">CFC Sal.</span><span class="summary-value">{{ number_format($sumCfcSal, 0, ',', ' ') }}</span></div>
                <div style="border-top: 1px solid #444; margin-top: 5px; padding-top: 5px;" class="summary-row clearfix">
                    <span class="summary-label font-bold">Total</span><span class="summary-value">{{ number_format($totalSal, 0, ',', ' ') }}</span>
                </div>
            </div>
            
            <div class="summary-box">
                <div class="text-center font-bold mb-4">Charges Patronales</div>
                <div class="summary-row clearfix"><span class="summary-label">CFC Pat.</span><span class="summary-value">{{ number_format($sumCfcPat, 0, ',', ' ') }}</span></div>
                <div class="summary-row clearfix"><span class="summary-label">FNE</span><span class="summary-value">{{ number_format($sumFne, 0, ',', ' ') }}</span></div>
                <div style="border-top: 1px solid #444; margin-top: 5px; padding-top: 5px;" class="summary-row clearfix">
                    <span class="summary-label font-bold">Total</span><span class="summary-value">{{ number_format($totalPat, 0, ',', ' ') }}</span>
                </div>
            </div>

             <div class="summary-box" style="background-color: #f9f9f9;">
                <div class="text-center font-bold mb-4">Total à reverser</div>
                <div class="summary-row clearfix"><span class="summary-label">Total Salarial</span><span class="summary-value">{{ number_format($totalSal, 0, ',', ' ') }}</span></div>
                <div class="summary-row clearfix"><span class="summary-label">Total Patronal</span><span class="summary-value">{{ number_format($totalPat, 0, ',', ' ') }}</span></div>
                <div style="border-top: 1px solid #444; margin-top: 5px; padding-top: 5px;" class="summary-row clearfix">
                    <span class="summary-label font-bold">TOTAL</span><span class="summary-value">{{ number_format($totalSal + $totalPat, 0, ',', ' ') }}</span>
                </div>
            </div>
        </div>
    @endif

    @if ($toggleDeclaration === 'sociale')
         <table>
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Salaire Brut</th>
                    <th>Salaire Côtisable</th>
                    <th>PV Sal.</th>
                    <th>Total Sal</th>
                    <th>PV Pat.</th>
                    <th>AMP</th>
                    <th>AF</th>
                    <th>Total Pat.</th>
                </tr>
            </thead>
             <tbody>
                @forelse($listEmployee as $empid => $name)
                    <tr>
                        <td>{{ $name }}</td>
                        <td>{{ number_format($salaries['gross_salary'][$empid] ?? 0, 0, ',', ' ') }}</td>
                        <td>{{ number_format($salaries['contributory_salary'][$empid] ?? 0, 0, ',', ' ') }}</td>
                        <td>{{ number_format($empContribution[PayslipItems::CNPS_VIEILLESSE_SALARIALE->code()][$empid] ?? 0, 0, ',', ' ') }}</td>
                        <td>{{ number_format($empContribution[PayslipItems::CNPS_VIEILLESSE_SALARIALE->code()][$empid] ?? 0, 0, ',', ' ') }}</td>
                        <td>{{ number_format($emprContribution[PayslipItems::CNPS_VIEILLESSE_PATRONALE->code()][$empid] ?? 0, 0, ',', ' ') }}</td>
                         <td>{{ number_format($emprContribution[PayslipItems::CNPS_ACCIDENT_MALADIE_PRO->code()][$empid] ?? 0, 0, ',', ' ') }}</td>
                         <td>{{ number_format($emprContribution[PayslipItems::CNPS_ALLOCATION_FAMILIALE->code()][$empid] ?? 0, 0, ',', ' ') }}</td>
                        <td>
                            {{ number_format(
                                ($emprContribution[PayslipItems::CNPS_VIEILLESSE_PATRONALE->code()][$empid] ?? 0) +
                                ($emprContribution[PayslipItems::CNPS_ALLOCATION_FAMILIALE->code()][$empid] ?? 0) +
                                ($emprContribution[PayslipItems::CNPS_ACCIDENT_MALADIE_PRO->code()][$empid] ?? 0),
                                0, ',', ' ') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center">Aucune donnée</td></tr>
                @endforelse
            </tbody>
             <tfoot>
                 <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td>TOTAL</td>
                    <td>{{ number_format(array_sum($salaries['gross_salary'] ?? []), 0, ',', ' ') }}</td>
                    <td>{{ number_format(array_sum($salaries['contributory_salary'] ?? []), 0, ',', ' ') }}</td>
                    <td>{{ number_format(array_sum($empContribution[PayslipItems::CNPS_VIEILLESSE_SALARIALE->code()] ?? []), 0, ',', ' ') }}</td>
                    <td>{{ number_format(array_sum($empContribution[PayslipItems::CNPS_VIEILLESSE_SALARIALE->code()] ?? []), 0, ',', ' ') }}</td>
                    <td>{{ number_format(array_sum($emprContribution[PayslipItems::CNPS_VIEILLESSE_PATRONALE->code()] ?? []), 0, ',', ' ') }}</td>
                    <td>{{ number_format(array_sum($emprContribution[PayslipItems::CNPS_ACCIDENT_MALADIE_PRO->code()] ?? []), 0, ',', ' ') }}</td>
                    <td>{{ number_format(array_sum($emprContribution[PayslipItems::CNPS_ALLOCATION_FAMILIALE->code()] ?? []), 0, ',', ' ') }}</td>
                    <td>
                        {{ number_format(
                            array_sum($emprContribution[PayslipItems::CNPS_VIEILLESSE_PATRONALE->code()] ?? []) +
                            array_sum($emprContribution[PayslipItems::CNPS_ALLOCATION_FAMILIALE->code()] ?? []) +
                            array_sum($emprContribution[PayslipItems::CNPS_ACCIDENT_MALADIE_PRO->code()] ?? []),
                            0, ',', ' ') }}
                    </td>
                </tr>
            </tfoot>
         </table>

          @php
                $sumPvSal = array_sum($empContribution[PayslipItems::CNPS_VIEILLESSE_SALARIALE->code()] ?? []);
                $totalSalSocial = $sumPvSal;

                $sumPvPat = array_sum($emprContribution[PayslipItems::CNPS_VIEILLESSE_PATRONALE->code()] ?? []);
                $sumAf = array_sum($emprContribution[PayslipItems::CNPS_ALLOCATION_FAMILIALE->code()] ?? []);
                $sumAmp = array_sum($emprContribution[PayslipItems::CNPS_ACCIDENT_MALADIE_PRO->code()] ?? []);
                $totalPatSocial = $sumPvPat + $sumAf + $sumAmp;
            @endphp
             <div class="summary-container clearfix">
                <div class="summary-box">
                    <div class="text-center font-bold mb-4">Charges Salariales</div>
                    <div class="summary-row clearfix"><span class="summary-label">P. Vieillesse Sal.</span><span class="summary-value">{{ number_format($sumPvSal, 0, ',', ' ') }}</span></div>
                    <div style="border-top: 1px solid #444; margin-top: 5px; padding-top: 5px;" class="summary-row clearfix">
                        <span class="summary-label font-bold">Total</span><span class="summary-value">{{ number_format($totalSalSocial, 0, ',', ' ') }}</span>
                    </div>
                </div>
                 <div class="summary-box">
                    <div class="text-center font-bold mb-4">Charges Patronales</div>
                    <div class="summary-row clearfix"><span class="summary-label">P. Vieillesse Pat.</span><span class="summary-value">{{ number_format($sumPvPat, 0, ',', ' ') }}</span></div>
                    <div class="summary-row clearfix"><span class="summary-label">Alloc. Familiales</span><span class="summary-value">{{ number_format($sumAf, 0, ',', ' ') }}</span></div>
                     <div class="summary-row clearfix"><span class="summary-label">Acc. Travail / Mal. Pro</span><span class="summary-value">{{ number_format($sumAmp, 0, ',', ' ') }}</span></div>
                    <div style="border-top: 1px solid #444; margin-top: 5px; padding-top: 5px;" class="summary-row clearfix">
                        <span class="summary-label font-bold">Total</span><span class="summary-value">{{ number_format($totalPatSocial, 0, ',', ' ') }}</span>
                    </div>
                </div>
                 <div class="summary-box" style="background-color: #f9f9f9;">
                    <div class="text-center font-bold mb-4">Total à reverser</div>
                     <div class="summary-row clearfix"><span class="summary-label">Total Salarial</span><span class="summary-value">{{ number_format($totalSalSocial, 0, ',', ' ') }}</span></div>
                    <div class="summary-row clearfix"><span class="summary-label">Total Patronal</span><span class="summary-value">{{ number_format($totalPatSocial, 0, ',', ' ') }}</span></div>
                    <div style="border-top: 1px solid #444; margin-top: 5px; padding-top: 5px;" class="summary-row clearfix">
                        <span class="summary-label font-bold">TOTAL CNPS</span><span class="summary-value">{{ number_format($totalSalSocial + $totalPatSocial, 0, ',', ' ') }}</span>
                    </div>
                </div>
            </div>
    @endif
</body>
</html>