<?php

namespace App\Services;

use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(string $action, array $data = []): void
    {
        try {
            UserActivity::create([
                'user_id' => $data['user_id'] ?? null,
                'actor_id' => $data['actor_id'] ?? Auth::id(),
                'action' => $action,
                'subject_type' => $data['subject_type'] ?? null,
                'subject_id' => $data['subject_id'] ?? null,
                'subject_label' => $data['subject_label'] ?? null,
                'description' => $data['description'] ?? null,
                'changes' => $data['changes'] ?? null,
            ]);
        } catch (\Throwable $e) {
            // Swallow logging errors to avoid breaking business flows
        }
    }

    public static function diff(array $before = null, array $after = null, ?array $only = null): array
    {
        $before = $before ?? [];
        $after = $after ?? [];
        if ($only) {
            $before = array_intersect_key($before, array_flip($only));
            $after = array_intersect_key($after, array_flip($only));
        }
        // Remove timestamps noise
        foreach (['updated_at','created_at','deleted_at'] as $k) {
            unset($before[$k], $after[$k]);
        }
        return [
            'before' => $before,
            'after' => $after,
        ];
    }

    public static function logModel(string $action, Model $model, ?int $userId = null, ?string $label = null, ?array $only = null, ?string $desc = null): void
    {
        $before = method_exists($model, 'getOriginal') ? $model->getOriginal() : [];
        $after = method_exists($model, 'getAttributes') ? $model->getAttributes() : [];
        self::log($action, [
            'user_id' => $userId,
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'subject_label' => $label,
            'description' => $desc,
            'changes' => self::diff($before, $after, $only),
        ]);
    }
}
