<?php

use App\Enums\CivilityEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\FeatureEnum;
use App\Enums\NationalityEnum;
use App\Jobs\calculateImpotJob;
use App\Jobs\CalculatePayslipJob;
use App\Livewire\Forms\EmployeeForm;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public $id;
    public EmployeeForm $form;

    public function mount()
    {
        $this->syndicat = $this->employee->data["syndicat"] ?? false;
    }
    public function render()
    {
        return $this->view()->title("Profil de " . $this->employee->shortName);
    }

    public function showPayslipModal()
    {
        $this->employee->payslip?->delete();
        CalculatePayslipJob::dispatch($this->employee);
        Flux::modal("payslip-modal")->show();
    }

    public function downloadPdf()
    {
        $pdf = Pdf::loadView("pdf.payslip", ["employee" => $this->employee]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "payslip.pdf");
    }

    public function with()
    {
        $sal = [];
        $contributionsArray = [];
        if ($this->employee && $this->employee->payslip) {
            $sal = $this->employee->payslip->formatted_salaries;
            $contributionsArray = $this->employee->payslip->formatted_contributions;
        }

        return [
            "employee" => $this->employee,
            "salaries" => $sal,
            "contributions" => $contributionsArray,
        ];
    }

    #[Computed]
    public function employee()
    {
        return Employee::whereId($this->id)
            ->with(["employeeContributions", "employerContributions", "overtimes", "payslip", "contractArchives", "remunerations", "leaves", "overtimesSnapshot", "leavesSnapshot", "remunerationsSnapshot"])
            ->firstOrFail();
    } //

    public function editEmployee()
    {
        $this->form->setEmployee($this->employee);
        Flux::modal("edit-employee")->show();
    }
    public function update()
    {
        $this->form->isCreating = false;
        $this->form->update();
        Flux::modal("edit-employee")->close();
        $this->form->reset();
        Flux::toast(variant: "success", text: __("toast.profil.success", ["name" => $this->employee->shortName]));
    }

    public bool $syndicat = false;
    public function updatedSyndicat()
    {
        // Update the employee data
        $data = $this->employee->data ?? [];
        $data["syndicat"] = $this->syndicat;
        $this->employee->data = $data;
        $this->employee->save();

        calculateImpotJob::dispatch($this->employee);
        Flux::toast(variant: "success", text: "Veuillez patienter pour la prise en compte du syndicat..");
    }
};
?>

<div>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href='{{ route("employees") }}'    >{{ __("Employé") }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $this->employee->shortName }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    <div class="flex items-center justify-between  mb-16">
        <div class="flex items-center justify-start gap-4">
            <flux:avatar name="{{ $this->employee->name }}" />
            <div>
                <flux:heading level="1">{{ $this->employee->shortName }}</flux:heading>
                <flux:text>{{ $this->employee->job_title . " . " . $this->employee->department?->label() }}</flux:text>
            </div>
        </div>
        <div class="flex items-center gap-2">

            <flux:button variant="primary" wire:click="showPayslipModal">Voir le bulletin</flux:button>
            <flux:button wire:click="editEmployee" icon="pencil" />
        </div>
    </div>


    <x-delta-card :cards='[
        [
            "label" => "Salaire de base",
            "current" => number_format($this->employee->base_salary, 0, ",", " ") . " F cfa",
            "delta" => "",
            "color" => "blue",
        ],
        [
            "label" => "Date embauche",
            "current" => $this->employee->start_date?->format("d M Y") ?? "N/A",
            "delta" => "",
            "color" => "emerald",
        ],
        [
            "label" => "Ancienneté",
            "current" => $this->employee->anc,
            "delta" => "",
            "color" => "violet",
        ],
        [
            "label" => "Type de contrat",
            "current" => $this->employee->contract_type?->label(),
            "delta" => "",
            "color" => "amber",
        ],
    ]' />
    @if($this->employee->isExpired())
<flux:callout color="rose" class="mb-4"  icon="exclamation-circle" heading="Cette alerte apparaît lorsque le contrat de votre resource est expiré ou est en stage, Veuillez regularisez dans l'onglet conntrat."/>
            @endif

    <x-ui.tabs variant="non-contained">
    
        <x-ui.tab.group>
            <x-ui.tab label="Vue d'ensemble" icon="globe-alt" />
            <x-ui.tab label="Rémunération" icon="credit-card" />
            <x-ui.tab label="Contrat" icon="document" />
            <x-ui.tab label="Absences" icon="clock" />
            <x-ui.tab label="Heures supps." icon="clock" />
            <x-ui.tab label="Documents" icon="folder" />
        </x-ui.tab.group>
            
        <x-ui.tab.panel>
            <livewire:employees.employee-general :employee="$this->employee" />

        </x-ui.tab.panel>
        <x-ui.tab.panel>
            @if(!$this->employee->isExpired())
            <livewire:employees.employee-remuneration :employee="$this->employee" />
            @else
            <x-empty-state message="Le contrat de votre employé est expiré ou n'a pas accès à ce  module" />
            @endif
        </x-ui.tab.panel>
        <x-ui.tab.panel>
            <livewire:employees.employee-contract :employee="$this->employee" />
        </x-ui.tab.panel>
        <x-ui.tab.panel>
            @if(!$this->employee->isExpired())
            <livewire:employees.employee-leaves :employee="$this->employee" />
            @else
            <x-empty-state message="Le contrat de votre employé est expiré ou n'a pas accès à ce  module" />
            @endif

        </x-ui.tab.panel>
        <x-ui.tab.panel>
            @if(!$this->employee->isExpired())
            <livewire:employees.employee-overtime :employee="$this->employee" />
            @else
            <x-empty-state message="Le contrat de votre employé est expiré ou n'a pas accès à ce  module" />
            @endif

        </x-ui.tab.panel>
        <x-ui.tab.panel>
            <livewire:employees.employee-document :employee="$this->employee" />

        </x-ui.tab.panel>
</x-ui.tabs>
    

    <flux:modal name="edit-employee" class="min-w-225">
        <div class="space-y-6 pt-5">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Editer l'employee {{ $this->employee->name }}</flux:heading>
            </div>
            <flux:switch label="Syndicat" description="{{ __('L\'employé fait-il partie d\'un syndicat ?') }}"
                wire:model.live="syndicat" />


            <form wire:submit="update" class="container mx-auto p-4 max-w-4xl space-y-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                        {{-- <x-icon name="user" class="w-5 h-5 text-primary-600" /> --}}
                        {{ __("Employee Details") }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Civility -->
                        <flux:select id="civility" wire:model="form.civility" :label="__('Civility')">
                            <flux:select.option value=""> Choisir une option</flux:select.option>
                            @foreach (CivilityEnum::cases() as $case)
                                <option value="{{ $case->value }}">{{ $case->name }}</option>
                            @endforeach
                        </flux:select>

                        <!-- Name -->
                        <flux:input id="name" wire:model="form.name" type="text" :label="__('Full Name')" />

                        <!-- Email -->
                        <flux:input id="email" wire:model="form.email" type="email"
                            :label="__('Email Address')" />

                        <!-- Phone -->
                        <flux:input id="phone" wire:model="form.phone" type="text"
                            :label="__('Phone (9 digits)')" />

                        <!-- Birth Date -->
                        <flux:input id="birth_date" wire:model="form.birth_date" type="date"
                            :label="__('Birth Date')" />

                        <!-- Nationality -->
                        <flux:select id="nationality" wire:model="form.nationality" :label="__('Nationality')">
                            <flux:select.option value=""> Choisir une option</flux:select.option>
                            @foreach (NationalityEnum::cases() as $case)
                                <option value="{{ $case->value }}">{{ $case->name }}</option>
                            @endforeach
                        </flux:select>

                        <!-- Number of Children -->
                        <flux:input id="child" wire:model="form.child" type="number" min="0"
                            :label="__('Number of Children')" />

                        <!-- NIU -->
                        <flux:input id="niu" wire:model="form.niu" type="text" :label="__('NIU')" />

                        <!-- CNPS -->
                        <flux:input id="cnps_number" wire:model="form.cnps_number" type="text"
                            :label="__('CNPS Number')" />
                    </div>

                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                        {{-- <x-icon name="user" class="w-5 h-5 text-primary-600" /> --}}
                        {{ __("Congés Details") }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Name -->
                        <flux:input id="name" wire:model="form.leaves_majority" type="number" step="any"
                            :label="__('Nbres jours congés mensuel')" />

                        <!-- Email -->
                        <flux:input id="email" wire:model="form.leaves_seniority" type="number" step="any"
                            :label="__('Nbres jours congés acquis/ancienneté')" />

                        <!-- Phone -->
                        <flux:input id="phone" wire:model="form.leaves_child" type="number" step="any"
                            :label="__('Nbres jours congés acquis/enfants')" />

                    </div>
                </div>
                <div class="flex gap-2 justify-end">
                    <flux:spacer />
                    <flux:button variant="primary" type="submit">
                        Enregistrer
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>


    <flux:modal name="payslip-modal" class="min-w-225">
        <div class="space-y-6 pt-5">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Bulletin de {{ $this->employee->name }}</flux:heading>
            </div>
            <div class="container mx-auto p-4 max-w-4xl">
                @if ($this->employee->payslip && $this->employee->contract_type != ContractTypeEnum::INTERNSHIP)
                    @include("pdf.payslip-content", [
                        "employee" => $this->employee,
                        "salaries" => $salaries,
                        "contributions" => $contributions,
                    ])
                @elseif (!$this->employee->payslip)
                    <div wire:poll.visible.5s="showPayslipModal"
                        class="flex flex-col items-center justify-center h-full p-8 text-zinc-500">
                        <div
                            class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-700 dark:bg-zinc-800">
                            <svg class="h-8 w-8 animate-spin text-blue-600 dark:text-blue-500"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <div class="flex flex-col">
                                <span class="text-lg font-semibold text-zinc-900 dark:text-white">Génération du
                                    bulletin de
                                    paie...</span>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">Cette opération peut prendre
                                    quelques
                                    instants. La boîte de dialogue se mettra à jour automatiquement.</span>
                            </div>
                        </div>
                    </div>
                @elseif($this->employee->contract_type === ContractTypeEnum::INTERNSHIP)
                    <flux:text>{{ $this->employee->name . " est un stagiaire, et ne peut avoir un bulletin de paie." }}
                    </flux:text>
                @endif
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button variant="primary" wire:click="downloadPdf" icon="arrow-down-tray">
                    Télécharger
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
