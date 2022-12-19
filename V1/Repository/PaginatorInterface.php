<?php

namespace Module\News\V1\Repository;

use App\Utils\Paginator;

interface PaginatorInterface
{
    public function getPaginator(int $page, array $data = []): Paginator;
}