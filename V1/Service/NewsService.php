<?php

namespace Module\News\V1\Service;

use App\Service\BaseService;
use Module\News\V1\Entity\News;

class NewsService extends BaseService
{
    protected const DATABASE = 'news';
    protected const ENTITY = 'Module\News\V1\Entity\News';
}