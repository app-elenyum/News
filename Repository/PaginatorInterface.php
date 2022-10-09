<?php

namespace Module\News\Repository;

use App\Util\Paginator;

interface PaginatorInterface
{
    public function getPaginator(int $page, array $data = []): Paginator;
}