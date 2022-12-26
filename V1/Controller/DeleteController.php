<?php

namespace Module\News\V1\Controller;

use App\Controller\BaseController;
use App\Exception\UndefinedEntity;
use App\Repository\GetItemForDeleteInterface;
use Exception;
use Module\Img\V1\Entity\Image;
use Module\News\V1\Entity\News;
use Module\News\V1\Service\NewsService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

#[OA\Response(
    response: 200,
    description: 'Delete news by id',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'success', type: 'boolean', default: true),
            new OA\Property(property: 'code', type: 'integer', default: 200),
            new OA\Property(
                property: 'items',
                ref: new Model(type: Image::class, groups: ["del"])
            ),
        ]
    )
)]
#[OA\Response(
    response: 417,
    description: 'Returns error',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'success', type: 'boolean', default: false),
            new OA\Property(property: 'code', type: 'integer'),
            new OA\Property(property: 'message', type: 'string', example: "Entity not found"),
        ]
    )
)]
#[OA\Parameter(
    name: 'id',
    description: 'can int or array',
    in: 'path',
    schema: new OA\Schema(type: 'object'),
    example: [1,2,3]
)]
#[Security(name: null)]
#[OA\Tag(name: 'News')]
#[Route('/v1/news/{id}', name: 'newsDelete', methods: Request::METHOD_DELETE)]
class DeleteController extends BaseController
{
    final public function __invoke(string $id, NewsService $service): Response
    {
        try {
            //Check access
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

            $repository = $service->getRepository();
            if (!$repository instanceof GetItemForDeleteInterface) {
                throw new Exception('Repository not implements GetItemForDeleteInterface');
            }
            $allId = explode(',', $id);

            $items = $repository->getItemsForDelete($allId);
            if (empty($items)) {
                throw new UndefinedEntity(News::class, $id);
            }
            foreach ($items as $item) {
                $service->getEntityManager()->remove($item);
            }
            $deletedItems = [];
            foreach ($items as $item) {
                if ($item instanceof News) {
                    $deletedItems[] = $item->toArray('del');
                    $service->getEntityManager()->remove($item);
                }
            }
            $service->getEntityManager()->flush();

            return $this->json([
                'success' => true,
                'code' => Response::HTTP_OK,
                'items' => $deletedItems,
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'code' => Response::HTTP_EXPECTATION_FAILED,
                'message' => $e->getMessage(),
            ]);
        }
    }

}