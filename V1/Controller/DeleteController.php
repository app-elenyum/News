<?php

namespace Module\News\V1\Controller;

use App\Controller\BaseController;
use App\Repository\GetItemForDeleteInterface;
use Exception;
use Module\News\V1\Service\NewsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

#[OA\Response(
    response: 200,
    description: 'Model news',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'success', type: 'boolean', default: true),
            new OA\Property(property: 'code', type: 'integer', default: 200),
            new OA\Property(
                property: 'id',
                type: 'array',
                items: new OA\Items(default: [1,2,3])
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
#[Security(name: 'Bearer')]
#[OA\Tag(name: 'news', description: 'Delete a REST API resource')]
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
                return $this->json([
                    'success' => false,
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => 'Entity not found'
                ]);
            }
            foreach ($items as $item) {
                $service->getEntityManager()->remove($item);
            }
            $service->getEntityManager()->flush();

            return $this->json([
                'success' => true,
                'code' => Response::HTTP_OK,
                'id' => $allId,
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