<?php

namespace App\Models;

use App\Enums\MotifEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('employee_id',
    'reason',
    'type',
    'start_date',
    'end_date',
    'data', )]
class EndContract extends Model
{
    protected function casts(): array
    {
        return [
            'type' => MotifEnum::class,
            'reason' => 'string',
            'start_date' => 'date',
            'end_date' => 'date',
            'data' => 'array',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class)->latest();
    }
    //
}
