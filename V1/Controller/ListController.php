<?php

namespace Module\News\V1\Controller;

use App\Controller\BaseController;
use App\Doc\ContentPagination;
use App\Repository\PaginatorInterface;
use Exception;
use Module\News\V1\Entity\News;
use Module\News\V1\Service\NewsService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Response(
    response: 200,
    description: 'Returns list the model news',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'success', type: 'boolean'),
            new OA\Property(property: 'code', type: 'integer'),
            new OA\Property(
                property: 'paginator',
                ref: new Model(type: ContentPagination::class)
            ),
            new OA\Property(
                property: 'items',
                type: 'array',
                items: new OA\Items(ref: new Model(type: News::class, groups: ["list"]))
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
            new OA\Property(property: 'message', type: 'string'),
        ]
    )
)]
#[OA\Parameter(
    name: 'limit',
    in: 'query',
    schema: new OA\Schema(type: 'integer')
)]
#[OA\Parameter(
    name: 'field',
    in: 'query',
    schema: new OA\Schema(type: 'string'),
    example: 'id,title'
)]
#[OA\Parameter(
    name: 'filter',
    in: 'query',
    schema: new OA\Schema(type: 'string'),
    example: '{"id": {"<": "36"}, "name": {"=": "algo"}}'
)]
#[OA\Parameter(
    name: 'sort',
    in: 'query',
    schema: new OA\Schema(type: 'string'),
    example: '+id,-name'
)]
#[OA\Parameter(
    name: 'order',
    description: 'The field used to order rewards',
    in: 'query',
    schema: new OA\Schema(type: 'string')
)]
#[Security(name: null)]
#[OA\Tag(name: 'News')]
#[Route(path: '/v1/news', name: 'newsList', methods: Request::METHOD_GET)]
class ListController extends BaseController
{
    final public function __invoke(Request $request, NewsService $service): Response
    {
        try {
            //Check access
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

            $repository = $service->getRepository();
            if (!$repository instanceof PaginatorInterface) {
                throw new Exception('Repository not implements PaginatorInterface');
            }

            $paginator = $repository->getPaginator($this->getRestParams($request));

            return $this->json([
                'success' => true,
                'code' => Response::HTTP_OK,
                'paginator' => [
                    'first' => 1,
                    'next' => $paginator->getNextPage(),
                    'previous' => $paginator->getPreviousPage(),
                    'last' => $paginator->getLastPage(),
                    'current' => $paginator->getCurrentPage(),
                ],
                'items' => $paginator->getResults(),
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