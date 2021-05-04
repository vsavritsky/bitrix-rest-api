<?php
$allowedOrigins = [
    "http://127.0.0.1:3000",
    "http://localhost:3000",
    "https://dev.air-dev.agency",
];
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT");
if (in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);

}
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers");

include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use BitrixRestApi\ApiEntityFactory;
use BitrixRestApi\Jwt\JwtManager;
use BitrixRestApi\Dispatcher;
use BitrixRestApi\Responser\Json;
use Service\Container\ContainerService;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Manager\App\UserManager;
use Bitrix\Sale;

$GLOBALS["APPLICATION"]->set_cookie("BITRIX_SM_SALE_UID", Sale\Fuser::getId(), false, "/", false, false, "Y", false);
$_COOKIE["BITRIX_SM_SALE_UID"] = Sale\Fuser::getId();

$containerService = new ContainerService();
$jwtManager = $containerService->get(JwtManager::class);
$request = Request::createFromGlobals();
$config = new ParameterBag();

$classes = [
    Api\App\Content\News::class,
    Api\App\Content\Magazine::class,
    Api\App\Content\MagazineSection::class,
    Api\App\Content\Slider::class,
    Api\App\Content\Shipment::class,
    Api\App\Content\Promotion::class,
    Api\App\Content\Faq::class,
    Api\App\Content\BestBanner::class,
    Api\App\Content\Review::class,
    Api\App\Content\Contacts::class,
    Api\App\Content\TransportCompany::class,
    Api\App\Content\History::class,
    Api\App\Content\Certificate::class,
    Api\App\Content\Team::class,
    Api\App\Content\Page::class,
    Api\App\Content\Delivery::class,
    Api\App\Content\TransportCompany::class,
    Api\App\Content\PurchaseReturns::class,

    Api\App\Content\Legal\Advantages::class,
    Api\App\Content\Legal\BecomeClient::class,
    Api\App\Content\Legal\Faq::class,
    Api\App\Content\Legal\OurApproach::class,
    Api\App\Content\Legal\Reviews::class,
    Api\App\Content\Legal\WeAreContacted::class,
    Api\App\Content\Legal\WorkWithUs::class,

    Api\App\Catalog\Product::class,
    Api\App\Catalog\Brand::class,
    Api\App\Catalog\Compare::class,
    Api\App\Catalog\Favorite::class,
    Api\App\Catalog\Filter::class,
    Api\App\Catalog\Category::class,

    Api\App\Setting::class,

    Api\App\Basket\Basket::class,

    Api\App\Security\Auth::class,
    Api\App\Security\Register::class,

    Api\App\Personal\Vacancy::class,
    Api\App\Personal\Profile::class,
    Api\App\Personal\Order::class,

    Api\App\Order\Delivery::class,

    Api\App\Search::class,

    Api\App\Form::class,
    Api\App\Seo\Seo::class,
];

foreach ($classes as $class) {
    $config->set($class, ['format' => Json::class]);
}

$dispatcher = new Dispatcher($config, new ApiEntityFactory());
$dispatcher->setJwtManager($jwtManager);
$dispatcher->setUserManager(new UserManager());
$dispatcher->addResponser(Json::class, new Json());
$dispatcher->setResponser(Json::class);

$result = $dispatcher->execute($request);
