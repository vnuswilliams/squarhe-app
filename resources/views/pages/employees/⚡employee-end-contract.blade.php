<?php

use App\Models\Employee;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Suspension rupture de contrat')] class extends Component
{
    #[Url]
    public string|int $employee;

    #[Computed()]
    public function employee()
    {
        return Employee::whereId($this->employee)
            ->firstOrFail();
    }
};
?>
<div>


</div>