<?php

namespace App\Models;

use App\Enums\DocumentAccessEnum;
use App\Enums\DocumentTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;



class Document extends Model
{
use HasUuids;
    protected $fillable = [
        'employee_id',
        'type',
        'name',
        'notes',
        'path',
        'added_by',
        'access'
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    protected $casts = [
        'access' => DocumentAccessEnum::class,
        'type' => DocumentTypeEnum::class,
    ];
    protected static function booted()
    {
        static::creating(function ($document) {
                $document->added_by ??= auth()->user()->name;
        });
        static::updating(function ($document) {
            $document->added_by ??= auth()->user()->name;
        });
    }
}
