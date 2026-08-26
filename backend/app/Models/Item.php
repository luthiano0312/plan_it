<?php

namespace App\Models;

use App\Enums\ItemStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'parent_id',
        'due_date',
        'effort',
        'manual_priority',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ItemStatus::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'effort' => 'integer',
            'manual_priority' => 'float',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Item::class, 'parent_id');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function isLeaf(): bool
    {
        return $this->children()->doesntExist();
    }

    /**
     * IDs de todos os descendentes, por níveis (BFS) para evitar N+1.
     */
    public function descendantIds(): Collection
    {
        $ids = collect();
        $frontier = collect([$this->id]);

        while ($frontier->isNotEmpty()) {
            $frontier = static::query()->whereIn('parent_id', $frontier)->pluck('id');
            $ids = $ids->merge($frontier);
        }

        return new Collection($ids->all());
    }

    /**
     * Acionáveis: não concluídos e sem nenhum filho pendente.
     */
    public function scopeActionable(Builder $query): void
    {
        $query->whereNot('status', ItemStatus::Concluido)
            ->whereDoesntHave('children', fn (Builder $q) => $q->whereNot('status', ItemStatus::Concluido));
    }
}
