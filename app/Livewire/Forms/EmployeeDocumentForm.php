<?php

namespace App\Livewire\Forms;

use App\Enums\DocumentAccessEnum;
use App\Enums\DocumentTypeEnum;
use App\Models\Document;
use App\Models\Employee;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EmployeeDocumentForm extends Form
{
    public bool $isCreating = false;
    public $employee_id, $file,  $document, $type, $name, $notes, $access;
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'type' => ['required', Rule::in(DocumentTypeEnum::values())],
            'name' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:100'],
            'file' => [$this->isCreating ? 'required' : 'nullable', 'file', 'mimes:xlsx,pdf,doc,docx,jpg,png', 'max:3072'],
            'access' => ['required', Rule::in(DocumentAccessEnum::values())],
        ];
    }

    public function setDocument($document)
    {
        $this->document = $document;
        $this->employee_id = $document->employee_id;


        $this->type = $document->type?->value;
        $this->name = $document->name;
        $this->notes = $document->notes;
        $this->access = $document->access?->value;
    }

    public function create()
    {

        Gate::authorize('create', Document::class);
        $validateData = $this->validate();
        $path = $validateData['file']->store(auth()->user()->company_id.'/'.$this->employee_id . '/documents', 'public');
        $employee = Employee::find($this->employee_id);
        $employee->documents()->create([
            'type' => $validateData['type'],
            'name' => $validateData['name'],
            'notes' => $validateData['notes'] ?? null,
            'path' => $path,
            'access' => $validateData['access'],
        ]);
    }

    public function update()
    {
        Gate::authorize('update', [Document::class, $this->document]);
        $validateData = $this->validate();
        if (empty($validateData['file'])):
            $this->document->update($validateData);
        else:
            Storage::disk('public')->exists($this->document->path) ? Storage::disk('public')->delete($this->document->path) : '';
            $path = $validateData['file']->store($this->employee_id . '/documents', 'public');
            $this->document->update([
                'type' => $validateData['type'],
                'name' => $validateData['name'],
                'notes' => $validateData['notes'] ?? null,
                'path' => $path,
                'access' => $validateData['access'],
            ]);

        endif;
    }
}
