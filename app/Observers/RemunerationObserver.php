<?php

namespace App\Observers;

use App\Models\Remuneration;

class RemunerationObserver
{
    /**
     * Handle the Remuneration "created" event.
     */
    public function created(Remuneration $remuneration): void
    {
        //
    }

    /**
     * Handle the Remuneration "updated" event.
     */
    public function updated(Remuneration $remuneration): void
    {
        //
    }

    /**
     * Handle the Remuneration "deleted" event.
     */
    public function deleted(Remuneration $remuneration): void
    {
        //
    }

    /**
     * Handle the Remuneration "restored" event.
     */
    public function restored(Remuneration $remuneration): void
    {
        //
    }

    /**
     * Handle the Remuneration "force deleted" event.
     */
    public function forceDeleted(Remuneration $remuneration): void
    {
        //
    }
}
