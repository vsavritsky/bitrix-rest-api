<?php

namespace App\Response\Basket;

use App\Repository\Catalog\ProductRepository;
use Bitrix\Sale;
use BitrixRestApi\Responser\Response\BaseSuccessResponse;
use BitrixModels\Service\PictureService;
use Bitrix\Sale\Internals\DiscountGroupTable;
use Bitrix\Sale\Internals\DiscountTable;
use Bitrix\Sale\Basket;

class BasketResponse extends BaseSuccessResponse implements \JsonSerializable
{
    public $item = null;

    public Basket $basket;

    protected ProductRepository $productRepository;

    public static $resultFields = [
        'item'
    ];

    public function __construct(ProductRepository $productRepository)
    {
        parent::__construct(null);
        $this->productRepository = $productRepository;
    }

    /**
     * @return Basket
     */
    public function getBasket(): Basket
    {
        return $this->basket;
    }

    /**
     * @param Basket $basket
     */
    public function setBasket(Basket $basket): void
    {
        $this->basket = $basket;
    }

    /** Sale\Basket $basket */
    public function populate($basket)
    {
        $this->item['costWithDiscount'] = 0;
        $this->item['cost'] = 0;
        $this->item['items'] = [];

        if (count($this->basket->getBasketItems()) == 0) {
            $this->clearCoupons();
        }

        $totalFinalPrice = 0;
        $totalWrittenOffBonuses = 0;

        /** @var Sale\BasketItem $basketItem */
        foreach ($this->basket->getBasketItems() as $basketItem) {
            $product = $this->productRepository->findById($basketItem->getProductId());

            if (!$product) {
                continue;
            }

            $discountProduct = 0;
            foreach ($basketItem->getPropertyCollection()->getPropertyValues() as $propertyItem) {
                if ($propertyItem['CODE'] == 'pay_bonus') {
                    $discountProduct = $propertyItem['VALUE'];
                    $totalWrittenOffBonuses += $discountProduct;
                }
            }

            $finalPriceItem = $basketItem->getFinalPrice() - $discountProduct;

            //$discount = round(floatval(100 - (($basketItem->getPrice() * $basketItem->getQuantity() - $discountProduct) / ($basketItem->getBasePrice() * $basketItem->getQuantity())) * 100), 4);
            //if (is_nan($discount)) {
            //    $discount = 0;
            //}

            $item = [
                'id' => $basketItem->getId(),
                'product' => [
                    'id' => (int)$product->getId(),
                    'name' => $product->getName(),
                    'code' => $product->getCode(),
                    'previewPicture' => PictureService::getPicture($product->getPreviewPicture()),
                    'price' => ceil($finalPriceItem),
                    'oldPrice' => ceil($basketItem->getBasePrice() * $basketItem->getQuantity()),
                    //'discount' => (float)$discount,
                    'discountPrice' => round(($basketItem->getBasePrice() * $basketItem->getQuantity()) - ($basketItem->getPrice() * $basketItem->getQuantity()) + $discountProduct, 2),
                    'quantity' => $basketItem->getQuantity()
                ]
            ];

            $totalFinalPrice += $basketItem->getBasePrice() * $basketItem->getQuantity();
            $totalFinalPriceWithDiscount += $basketItem->getPrice() * $basketItem->getQuantity();
            $totalDiscount += $basketItem->getDiscountPrice() + $discountProduct;

            $this->item['items'][] = $item;
        }

        $this->item['costWithDiscount'] = ceil($totalFinalPriceWithDiscount - $totalWrittenOffBonuses);
        $this->item['cost'] = ceil($totalFinalPrice);
    }

    protected function clearCoupons()
    {
        \Bitrix\Main\Loader::includeModule('sale');
        \Bitrix\Sale\DiscountCouponsManager::init();
        \Bitrix\Sale\DiscountCouponsManager::clear(true);
        \Bitrix\Sale\DiscountCouponsManager::clearApply(true);
    }
}
