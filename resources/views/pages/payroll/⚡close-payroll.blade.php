<?php

use App\Enums\StatusEnum;
use App\Jobs\ClosePayrollJob;
use Carbon\Carbon;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Clôturer la paie')] class extends Component {

    //TODO la date a laquelle il choisi pour programmer sa paie c'est la plage entre le 20 du mois en cours et le dernier jour du mois, pour eviter qu'il ne fasse des fantaisie et choisi une date au hasard, passe une regle validdate par exple oou un truc du genre pour lui dire que la date doit etre comprise entre le 20 du mois et le dernier jour du mois
    public $company; 
    public int $hasNotPayslip = 0;
    public bool $closeNow = true;
    public bool $closeLater = false;
    public $closureDate;
    public bool $sendPayslipsByEmail = false;
    public $closure;
    public string $ref; // Public property to allow setting custom ref for tests

    
    public function mount()
    {
                $this->closureDate = now()->addDay()
            ->format('Y-m-d');
    }
    public function closePayroll()
    {
        if (!$this->closeNow &&    !$this->closeLater) {
            Flux::toast(variant: 'danger', text: 'Aucun mode de clôture n\'as été sélectionné pour votre paie.');
            return;
        }

        if ($this->closeLater && $this->closureDate <= now()->format('Y-m-d')) {
            Flux::toast(variant: 'danger', text: 'La date de clôture choisi ne doit pas être égale ou inférieur à la date du jour.');;
            return;
        }

        $ref = now()->format('m-Y');


        // Vérification du verrouillage avant toute action
        $existingClosure = $this->company->payrollClosures->where('ref', $ref)->first();

        if ($existingClosure && $existingClosure->status === StatusEnum::LOCKED) {
            Flux::toast(variant: 'warning', text: 'Cette période est verrouillée, vous ne pouvez plus la modifier.');
            return;
        }

        $this->closure = $this->company->payrollClosures()->updateOrCreate(
            ['ref' => $ref],
            [
                'status' => $this->closeNow ? StatusEnum::CLOSED : StatusEnum::DRAFT,
                'closed_by' => auth()->user()->name,
                'closed_at' => now(),
                'send_payslips_by_email' => $this->sendPayslipsByEmail,
                'scheduled_at' => $this->closeNow ? null : Carbon::parse($this->closureDate)->startOfDay(),
            ]
        );
        unset($this->company);

        if ($this->closeNow) {
            ClosePayrollJob::dispatch($this->closure, true, auth()->user()->company_id);
            Flux::toast(variant: 'success', text: 'La clôture immédiate a été lancée avec succès.');
            return;
        }
        Flux::toast(variant: 'success', text: 'La clôture a été programmée avec succès.');
        return $this->redirect(route('pay'), navigate: true);
    }


    #[Computed]
    private function company()
    {
        return  auth()
            ->user()
            ->company()
            ->with(['payrollClosures', 'declarations'])
            ->first();
    }

    public function updatedCloseNow()
    {
        $this->closeLater = false;
    }
    public function updatedCloseLater()
    {
        $this->closeNow = false;
    }
};
?>

<div>

    {{-- Header --}}
    <div class="space-y-2">
        <flux:heading size="xl">
            Clôture de la paie
        </flux:heading>

        <flux:text class="text-gray-500">
            Configurez les modalités de clôture de votre paie et validez les paramètres avant exécution.
        </flux:text>
    </div>

    {{-- 1. Mode de clôture --}}
    <x-container class="flex flex-col gap-4 ">
        <flux:heading size="lg">1. Mode de clôture</flux:heading>

        <flux:switch label="Clôturez immédiatement votre paie" description="Lancez la clôture dès validation." wire:model.live="closeNow" />
        <flux:switch label="Programmer la clôture" description="Planifiez la clôture à une date ultérieure." wire:model.live="closeLater" />


        @if($closeLater)
        <flux:field>
            <flux:label>Choississez la date de clôture</flux:label>

            <flux:input
                type="date"
                wire:model.blur="closureDate" />
        </flux:field>
        @endif

    </x-container>

    {{-- 2. Notifications --}}
    <x-container class="flex flex-col gap-4 ">
        <flux:heading size="lg">2. Notification des bulletins</flux:heading>

        <flux:switch label="Notification mail" description="Envoyez les bulletins de paie et informez vos collaborateurs de leur disponibilité par mail." wire:model.live="sendPayslipsByEmail" />


    </x-container>

    {{-- 3. Récapitulatif --}}
    <x-container class="flex flex-col gap-4 ">

        <flux:heading size="lg">3. Récapitulatif</flux:heading>

        <flux:text>
            Vérifiez les informations avant de finaliser la clôture.
        </flux:text>

        <div>

            <div class="flex justify-between">
                <span>Mode</span>
                <span class="font-medium">
                    {{ $closeNow ? 'Clôture immédiate' : 'Clôture Programmée' }}
                </span>
            </div>

            <div class="flex justify-between">
                <span>Date de clôture</span>
                <span class="font-medium">
                    {{ $closeNow 
                        ? 'Immédiatement'
                        : \Carbon\Carbon::parse($closureDate)->format('d/m/Y') 
                    }}
                </span>
            </div>

            <div class="flex justify-between">
                <span>Notification email</span>
                <span class="font-medium">
                    {{ $sendPayslipsByEmail ? 'Activée' : 'Désactivée' }}
                </span>
            </div>

        </div>

        <flux:callout>
            Une fois la paie clôturée, la préparation pour le prochain mois de paie seront mis à jour.
        </flux:callout>
    </x-container>


    {{-- Actions --}}
    <div class="flex justify-end gap-3 pt-6 border-t">

        <flux:button
            onclick="history.back()">
            Annuler
        </flux:button>

        <flux:button
            variant="primary"
            wire:click="closePayroll">
            Clôturer la paie
        </flux:button>

    </div>


</div>