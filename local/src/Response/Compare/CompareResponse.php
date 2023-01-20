<?php

namespace App\Response\Compare;

use App\Entity\User\Compare;
use App\Entity\User\Favorite;
use App\Repository\Catalog\ProductRepository;
use BitrixModels\Entity\ProductModel;
use BitrixRestApi\Responser\Response\BaseSuccessResponse;
use BitrixModels\Service\PictureService;

class CompareResponse extends BaseSuccessResponse implements \JsonSerializable
{
    protected ProductRepository $productRepository;
    protected Compare|null $compare = null;

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
     * @param Compare|null $compare
     */
    public function setCompare(?Compare $compare): void
    {
        $this->compare = $compare;
    }

    /** Sale\Basket $basket */
    public function populate($basket)
    {
        foreach ($this->compare->getUfProducts() as $productId) {
            /** @var ProductModel $product */
            $product = $this->productRepository->findById($productId);

            if (!$product) {
                continue;
            }

            $item = [
                'id' => $this->compare->getId(),
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
