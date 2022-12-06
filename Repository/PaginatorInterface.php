<?php

namespace Module\News\Repository;

use App\Utils\Paginator;

interface PaginatorInterface
{
    public function getPaginator(int $page, array $data = []): Paginator;
}