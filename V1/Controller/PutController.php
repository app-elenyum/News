<?php

namespace Module\News\V1\Controller;

use App\Controller\BaseController;
use App\Exception\UndefinedEntity;
use App\Repository\GetItemForPutInterface;
use App\Validator\ValidationException;
use Exception;
use Module\News\V1\Entity\News;
use Module\News\V1\Service\NewsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;

//Update a REST API resource
#[OA\RequestBody(
    description: 'Update model news',
    content: new OA\JsonContent(
        ref: new Model(type: News::class, groups: ["put"])
    )
)]
#[OA\Response(
    response: 200,
    description: 'Update news',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'success', type: 'boolean', default: false),
            new OA\Property(property: 'code', type: 'integer'),
            new OA\Property(
                property: 'item',
                ref: new Model(type: News::class, groups: ["put"])
            ),
        ]
    )
)]
#[OA\Response(
    response: 400,
    description: 'Returns error if invalid data',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'success', type: 'boolean', default: false),
            new OA\Property(property: 'code', type: 'integer'),
            new OA\Property(property: 'message', type: 'integer'),
            new OA\Property(property: 'errors', type: 'array', items: new OA\Items(type: 'string'))
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
            new OA\Property(property: 'message', type: 'integer'),
        ]
    )
)]
#[Security(name: null)]
#[OA\Tag(name: 'News')]
#[Route(path: '/v1/news/{id<\d+>}', name: 'newsPut', methods: Request::METHOD_PUT)]
class PutController extends BaseController
{
    final public function __invoke(int $id, Request $request, NewsService $service): Response
    {
        try {
            //Check access
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

            $repository = $service->getRepository();
            if (!$repository instanceof GetItemForPutInterface) {
                throw new Exception('Repository not implements GetItemForPutInterface');
            }
            $item = $repository->getItemForPut($id);
            if (!$item instanceof News) {
                throw new UndefinedEntity(News::class, $id);
            }

            $service->updateEntity($item, $request->getContent());
            $service->getEntityManager()->flush();

            return $this->json([
                'success' => true,
                'code' => Response::HTTP_OK,
                'item' => $item->toArray('put'),
            ]);
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => $e->getMessage(),
                'errors' => json_decode($e->getMessage()),
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