<?php

namespace Module\News\V1\Controller;

use App\Controller\BaseController;
use App\Validator\ValidationException;
use Exception;
use Module\News\V1\Entity\News;
use Module\News\V1\Service\NewsService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

//POST - Create a REST API resource
#[OA\RequestBody(
    description: 'Model news',
    content: new OA\JsonContent(
        ref: new Model(type: News::class, groups: ["post"])
    )
)]
#[OA\Response(
    response: 200,
    description: 'Add news',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'success', type: 'boolean', default: true),
            new OA\Property(property: 'code', type: 'integer'),
            new OA\Property(
                property: 'item',
                ref: new Model(type: News::class, groups: ["post"])
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
#[Route(path: '/v1/news', name: 'newsPost', methods: Request::METHOD_POST)]
class PostController extends BaseController
{
    /**
     * @throws Exception
     */
    final public function __invoke(Request $request, NewsService $service): Response
    {
        try {
            //Check access
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

            $item = $service->toEntity($request->getContent());
            if (!$item instanceof News) {
                throw new Exception('Repository not implements User');
            }
            $service->getEntityManager()->persist($item);
            $service->getEntityManager()->flush();

            return $this->json([
                'success' => true,
                'code' => Response::HTTP_OK,
                'item' => $item->toArray('post'),
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