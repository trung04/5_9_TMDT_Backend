<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait PaginatesApiResults
{
    protected function perPage(Request $request, int $default = 15, int $max = 100): int
    {
        $perPage = (int) $request->query('per_page', $default);

        return min(max($perPage, 1), $max);
    }

    /**
     * @param  callable(mixed): mixed  $callback
     */
    protected function transformPaginator(LengthAwarePaginator $paginator, callable $callback): LengthAwarePaginator
    {
        $paginator->setCollection(
            $paginator->getCollection()
                ->map($callback)
                ->values()
        );

        return $paginator;
    }
}
