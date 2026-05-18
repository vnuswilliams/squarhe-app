<?php

namespace App\Enums;

use App\Concerns\EnumTrait;


enum StatusEnum: string
{
     use EnumTrait;

    case APPROVED = 'Approved';
    case ONLEAVE = 'on_leave';
    case PENDING = 'pending';
    case TERMINATED = 'terminated';
    case REJECTED = 'Rejected';
    case ACTIVE = 'active';
    case DRAFT = 'draft';
    case CLOSED = 'closed';
    case LOCKED = 'locked';
    case SUSPEND = 'suspend';

    public function label(): string
    {
        return match ($this) {
            self::APPROVED => __('Approuvé'),
            self::ONLEAVE => __('En congé/absent'),
            self::PENDING => __('En attente'),
            self::REJECTED => __('Rejetté'),
            self::TERMINATED => 'Terminé',
            self::ACTIVE => 'active',
            self::SUSPEND => 'suspendu',
            self::DRAFT => 'Brouillon',
            self::CLOSED => 'Clôturé',
            self::LOCKED => 'Verrouillé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::APPROVED => 'green',
            self::ONLEAVE => 'orange',
            self::SUSPEND => 'orange',
            self::PENDING => 'blue',
            self::TERMINATED => 'red',
            self::REJECTED => 'red',
            self::ACTIVE => 'green',

            self::DRAFT => 'orange',
            self::CLOSED => 'green',
            self::LOCKED => 'green',
        };
    }
}