    <div
        class="bg-white dark:bg-zinc-900 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-800 transition-colors duration-200 font-sans text-zinc-900 dark:text-zinc-100">

        <!-- En-tête / Header - Using Table for consistent 2-col layout in PDF -->
        <div @class([
            'text-white transition-colors duration-200',
            'bg-green-600 dark:bg-green-700' =>
                $status === App\Enums\StatusEnum::APPROVED,
            'bg-blue-600 dark:bg-blue-700' =>
                $status === App\Enums\StatusEnum::PENDING,
        ])>
            <div class="p-6">
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-bold tracking-tight">LIVRE DE PAIE</h1>
                    <p class="text-sm opacity-90">Période du 01/{{ now()->format('m/Y') }} au
                        {{ now()->endOfMonth()->format('d/m/Y') }}</p>
                    <p class="text-sm opacity-90">Paiement le {{ now()->endOfMonth()->format('d/m/Y') }} par
                        virement bancaire</p>
                </div>

                <!-- Layout Table for Header Details -->
                <table class="w-full" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="align-top text-left" style="width: 50%; vertical-align: top;">
                            <h2 class="text-xl font-semibold">{{ $company->name }}</h2>
                            <p class="text-sm opacity-90 leading-relaxed">
                                {{ $company->phone . ' | ' . $company->email }}<br>
                                {{ $company->adresse }}<br>
                                {{ $company->city }}
                            </p>
                        </td>
                        <td class="align-top " style="width: 50%; vertical-align: top; text-align: right;">
                            <div class="space-y-1">
                                <p><span class="font-semibold">NIU :</span> {{ $company->niu }}</p>
                                <p><span class="font-semibold">N° CNPS :</span> {{ $company->cnps }}
                                </p>
                                <p><span class="font-semibold">RCCM :</span> {{ $company->rccm }}
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="mt-6 text-center pt-4 border-t border-blue-500/30">
                    <p class="text-xs uppercase tracking-wider opacity-75 mb-1">Droit applicable</p>
                    <p class="font-medium">Conv coll de transport</p>
                </div>
            </div>
        </div>

        <!-- Tableau des éléments de paie -->
        <div class="p-6">
            @if (isset($showPagination) && $showPagination)
                <div class="flex items-center justify-between mb-4">
                    <flux:button wire:click="previousPage" icon="chevron-left" :disabled="$currentPage <= 1"
                        size="sm" />
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        Page {{ $currentPage }} / {{ $totalPages }}
                    </span>
                    <flux:button wire:click="nextPage" icon="chevron-right" :disabled="$currentPage >= $totalPages"
                        size="sm" />
                </div>
            @endif
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-800">
                <table class="w-full text-sm text-left data-table">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-500 dark:text-zinc-400 font-medium">
                        <tr>
                            <th class="py-3 px-4 w-16">N°</th>
                            <th class="py-3 px-4">Désignation</th>
                            @foreach ($listEmployee as $emp)
                                <th class="py-3 px-4 ">{{ $emp }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach ($matrix as $ma)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">{{ $ma['code'] }}</td>
                                <td class="py-3 px-4 font-medium">{{ $ma['element'] }}</td>
                                @foreach ($listEmployee as $empid => $name)
                                    <td class="py-3 px-4  tabular-nums">{{ $ma[$empid] ?? 0 }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr class="bg-zinc-100 dark:bg-zinc-800 font-bold text-zinc-900 dark:text-white">
                            <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">#</td>
                            <td class="py-3 px-4 font-extrabold">Salaire Brut</td>
                            @foreach ($listEmployee as $empid => $name)
                                <td class="py-3 px-4  tabular-nums">{{ $salaries[$empid]['gross_salary'] ?? 0 }}</td>
                            @endforeach
                        </tr>

                        @foreach ($employeeContribution as $empCon)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">{{ $empCon['code'] }}</td>
                                <td class="py-3 px-4 font-medium">{{ $empCon['element'] }}</td>
                                @foreach ($listEmployee as $empid => $name)
                                    <td class="py-3 px-4  tabular-nums">{{ $empCon[$empid] ?? 0 }}</td>
                                @endforeach
                            </tr>
                        @endforeach

                        @foreach ($employerContribution as $empCon)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">{{ $empCon['code'] }}</td>
                                <td class="py-3 px-4 font-medium">{{ $empCon['element'] }}</td>
                                @foreach ($listEmployee as $empid => $name)
                                    <td class="py-3 px-4  tabular-nums">{{ $empCon[$empid] ?? 0 }}</td>
                                @endforeach
                            </tr>
                        @endforeach


                        @foreach ($retenues as $ret)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">{{ $ret['code'] }}</td>
                                <td class="py-3 px-4 font-medium">{{ $ret['element'] }}</td>
                                @foreach ($listEmployee as $empid => $name)
                                    <td class="py-3 px-4  tabular-nums">{{ $ret[$empid] ?? 0 }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr class="bg-zinc-100 dark:bg-zinc-800 font-bold text-zinc-900 dark:text-white ">
                            <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">#</td>
                            <td class="py-3 px-4 font-extrabold">Net à déduire</td>
                            @foreach ($listEmployee as $empid => $name)
                                <td class="py-3 px-4  tabular-nums">{{ $salaries[$empid]['nad'] ?? 0 }}</td>
                            @endforeach
                        </tr>
                        <tr class="bg-zinc-100 dark:bg-zinc-800 font-bold text-zinc-900 dark:text-white ">
                            <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">#</td>
                            <td class="py-3 px-4 font-extrabold">Net à payer</td>
                            @foreach ($listEmployee as $empid => $name)
                                <td class="py-3 px-4  tabular-nums">{{ $salaries[$empid]['nap'] ?? 0 }}</td>
                            @endforeach
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 text-xs text-center text-gray-500">
            <p>Document généré le {{ now()->format('d/m/Y à H:i') }} | {{ $company->name }}</p>
        </div>
    </div>
