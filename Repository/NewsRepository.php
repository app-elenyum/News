<?php

namespace Module\News\Repository;

use App\Repository\GetItemForDeleteInterface;
use App\Repository\GetItemForPutInterface;
use App\Repository\GetItemInterface;
use App\Repository\PaginatorInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Module\News\Entity\News;
use App\Util\Paginator;

/**
 * Class NewsRepository
 * @package Module\News\Repository
 *
 * @method News|null find($id, $lockMode = null, $lockVersion = null)
 * @method News|null findOneBy(array $criteria, array $orderBy = null)
 * @method News[]    findAll()
 * @method News[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsRepository
    extends ServiceEntityRepository
    implements PaginatorInterface, GetItemInterface, GetItemForPutInterface, GetItemForDeleteInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    /**
     * @throws \Exception
     */
    public function getPaginator(int $page, array $data = []): Paginator
    {
        $qb = $this->createQueryBuilder('news')
            ->orderBy('news.publishedAt', 'DESC')
            ->where('news.status=:status')
            ->setParameter('status', News::STATUS_PUBLISH);

        return (new Paginator($qb, Paginator::PAGE_SIZE))->paginate($page);
    }

    public function getItem(int $id): ?object
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function getItemForDelete(int $id): ?object
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function getItemForPut(int $id): ?object
    {
        return $this->findOneBy(['id' => $id]);
    }
}