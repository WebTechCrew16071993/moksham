<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserActivityResource\Pages;
use App\Models\UserActivity;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserActivityResource extends Resource
{
    protected static ?string $model = UserActivity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?string $navigationLabel = 'User Activity';
    protected static ?int $navigationSort = 99;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('actor.name')->label('User')->searchable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Document')
                    ->formatStateUsing(function ($state, $record) {
                        $action = $record->action ?? '';
                        $label = 'Permissions';
                        if (!str_starts_with((string) $action, 'permissions')) {
                            $base = $state ? basename(str_replace('\\','/',$state)) : null;
                            $label = match ($base) {
                                'BlCorrection' => 'BL',
                                'Form6Document' => 'Form 6',
                                'Form9Document' => 'Form 9',
                                'PackingList' => 'Packing List',
                                'Invoice' => 'Invoice',
                                'Indent' => 'Indent',
                                'Shipment' => 'Shipment',
                                'User' => 'Permissions',
                                default => ($base ?: 'Permissions'),
                            };
                        }
                        $id = $record->subject_id ?? null;
                        return $id ? ($label . ' #' . $id) : $label;
                    })
                    ->url(function ($record) {
                        return self::resolveSubjectUrl($record);
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->formatStateUsing(function ($state, $record) {
                        if ($state) {
                            return $state;
                        }
                        $a = (string) ($record->action ?? '');
                        if ($a === '') return '—';
                        if (str_contains($a, 'created')) return 'Created';
                        if (str_contains($a, 'updated')) return 'Updated';
                        if (str_contains($a, 'deleted')) return 'Deleted';
                        if (str_starts_with($a, 'permissions')) return 'Permissions updated';
                        return $a;
                    })
                    ->wrap(),
                // Tables\Columns\TextColumn::make('changes')
                //     ->label('Changes')
                //     ->formatStateUsing(function ($state) {
                //         if (!is_array($state)) {
                //             return '—';
                //         }
                //         $before = $state['before'] ?? null;
                //         $after = $state['after'] ?? null;
                //         if (!is_array($before) || !is_array($after)) {
                //             return '—';
                //         }
                //         $fmt = function ($v) {
                //             if (is_bool($v)) return $v ? 'true' : 'false';
                //             if ($v === null) return 'null';
                //             if (is_scalar($v)) return (string) $v;
                //             return '[…]';
                //         };
                //         $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
                //         $parts = [];
                //         $nested = 0;
                //         foreach ($keys as $k) {
                //             $b = $before[$k] ?? null;
                //             $a = $after[$k] ?? null;
                //             if ($b === $a) continue;
                //             if (is_array($b) || is_array($a)) { $nested++; continue; }
                //             $parts[] = $k . ': ' . $fmt($b) . ' → ' . $fmt($a);
                //             if (count($parts) >= 4) break;
                //         }
                //         $out = implode(', ', $parts);
                //         if ($nested > 0) {
                //             $out .= ($out ? '; ' : '') . $nested . ' nested change(s)';
                //         }
                //         return $out ?: '—';
                //     })
                //     ->wrap(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('When')->sortable(),
            ])
            ->filters([])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                // Show only activities performed by users (exclude admin actors)
                $query->whereHas('actor', function ($q) {
                    $q->where('role', 'user');
                });
            })
            // ->actions([
            //     Tables\Actions\Action::make('view')
            //         ->label('View')
            //         ->icon('heroicon-o-eye')
            //         ->modalHeading('Activity Details')
            //         ->modalSubmitAction(false)
            //         ->modalCancelActionLabel('Close')
            //         ->modalWidth('lg')
            //         ->modalContent(function (UserActivity $record) {
            //             $doc = $record->subject_type ? basename(str_replace('\\\\','/',$record->subject_type)) : 'User';
            //             $id  = $record->subject_id;
            //             $title = ($doc === 'User' && str_starts_with((string) $record->action, 'permissions')) ? 'Permissions' : $doc;
            //             $changes = $record->changes ?? [];
            //             $pretty = json_encode($changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            //             return new \Illuminate\Support\HtmlString(
            //                 '<div class="space-y-2">'
            //                 .'<div><strong>Action:</strong> '.e($record->action).'</div>'
            //                 .'<div><strong>Document:</strong> '.e($title).($id ? ' #'.e((string)$id) : '').'</div>'
            //                 .'<div><strong>When:</strong> '.e((string)$record->created_at).'</div>'
            //                 .'<div class="mt-2"><strong>Changes:</strong><pre class="mt-1 bg-gray-50 p-3 rounded text-xs overflow-auto">'.e($pretty).'</pre></div>'
            //                 .'</div>'
            //             );
            //         }),
            // ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserActivities::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    protected static function resolveSubjectUrl(UserActivity $record): ?string
    {
        $action = $record->action ?? '';
        if (str_starts_with((string) $action, 'permissions')) {
            return null;
        }

        $modelClass = $record->subject_type ?? null;
        $id = $record->subject_id ?? null;
        if (!$modelClass || !$id) {
            return null;
        }

        $resource = Filament::getModelResource($modelClass);
        if (!$resource) {
            return null;
        }

        return $resource::getUrl('edit', ['record' => $id]);
    }
}
