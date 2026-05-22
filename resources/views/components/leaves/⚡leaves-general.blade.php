<?php

use App\Charts\LeaveChart;
use Livewire\Component;

new class extends Component {
public $company;
public function with(): array
    {
        return [
            'chart' => app(LeaveChart::class, ['company' => $this->company])->leavePerDepertment(),
        ];
    }
}; ?>

<div>
    <x-container >
        {!! $chart->container() !!}
    </x-container>
    {{ $chart->script() }}
    </div>