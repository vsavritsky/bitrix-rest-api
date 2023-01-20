<?php

namespace App\Controller;

use App\Repository\Catalog\ProductSectionRepository;
use App\Repository\Catalog\SectionRepository;
use App\Repository\Settings\ContactsRepository;
use App\Repository\Settings\MenuRepository;
use App\Repository\Settings\OfficeRepository;
use App\Repository\Settings\SettingsRepository;
use App\Response\MainResponse;
use App\Response\SettingsResponse;
use BitrixModels\Entity\BaseModel;
use BitrixModels\Model\Filter;
use BitrixModels\Model\Sort;
use BitrixModels\Service\FileService;
use BitrixModels\Service\PictureService;
use BitrixRestApi\Controller\AbstractController;
use BitrixRestApi\Response\ResponseFacade;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;

class SettingsController extends AbstractController
{
    protected ContactsRepository $contactsRepository;
    protected MenuRepository $menuRepository;
    protected OfficeRepository $officeRepository;

    protected SettingsRepository $settingsRepository;

    protected ProductSectionRepository $productSectionRepository;

    public function __construct(
        ContainerInterface $container,
        ResponseFacade     $responseFacade,
        ContactsRepository   $contactsRepository,
        MenuRepository     $menuRepository,
        OfficeRepository  $officeRepository,
        SettingsRepository  $settingsRepository,
    )
    {
        parent::__construct($container, $responseFacade);

        $this->contactsRepository = $contactsRepository;
        $this->menuRepository = $menuRepository;
        $this->officeRepository = $officeRepository;
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * @OA\Get (
     *     tags={"Настройки"},
     *     path="/api/app/settings/view",
     *     summary="Обвязка страницы",
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     )
     * )
     */
    public function view(Request $request): ResponseInterface
    {
        $response = new SettingsResponse();

        /** @var BaseModel $settings */
        $settings = $this->settingsRepository->findOneByFilter();

        if ($settings && $settings->getOffices()) {
            $sort = Sort::create()->setSortBy('SORT')->setSortDirection('ASC');
            $filter = Filter::create()->eq('ID', $settings->getOffices());

            $list = $this->officeRepository->findByFilter(null, $filter, $sort, 3)->getList();
            $data = [];
            foreach ($list as $item) {
                $data[] = [
                    'id' => $item->getId(),
                    'name' => $item->getName(),
                    'address' => htmlspecialchars_decode(strip_tags($item->getPreviewText())),
                    'production' => $item->getProduction() === 'Y'
                ];
            }
            $response->setOffices($data);
            $response->setRules(FileService::getLink($settings->getRules()));
            $response->setPolitic(FileService::getLink($settings->getPolitic()));
            $response->setLogo(FileService::getLink($settings->getLogo()));
        }

        $sort = (new Sort())->setSortBy('SORT')->setSortDirection('ASC');
        $list = $this->menuRepository->findByFilter(null, null, $sort, 1000)->getList();
        $data = [];
        foreach ($list as $item) {
            if ($item->getParent()) {
                continue;
            }
            $data[$item->getId()] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'link' => $item->getCode(),
                'elements' => []
            ];
        }

        foreach ($list as $item) {
            if (!$item->getParent()) {
                continue;
            }
            $data[$item->getParent()]['elements'][] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'link' => $item->getCode(),
                'elements' => []
            ];
        }
        $response->setMenu(array_values($data));

        $contacts = $this->contactsRepository->findOneByFilter();
        $data = [
            'phone' => $contacts->getPhone(),
            'email' => $contacts->getEmail(),
            'socials' => [],
        ];

        foreach ($contacts->getField('SOCIALS')->getValue() as $key => $name) {
            $code = $contacts->getField('SOCIALS')->getDescription()[$key];
            $data['socials'][] = [
                'code' => $name,
                'link' => $code,
            ];
        }

        $response->setContacts($data);

        return $this->response->setContent($response)->getResponse();
    }
}
