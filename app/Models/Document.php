<?php

namespace App\Models;

use App\Enums\DocumentAccessEnum;
use App\Enums\DocumentTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;



class Document extends Model
{

    protected $fillable = [
        'uuid',
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
            if (empty($document->uuid)) {
                $document->uuid = (string) Str::uuid();
            }
              if (empty($document->added_by)):
                $document->added_by = auth()->user()->name;
            endif;
        });
        static::updating(function ($document) {
              if ($document->added_by):
                $document->added_by = auth()->user()->name;
            endif;
        });
    }
}
