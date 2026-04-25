{{--
    Composant : delta-cards
    Fichier   : resources/views/components/delta-cards.blade.php

    USAGE MINIMAL (données passées en props) :
    ───────────────────────────────────────────
    <x-delta-cards :cards="[
        [
            'label'   => 'Brut',
            'current' => '12 450 000',
            'prev'    => '12 060 000',
            'delta'   => '+3.2%',
            'up'      => true,
            'color'   => 'emerald',
        ],
        [
            'label'   => 'Net',
            'current' => '9 876 000',
            'prev'    => '9 608 000',
            'delta'   => '+2.8%',
            'up'      => true,
            'color'   => 'blue',
        ],
    ]" />

    PROPS DISPONIBLES :
    ───────────────────
    @cards  (array, requis)  – tableau de cards (voir structure ci-dessus).
                               Chaque card accepte :
                                 label*   : string  – titre de la card
                                 current* : string  – valeur principale
                                 prev*    : string  – valeur période précédente
                                 delta*   : string  – ex. "+3.2%" ou "+2"
                                 up*      : bool    – true = hausse (vert), false = baisse (rouge)
                                 color    : string  – teinte de l'icône (emerald|blue|amber|violet|rose|sky|…)
                                                      (toute couleur Tailwind valide, défaut : slate)

    @cols   (string, optionnel, défaut : "2 sm:4")
            Nombre de colonnes sous la forme "mobile desktop".
            Exemples :
              "1 sm:2"    → 1 col mobile, 2 desktop
              "2 sm:4"    → 2 col mobile, 4 desktop  (défaut)
                            "1 sm:3"    → 1 col mobile, 3 desktop

    @gap    (string, optionnel, défaut : "3 sm:4")
            Espacement sous la forme "mobile desktop".

    @mb     (string, optionnel, défaut : "mb-5")
            Marge basse du wrapper.
--}}

@props([
'cards' => [],
'cols' => '2 sm:4',
'gap' => '3 sm:4',
'mb' => 'my-5',
])

@php
/*──────────────────────────────────────────────────────────────
| Parse "mobile desktop" → classes Tailwind
*──────────────────────────────────────────────────────────────*/
$parseCols = function (string $value): string {
$parts = explode(' ', trim($value));
if (count($parts) === 1) {
return 'grid-cols-' . $parts[0];
}
// Le deuxième segment contient déjà le préfixe breakpoint (ex. "sm:4")
[$mob, $desk] = $parts;
[$bp, $n] = str_contains($desk, ':') ? explode(':', $desk, 2) : ['sm', $desk];
return "grid-cols-{$mob} {$bp}:grid-cols-{$n}";
};

$parseGap = function (string $value): string {
$parts = explode(' ', trim($value));
if (count($parts) === 1) {
return 'gap-' . $parts[0];
}
[$mob, $desk] = $parts;
[$bp, $n] = str_contains($desk, ':') ? explode(':', $desk, 2) : ['sm', $desk];
return "gap-{$mob} {$bp}:gap-{$n}";
};

$colClass = $parseCols($cols);
$gapClass = $parseGap($gap);

/*──────────────────────────────────────────────────────────────
| Map couleur → classes Tailwind (nécessaire car Tailwind purge
| les classes non présentes dans les fichiers Blade ; si tu
| utilises le JIT en mode "safelist", tu peux supprimer ce map
| et construire les classes dynamiquement).
*──────────────────────────────────────────────────────────────*/
$colorMap = [
'emerald' => ['dot' => 'bg-emerald-400', 'ring' => 'ring-emerald-400/20'],
'blue' => ['dot' => 'bg-blue-400', 'ring' => 'ring-blue-400/20'],
'amber' => ['dot' => 'bg-amber-400', 'ring' => 'ring-amber-400/20'],
'violet' => ['dot' => 'bg-violet-400', 'ring' => 'ring-violet-400/20'],
'rose' => ['dot' => 'bg-rose-400', 'ring' => 'ring-rose-400/20'],
'sky' => ['dot' => 'bg-sky-400', 'ring' => 'ring-sky-400/20'],
'teal' => ['dot' => 'bg-teal-400', 'ring' => 'ring-teal-400/20'],
'pink' => ['dot' => 'bg-pink-400', 'ring' => 'ring-pink-400/20'],
'indigo' => ['dot' => 'bg-indigo-400', 'ring' => 'ring-indigo-400/20'],
'orange' => ['dot' => 'bg-orange-400', 'ring' => 'ring-orange-400/20'],
];
$defaultColor = ['dot' => 'bg-slate-400', 'ring' => 'ring-slate-400/20'];
@endphp

<div class="grid {{ $colClass }} {{ $gapClass }} {{ $mb }} ">
    @foreach ($cards as $card)
    @php
    $color = $colorMap[$card['color'] ?? ''] ?? $defaultColor;
    $isUp = $card['up'] ?? true;
    $deltaClass = $isUp ? 'text-emerald-400' : 'text-rose-400';
    $arrow = $isUp ? '↑' : '↓';
    @endphp

    <div class="bg-white/3 border border-white/6 rounded-xl p-4
                    hover:bg-white/5.5 hover:border-white/10
                    transition-colors duration-200">

        {{-- En-tête : label + badge delta --}}
        <div class="flex items-start justify-between mb-2">
            <div class="flex items-center gap-1.5">
                {{-- Point coloré (indicateur de catégorie) --}}
                <span class="inline-block w-1.5 h-1.5 rounded-full ring-2 {{ $color['dot'] }} {{ $color['ring'] }}"></span>
                <p class="text-xs text-white/40">{{ $card['label'] }}</p>
            </div>

            <span class="inline-flex items-center gap-0.5 text-xs font-bold {{ $deltaClass }}">
                <span class="text-[10px]">{{ $arrow }}</span>
                {{ $card['delta'] }}
            </span>
        </div>

        {{-- Valeur principale --}}
        <p class="text-base sm:text-lg font-bold text-white font-mono tracking-tight">
            {{ $card['current'] }}
        </p>

        {{-- Comparaison période précédente --}}
        @if (array_key_exists('prev', $card) )
        <p class="text-xs text-white/25 mt-0.5">{{ $card['prev'] }}</p>
        @endif
    </div>
    @endforeach
</div>