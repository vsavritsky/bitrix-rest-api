<?php

namespace App\Controller;

use App\Repository\Content\NewsRepository;
use App\Response\Content\NewsListResponse;
use App\Response\Content\NewsViewResponse;
use BitrixModels\Model\Filter;
use BitrixModels\Model\Sort;
use BitrixModels\Service\DateTimeService;
use BitrixModels\Service\PictureService;
use BitrixRestApi\Controller\AbstractController;
use BitrixRestApi\Response\ResponseFacade;
use Slim\Exception\HttpNotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class NewsController extends AbstractController
{
    protected NewsRepository $newsRepository;

    public function __construct(
        ContainerInterface $container,
        ResponseFacade     $responseFacade,
        NewsRepository     $newsRepository,
    )
    {
        parent::__construct($container, $responseFacade);

        $this->newsRepository = $newsRepository;
    }

    /**
     * @OA\Get(
     *     tags={"Новости"},
     *     path="/api/app/content/news/list",
     *     summary="Список новостей",
     *     @OA\Parameter(
     *           in="query",
     *           name="page",
     *           description="Номер страницы",
     *           @OA\Schema(
     *               type="integer",
     *               default=1,
     *           )
     *     ),
     *     @OA\Parameter(
     *           in="query",
     *           name="count",
     *           description="Количество элементов на странице",
     *           @OA\Schema(
     *               type="integer",
     *               default=1,
     *           )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
     * )
     */
    public function list(Request $request): ResponseInterface
    {
        $query = $request->getQueryParams();

        $count = $query['count'] ? (int)$query['count'] : 12;
        $page = $query['page'] ? (int)$query['page'] : 1;

        $response = new NewsListResponse();

        $sort = Sort::create()->setSortBy('ID')->setSortDirection('DESC');

        $newsList = $this->newsRepository->findByFilter(null, null, $sort, $count, $page);

        $data = [];
        foreach ($newsList->getList() as $item) {
            $data[] = [
                'id' => $item->getId(),
                'code' => $item->getCode(),
                'name' => $item->getName(),
                'picture' => PictureService::getPicture($item->getPreviewPicture(), PictureService::SIZE_MEDIUM),
                'date' => DateTimeService::format($item->getActiveFrom() ?? $item->getDateCreate()),
                'text' => $item->getPreviewText(),
            ];
        }
        $response->setList($data);
        $response->setPagination($newsList->getPagination());

        return $this->response->setContent($response)->getResponse();
    }

    /**
     * @OA\Get(
     *     tags={"Новости"},
     *     path="/api/app/content/news/{id}/view",
     *     summary="Детальная новость",
     *     @OA\Parameter(
     *           in="request",
     *           name="id",
     *           description="Id лли символьный код",
     *           @OA\Schema(
     *               type="integer",
     *               default=1,
     *           )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
     * )
     */
    public function view(Request $request, Response $response, array $args): ResponseInterface
    {
        $response = new NewsViewResponse();

        $filter = Filter::create()->eq('ACTIVE', 'Y')->addOrFilter(
            Filter::create()->eq('ID', $args['id'])->eq('CODE', $args['id'])
        );
        $sort = Sort::create()->setSortBy('ID')->setSortDirection('DESC');

        $item = $this->newsRepository->findOneByFilter($filter, $sort);

        if (!$item) {
            throw new HttpNotFoundException($request);
        }

        $data = [
            'id' => $item->getId(),
            'code' => $item->getCode(),
            'name' => $item->getName(),
            'picture' => PictureService::getPicture($item->getDetailPicture(), PictureService::SIZE_BIG),
            'date' => DateTimeService::format($item->getActiveFrom() ?? $item->getDateCreate()),
            'text' => $item->getDetailText(),
        ];

        $response->setItem($data);

        return $this->response->setContent($response)->getResponse();
    }
}
