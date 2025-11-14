<?php

namespace App\Observers;

use App\Models\BlCorrection;
use App\Services\ActivityLogger;

class BlCorrectionObserver
{
    public function created(BlCorrection $bl): void
    {
        ActivityLogger::logModel('bl.created', $bl, $bl->user_id ?? null, 'BL #'.$bl->getKey(), null, 'BL created');
    }

    public function updated(BlCorrection $bl): void
    {
        ActivityLogger::logModel('bl.updated', $bl, $bl->user_id ?? null, 'BL #'.$bl->getKey(), null, 'BL updated');
    }

    public function deleted(BlCorrection $bl): void
    {
        ActivityLogger::log('bl.deleted', [
            'user_id' => $bl->user_id ?? null,
            'subject_type' => BlCorrection::class,
            'subject_id' => $bl->getKey(),
            'subject_label' => 'BL #'.$bl->getKey(),
            'description' => 'BL deleted',
        ]);
    }
}
