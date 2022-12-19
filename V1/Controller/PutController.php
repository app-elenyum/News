<?php

namespace Module\News\V1\Controller;

use App\Controller\BaseController;
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
    description: 'Model news',
    content: new OA\JsonContent(
        ref: new Model(type: News::class, groups: ["put"])
    )
)]
#[Security(name: 'Bearer')]
#[OA\Tag(name: 'news')]
#[Route(path: '/v1/news/{id<\d+>}', name: 'newsPut', methods: Request::METHOD_PUT)]
class PutController extends BaseController
{
    final public function __invoke(int $id, Request $request, NewsService $service): Response
    {
        try {
            $repository = $service->getRepository();
            if (!$repository instanceof GetItemForPutInterface) {
                throw new Exception('Repository not implements GetItemForPutInterface');
            }
            $item = $repository->findOneBy(['id' => $id]);
            if ($item === null) {
                return $this->json([
                    'success' => false,
                    'code' => Response::HTTP_NOT_FOUND,
                ]);
            }

            $service->updateEntity($item, $request->getContent());
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