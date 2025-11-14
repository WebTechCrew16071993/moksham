<?php

namespace App\Observers;

use App\Models\Form6Document;
use App\Services\ActivityLogger;

class Form6DocumentObserver
{
    public function created(Form6Document $doc): void
    {
        ActivityLogger::logModel('form6.created', $doc, $doc->user_id ?? null, 'Form 6 #'.$doc->getKey(), null, 'Form 6 created');
    }

    public function updated(Form6Document $doc): void
    {
        ActivityLogger::logModel('form6.updated', $doc, $doc->user_id ?? null, 'Form 6 #'.$doc->getKey(), null, 'Form 6 updated');
    }

    public function deleted(Form6Document $doc): void
    {
        ActivityLogger::log('form6.deleted', [
            'user_id' => $doc->user_id ?? null,
            'subject_type' => Form6Document::class,
            'subject_id' => $doc->getKey(),
            'subject_label' => 'Form 6 #'.$doc->getKey(),
            'description' => 'Form 6 deleted',
        ]);
    }
}
