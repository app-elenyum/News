<?php

namespace Module\News\V1\Repository;

use App\Repository\BaseRepository;
use Doctrine\Persistence\ManagerRegistry;
use Module\News\V1\Entity\News;

/**
 * Class NewsRepository
 * @package Module\News\Repository
 *
 * @method News|null find($id, $lockMode = null, $lockVersion = null)
 * @method News|null findOneBy(array $criteria, array $orderBy = null)
 * @method News[]    findAll()
 * @method News[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    public function getItem(int $id): ?object
    {
        return $this->findOneBy(['id' => $id, 'status' => News::STATUS_PUBLISH]);
    }
}