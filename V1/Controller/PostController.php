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
#[Security(name: 'Bearer')]
#[OA\Tag(name: 'news')]
#[Route(path: '/v1/news', name: 'newsPost', methods: Request::METHOD_POST)]
class PostController extends BaseController
{
    /**
     * @throws Exception
     */
    final public function __invoke(Request $request, NewsService $service): Response
    {
        try {
            $item = $service->toEntity($request->getContent());
            $service->getEntityManager()->persist($item);
            $service->getEntityManager()->flush();

            return $this->json([
                'success' => true,
                'code' => Response::HTTP_OK,
                'item' => $item,
            ]);
        } catch (ValidationException $e) {
            return $this->json([
                'success' => false,
                'code' => Response::HTTP_EXPECTATION_FAILED,
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