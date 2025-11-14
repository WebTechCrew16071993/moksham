<?php

namespace App\Observers;

use App\Models\Form9Document;
use App\Services\ActivityLogger;

class Form9DocumentObserver
{
    public function created(Form9Document $doc): void
    {
        ActivityLogger::logModel('form9.created', $doc, $doc->user_id ?? null, 'Form 9 #' . $doc->getKey(), null, 'Form 9 created');
    }

    public function updated(Form9Document $doc): void
    {
        ActivityLogger::logModel('form9.updated', $doc, $doc->user_id ?? null, 'Form 9 #' . $doc->getKey(), null, 'Form 9 updated');
    }

    public function deleted(Form9Document $doc): void
    {
        ActivityLogger::log('form9.deleted', [
            'user_id' => $doc->user_id ?? null,
            'subject_type' => Form9Document::class,
            'subject_id' => $doc->getKey(),
            'subject_label' => 'Form 9 #' . $doc->getKey(),
            'description' => 'Form 9 deleted',
        ]);
    }
}
