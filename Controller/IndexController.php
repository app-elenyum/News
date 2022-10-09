<?php

namespace Module\News\Controller;

use App\Controller\BaseCrudController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/news', name: 'news')]
class IndexController extends BaseCrudController
{
    protected const DATABASE = 'news';
    protected const ENTITY = 'Module\News\Entity\News';
}