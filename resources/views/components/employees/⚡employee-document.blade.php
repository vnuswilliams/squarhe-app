<?php

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

new class extends Component {
    use WithFileUploads;
    public $employee;
    public EmployeeDocumentForm $form;

    #[Computed]
    public function documents()
    {
        return $this->employee->documents;
    }
   
    public function save()
    {
        $this->form->employee_id = $this->employee->id;
        $this->form->isCreating = true;

        $this->form->create();
       
        Flux::toast(variant: 'success', text: __("toast.document.createDocumentSuccessfull"));
        $this->form->reset();
    }


    public function edit($documentId)
    {
        $documentToUpdate  = Document::whereId($documentId)
            ->whereEmployeeId($this->employee->id)
            ->firstOrFail();

        $this->form->setDocument($documentToUpdate);
        Flux::modal('edit-document-modal')->show();
    }

    public function update()
    {
        $this->form->update();
        Flux::modal('edit-document-modal')->close();
        Flux::toast(variant: 'success', text: __('toast.document.updateDocumentSuccessfull') );
       

        $this->form->reset();
    }



    public $documentToDelete = null;
    public function confirmBeforeDelete($idDocumentWeWantToDelete)
    {
        $this->documentToDelete = Document::whereId($idDocumentWeWantToDelete)
            ->whereEmployeeId($this->employee->id)
            ->firstOrFail();
        Flux::modal('delete-document-modal')->show();
    }
    public function delete()
    {
        if ($this->documentToDelete):
            Gate::authorize('delete', [Document::class, $this->documentToDelete]);

            Storage::disk('public')->exists($this->documentToDelete->path) ?: Storage::disk('public')->delete($this->documentToDelete->path);


            $this->documentToDelete->delete();

            Flux::toast(variant: 'success', text: __('toast.document.deleteDocumentSuccessfull'));
           
            Flux::modal('delete-document-modal')->close();
            $this->documentToDelete = null;
        endif;
    }

    public function downloadDoc($id)
    {
        $docToDownload = Document::whereId($id)->whereEmployeeId($this->employee->id)
            ->firstOrFail();
        Gate::authorize('view', [Document::class, $docToDownload]);

        $name =  Str::snake($this->employee->shortName . ' ' . $docToDownload->type?->value . ' ' . $docToDownload->name . ' ' . now()->format('_d_m_Y_H_i_s'));
        return Storage::disk('public')->download($docToDownload->path, $name);
    }
};
?>
<div x-data="{activeForm : null}" >
    <div class="flex items-center justify-between">
        <div>
            <flux:heading level="1" class="font-bold">Ajouter un document a votre collaborateur</flux:heading>
            <flux:text class="text-gray-300">Il sera visble en fonction de votre niveau d'accès</flux:text>
        </div>

        <flux:button tooltip="Ajouter un nouveau document" @click="activeForm = 'a' " variant="primary" icon="plus" />
    </div>
    @if($this->documents->isNotEmpty())
    {{-- Delta Card for Documents --}}
        <x-delta-card :cards="[
            [
                'label' => 'Total Documents',
                'current' => $this->documents()->count(),
                'delta' => '',
                'color' => 'blue',
            ]
        ]" />

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
                    {{ __('Cancel') }}

                </flux:button>
                <flux:button variant="primary" type="submit" class="cursor-pointer w-full">
                    Enregistrer le document

                </flux:button>

            </div>
        </form>
    </x-container>


    <x-container>
       
        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-700">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Nom du doc.') }}
                    </th>

                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Type') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Ajouté par') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Accesible par') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Ajouté le') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Actions') }}
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse ($this->documents as $doc)
                <tr wire:key="{{ $doc->id }}">

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        <flux:heading class="flex items-center gap-2">
                            {{ $doc->name }}
                            <flux:tooltip toggleable>
                                <flux:button icon="information-circle" size="sm" variant="ghost" />
                                <flux:tooltip.content>
                                    {{ $doc->notes }}
                                </flux:tooltip.content>
                            </flux:tooltip>

                        </flux:heading>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $doc->type->label()                    }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $doc->added_by }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $doc->access }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $doc->created_at->translatedFormat('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <flux:button variant="primary" icon="arrow-down-tray" siez="sm"
                                wire:click="downloadDoc({{ $doc->id }})" />
                            <flux:button wire:click="edit({{ $doc->id }})" size="sm" variant="ghost" icon="pencil" />


                            <flux:button wire:click="confirmBeforeDelete({{ $doc->id }})"
                                size="sm"
                                variant="ghost" icon="trash" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-8">
                        <x-empty-state message=" 
                    {{ __('Aucun documents trouvés pour ').$this->employee->name }}" />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

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
            @if($documentToDelete)
            <p>
                Voulez vous vraiment supprimer le document {{$documentToDelete->name}} ?
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