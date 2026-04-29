<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    protected $fillable = [
        'sender_id', 'recipient_id', 'company_id',
        'company_code', 'role', 'expires_at', 'accepted_at'
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return ! is_null($this->accepted_at);
    }

    public function status(): string
    {
        if ($this->isAccepted()) return StatusEnum::APPROVED->value;
        if ($this->isExpired())  return StatusEnum::REJECTED->value;
        return StatusEnum::PENDING->value;
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}