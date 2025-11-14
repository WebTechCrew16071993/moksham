<?php

namespace App\Observers;

use App\Models\Indent;
use App\Services\ActivityLogger;

class IndentObserver
{
    public function created(Indent $indent): void
    {
        ActivityLogger::logModel('indent.created', $indent, $indent->user_id ?? null, 'Indent #'.$indent->getKey(), null, 'Indent created');
    }

    public function updated(Indent $indent): void
    {
        ActivityLogger::logModel('indent.updated', $indent, $indent->user_id ?? null, 'Indent #'.$indent->getKey(), null, 'Indent updated');
    }

    public function deleted(Indent $indent): void
    {
        ActivityLogger::log('indent.deleted', [
            'user_id' => $indent->user_id ?? null,
            'subject_type' => Indent::class,
            'subject_id' => $indent->getKey(),
            'subject_label' => 'Indent #'.$indent->getKey(),
            'description' => 'Indent deleted',
        ]);
    }
}
