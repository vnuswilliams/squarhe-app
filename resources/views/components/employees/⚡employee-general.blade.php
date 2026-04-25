<?php

use App\Jobs\calculateImpotForEmployee;
use Flux\Flux;
use Livewire\Component;

new class extends Component
{
    
    
    public bool $syndicat = false;
   public function updatedSyndicat()
    {

        calculateImpotForEmployee::dispatch($this->employee, $this->syndicat);
        Flux::toast(variante: "success", teaxt: 'Veuillez patienter pour la prise en compte du syndicat..');
    }
};
?>

<div>
      <x-container class="mb-4">
        <flux:switch label="Syndicat" description=" {{ __('L\'employé fait-il partie d\'un syndicat ?') }}" wire:model.live="syndicat" />

    </x-container>
    {{-- The best way to take care of the future is to take care of the present moment. - Thich Nhat Hanh --}}
</div>