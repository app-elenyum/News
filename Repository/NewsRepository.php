<?php

namespace Module\News\Repository;

use App\Repository\GetItemForDeleteInterface;
use App\Repository\GetItemForPutInterface;
use App\Repository\GetItemInterface;
use App\Repository\PaginatorInterface;
use App\Utils\RestParams;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Module\News\Entity\News;
use App\Utils\Paginator;

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
     * @param RestParams $params
     * @return Paginator
     * @throws \Exception
     */
    public function getPaginator(
        RestParams $params
    ): Paginator
    {
        $qb = $this->createQueryBuilder('news')
            ->orderBy('news.publishedAt', 'DESC')
            ->where('news.status=:status')
            ->setParameter('status', News::STATUS_PUBLISH);

        return (new Paginator($qb))->paginate($offset);
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