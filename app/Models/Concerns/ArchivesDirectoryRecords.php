<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait ArchivesDirectoryRecords
{
    protected static function bootArchivesDirectoryRecords(): void
    {
        static::addGlobalScope('directory_archive', function (Builder $query) {
            $query->whereNull($query->getModel()->qualifyColumn('directory_archived_at'));
        });
    }

    public function scopeWithArchived(Builder $query): Builder
    {
        return $query->withoutGlobalScope('directory_archive');
    }
}
