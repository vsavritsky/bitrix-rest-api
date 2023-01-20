<?php

namespace App\Response\Favorite;

use App\Entity\User\Favorite;
use App\Repository\Catalog\ProductRepository;
use BitrixModels\Entity\ProductModel;
use BitrixRestApi\Responser\Response\BaseSuccessResponse;
use BitrixModels\Service\PictureService;

class FavoriteResponse extends BaseSuccessResponse implements \JsonSerializable
{
    protected ProductRepository $productRepository;
    protected Favorite|null $favorite = null;

    protected $products = [];

    public static $resultFields = [
        'products'
    ];

    public function __construct(ProductRepository $productRepository)
    {
        parent::__construct(null);
        $this->productRepository = $productRepository;
    }

    /**
     * @param Favorite|null $favorite
     */
    public function setFavorite(?Favorite $favorite): void
    {
        $this->favorite = $favorite;
    }

    /** Sale\Basket $basket */
    public function populate($basket)
    {
        foreach ($this->favorite->getProducts() as $productId) {
            /** @var ProductModel $product */
            $product = $this->productRepository->findById($productId);

            if (!$product) {
                continue;
            }

            $item = [
                'id' => $this->favorite->getId(),
                'product' => [
                    'id' => (int)$product->getId(),
                    'name' => $product->getName(),
                    'article' => $product->getArticle(),
                    'neck' => $product->getNeck(),
                    'volume' => $product->getVolume(),
                    'weight' => $product->getWeight(),
                    'code' => $product->getCode(),
                    'previewPicture' => PictureService::getPicture($product->getPreviewPicture()),
                    'price' => $product->getPrice()
                ]
            ];

            $this->products[] = $item;
        }
    }
}
