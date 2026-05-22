<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasActiveState
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('is_active'), true);
    }

    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('is_deleted'), false);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->active()->notDeleted();
    }

    public function markInactive(): bool
    {
        return $this->update(['is_active' => false]);
    }

    public function markDeleted(): bool
    {
        return $this->update([
            'is_active' => false,
            'is_deleted' => true,
        ]);
    }

    public function restoreRecord(): bool
    {
        return $this->update([
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }
}
