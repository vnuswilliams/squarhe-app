<?php

use App\Concerns\HasTableOptions;
use App\Enums\DocumentAccessEnum;
use App\Enums\DocumentTypeEnum;
use App\Livewire\Forms\EmployeeDocumentForm;
use App\Models\Document;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

new class extends Component{  
    use HasTableOptions;
use WithFileUploads;
use WithoutUrlPagination, WithPagination;

    public $employee;

    public EmployeeDocumentForm $form;

    #[Computed]
    public function documents()
    {
        $paginator = $this->baseQuery()
            ->when(filled($this->searchQuery), fn($q) => $this->applySearch($q))
            ->when(filled($this->sortBy), fn($q) => $this->applySorting($q))
            ->latest()
            ->paginate(15);

       
        return $paginator;
    }

    protected function applySearch($query)
    {
        return $query->where(function ($q) {
            $q->where("name", "like", "%" . $this->searchQuery . "%")
                ->orWhere("notes", "like", "%" . $this->searchQuery . "%")
                ->orWhere("added_by", "like", "%" . $this->searchQuery . "%")
                ->orWhere("type", "like", "%" . $this->searchQuery . "%");
        });
    }

    protected function baseQuery()
    {
        return Document::whereEmployeeId($this->employee->id);
    }

    public function save()
    {
        $this->form->employee_id = $this->employee->id;
        $this->form->isCreating = true;

        $this->form->create();

        Flux::toast(variant: "success", text: __("toast.document.createDocumentSuccessfull"));
        $this->form->reset();
    }

    public function edit($documentId)
    {
        $documentToUpdate = $this->documents->where('id',$documentId)->first();
if($documentToUpdate){

    $this->form->setDocument($documentToUpdate);
    Flux::modal("edit-document-modal")->show();
    }
    }

    public function update()
    {
        $this->form->update();
        Flux::modal("edit-document-modal")->close();
        Flux::toast(variant: "success", text: __("toast.document.updateDocumentSuccessfull"));

        $this->form->reset();
    }

    public $documentToDelete = null;

    public function confirmBeforeDelete($idDocumentWeWantToDelete)
    {
        $this->documentToDelete = $this->documents->where('id', $idDocumentWeWantToDelete)->first();
        Flux::modal("delete-document-modal")->show();
    }

    public function delete()
    {
        if ($this->documentToDelete) {
            Gate::authorize("delete", [Document::class, $this->documentToDelete]);

            Storage::disk("public")->exists($this->documentToDelete->path) ?: Storage::disk("public")->delete($this->documentToDelete->path);

            $this->documentToDelete->delete();

            Flux::toast(variant: "success", text: __("toast.document.deleteDocumentSuccessfull"));

            Flux::modal("delete-document-modal")->close();
            $this->documentToDelete = null;
        }
    }

    public function downloadDoc($id)
    {
        $docToDownload = $this->documents->where('id', $id)->first();
        if($docToDownload):
        Gate::authorize("view", [Document::class, $docToDownload]);

        $name = Str::snake($this->employee->shortName . " " . $docToDownload->type?->value . " " . $docToDownload->name . " " . now()->format("_d_m_Y_H_i_s"));

        Flux::toast(variant:'success', text:__('toast.download'));
        return Storage::disk("public")->download($docToDownload->path, $name);
endif;

    }
};
?>
<div x-data="{ activeForm: null }">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading level="1" class="font-bold">Ajouter un document a votre collaborateur</flux:heading>
            <flux:text class="text-gray-300">Il sera visble en fonction de votre niveau d'accès</flux:text>
        </div>

        <flux:button tooltip="Ajouter un nouveau document" @click="activeForm = 'a' " variant="primary" icon="plus" />
    </div>
    @if ($this->documents->isNotEmpty())
        {{-- Delta Card for Documents --}}
        <x-delta-card :cards='[
            [
                "label" => "Total Documents",
                "current" => $this->documents->count(),
                "delta" => "",
                "color" => "blue",
            ],
        ]'  />
    @endif
    <x-container x-show="activeForm === 'a' " x-transition>
        <form wire:submit="save" class="space-y-6" id="add-document-form" enctype="multipart/form-data">


            {{-- Employee ID (hidden) --}}
            <div class="grid sm:grid-cols-3 gap-4">
                {{-- Nom du document --}}
                <flux:input wire:model="form.name" label="Nom du document" placeholder="Ex : Contrat de travail" />

                {{-- Type de document --}}
                <flux:select wire:model="form.type" label="Type de document" placeholder="Choisir un type">
                    <option value="">Choisir une option</option>
                    @foreach (DocumentTypeEnum::options() as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach

                </flux:select>
                {{-- Droit d’accès --}}
                <flux:select wire:model="form.access" label="Niveau d’accès" placeholder="Choisir le niveau d’accès">
                    <option value="">Choisir une option</option>
                    @foreach (DocumentAccessEnum::options() as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </flux:select>
            </div>


            {{-- Description --}}
            <flux:textarea wire:model="form.notes" label="Notes"
                placeholder="Ex : Contrat du salarié pour l'année 2025" rows="3" />

            {{-- Fichier --}}
            <flux:input type="file" wire:model="form.file" label="Fichier" accept=".pdf,.doc,.docx,.jpg,.png" />



            {{-- Ajouté par (si manuel, sinon retirer ce champ) --}}

            {{-- Bouton d’enregistrement --}}
            <div class="flex items-center justify-end  gap-2">
                <flux:button @click="activeForm = null ">
                    {{ __("Cancel") }}

                </flux:button>
                <flux:button variant="primary" type="submit" class="cursor-pointer w-full">
                    Enregistrer le document

                </flux:button>

            </div>
        </form>
    </x-container>


    <x-container>
        <div class="flex items-center gap-2">


            {{-- Search --}}
            <div class="ml-auto">
                <flux:input placeholder='{{ __("Rechercher...") }}' wire:model.live.debounce.300ms="searchQuery" />
            </div>

        </div>
        <x-ui.table.container>

            <x-ui.table variant="default" wire:loading loadOn="pagination, search, sorting">
                <x-ui.table.header sticky class="dark:bg-neutral-900 bg-white" id="table">
                    <x-ui.table.columns>
                        <x-ui.table.head column="name" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">
                            {{ __("Nom du doc.") }}
                        </x-ui.table.head>

                        <x-ui.table.head column="name" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">
                            {{ __("Type") }}
                        </x-ui.table.head>
                        <x-ui.table.head column="name" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">
                            {{ __("Ajouté par") }}
                        </x-ui.table.head>
                        <x-ui.table.head column="name" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">
                            {{ __("Accesible par") }}
                        </x-ui.table.head>
                        <x-ui.table.head column="name" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">
                            {{ __("Ajouté le") }}
                        </x-ui.table.head>
                        <x-ui.table.head>
                            {{ __("Actions") }}
                        </x-ui.table.head>

                    </x-ui.table.columns>
                </x-ui.table.header>

                <x-ui.table.rows>
                    @forelse ($this->documents as $doc)
                        <x-ui.table.row :key="$doc->id"
                            class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">

                            <x-ui.table.cell>
                                <flux:heading class="flex items-center gap-2">
                                    {{ $doc->name }}
                                    <flux:tooltip toggleable>
                                        <flux:button icon="information-circle" size="sm" variant="ghost" />
                                        <flux:tooltip.content>
                                            {{ $doc->notes }}
                                        </flux:tooltip.content>
                                    </flux:tooltip>

                                </flux:heading>
                            </x-ui.table.cell>
                            <x-ui.table.cell>
                                {{ $doc->type->label() }}
                            </x-ui.table.cell>
                            <x-ui.table.cell>
                                {{ $doc->added_by }}
                            </x-ui.table.cell>
                            <x-ui.table.cell>
                                {{ $doc->access }}
                            </x-ui.table.cell>
                            <x-ui.table.cell>
                                {{ $doc->created_at->translatedFormat("d M Y") }}
                            </x-ui.table.cell>
                            <x-ui.table.cell>
                                <div class="flex items-center gap-2">
                                    
                                    <flux:button  icon="arrow-down-tray"  tooltip="{{ __('Telecharger le document')}}"                                  wire:click="downloadDoc('{{ $doc->id }}')" />
                                    <flux:button wire:click="edit('{{ $doc->id }}')" square icon="pencil" tooltip="{{ __('Modifier') }}" />
                                    <flux:button wire:click="confirmBeforeDelete('{{ $doc->id }}')" square tooltip="{{__('Supprimer le document')}}"                                         icon="trash" />
                                </div>
                            </x-ui.table.cell>
                        </x-ui.table.row>
                    @empty
                        <x-ui.table.empty>
                            <x-empty-state
                                message='                     {{ __("Aucun documents trouvés pour ") . $this->employee->name }}' />
                        </x-ui.table.empty>
                    @endforelse
                </x-ui.table.rows>
            </x-ui.table>
            {{ $this->documents->links(data: ["scrollTo" => "#table"]) }}

        </x-ui.table.container>

    </x-container>


    <flux:modal name="edit-document-modal" class="min-w-225">
        <div class="space-y-6 pt-5">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Mettre à jour un congé ou une absence</flux:heading>
            </div>
            <form wire:submit="update" class="container mx-auto p-4 max-w-4xl space-y-4">


                {{-- Employee ID (hidden) --}}
                <div class="grid sm:grid-cols-3 gap-4">
                    {{-- Nom du document --}}
                    <flux:input wire:model="form.name" label="Nom du document" placeholder="Ex : Contrat de travail" />

                    {{-- Type de document --}}
                    <flux:select wire:model="form.type" label="Type de document" placeholder="Choisir un type">
                        <option value="">Choisir une option</option>
                        @foreach (DocumentTypeEnum::options() as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach

                    </flux:select>
                    {{-- Droit d’accès --}}
                    <flux:select wire:model="form.access" label="Niveau d’accès"
                        placeholder="Choisir le niveau d’accès">
                        <option value="">Choisir une option</option>
                        @foreach (DocumentAccessEnum::options() as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </flux:select>
                </div>


                {{-- Description --}}
                <flux:textarea wire:model="form.notes" label="Notes"
                    placeholder="Ex : Contrat du salarié pour l'année 2025" rows="3" />

                {{-- Fichier --}}
                <flux:input type="file" wire:model="form.file" label="Fichier"
                    accept=".pdf,.doc,.docx,.jpg,.png" />




                <div class="flex justify-end gap-2  pt-4">
                    <flux:button type="submit" variant="primary">Enregistrer</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
    <flux:modal name="delete-document-modal">
        <div class="space-y-6 pt-5">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Supprimer ce document</flux:heading>
            </div>
            @if ($documentToDelete)
                <p>
                    Voulez vous vraiment supprimer le document {{ $documentToDelete->name }} ?
                </p>
                <p>Cette action est irréversiblee.</p>
            @endif

            <div class="flex justify-end gap-2  pt-4">
                <flux:modal.close>
                    <flux:button>Annuler</flux:button>

                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">Oui, j'en suis sûr</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
