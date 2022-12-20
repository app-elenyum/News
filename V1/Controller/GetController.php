<?php

namespace Module\News\V1\Controller;

use App\Controller\BaseController;
use App\Repository\GetItemInterface;
use Exception;
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
    description: 'Model news',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'success', type: 'boolean', default: true),
            new OA\Property(property: 'code', type: 'integer', default: 200),
            new OA\Property(
                property: 'item',
                ref: new Model(type: News::class, groups: ["list"])
            ),
        ]
    )
)]
#[OA\Response(
    response: 417,
    description: 'Returns error for all errors',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'success', type: 'boolean', default: false),
            new OA\Property(property: 'code', type: 'integer'),
            new OA\Property(property: 'message', type: 'string'),
        ]
    )
)]
#[Security(name: 'Bearer')]
#[OA\Tag(name: 'news')]
#[Route(path: '/v1/news/{id<\d+>}', name: 'newsGet', methods: Request::METHOD_GET)]
class GetController extends BaseController
{
    final public function __invoke(int $id, NewsService $service): Response
    {
        try {
            //Check access
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

            $repository = $service->getRepository();
            if (!$repository instanceof GetItemInterface) {
                throw new Exception('Repository not implements GetItemInterface');
            }
            $item = $repository->getItem($id);
            if ($item === null) {
                return $this->json([
                    'success' => false,
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => 'Entity not found'
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->json([
                'success' => true,
                'code' => Response::HTTP_OK,
                'item' => $item,
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'code' => Response::HTTP_EXPECTATION_FAILED,
                'message' => $e->getMessage(),
            ], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}