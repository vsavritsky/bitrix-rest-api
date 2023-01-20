<?php

namespace App\Controller;

use App\Service\Security\RestoreService;
use BitrixRestApi\Controller\AbstractController;
use BitrixRestApi\Response\ResponseFacade;
use App\Model\UserAddModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use App\Service\Security\RegistrationService;
use Slim\Psr7\Request;

class SecurityController extends AbstractController
{

    public function __construct(
        ContainerInterface $container,
        ResponseFacade     $responseFacade
    )
    {
        parent::__construct($container, $responseFacade);
    }

    /**
     * @OA\Post(
     *     tags={"Регистрация"},
     *     path="/api/security/registration/byEmail",
     *     summary="Регистрация по мылу",
     *      @OA\RequestBody(
     *         required="true",
     *         @OA\JsonContent(
     *          type="object",
     *              @OA\Property(property="name", type="string", example="ФИО"),
     *              @OA\Property(property="workCompany", type="string", example="Компания"),
     *              @OA\Property(property="workPosition", type="string", example="Должность"),
     *              @OA\Property(property="password", type="string", example="123123"),
     *              @OA\Property(property="email", type="string", example="vladimir.savritsky@gmail.com"),
     *              @OA\Property(property="phone", type="string", example="+79266881334"),
     *         )
     *      ),
     * )
     */
    public function registrationByEmail(Request $request): ResponseInterface
    {
        $userAddModel = new UserAddModel($request->getParsedBody());

        /** @var RegistrationService $registrationService */
        $registrationService = $this->container->get(RegistrationService::class);

        $response = $registrationService->registerByEmail($userAddModel);

        return $this->response->setContent($response)->getResponse();
    }

    /**
     * @OA\Post(
     *     tags={"Восстановление доступа"},
     *     path="/api/app/security/restore/byEmail",
     *     summary="Восстановление доступа по мылу",
     *     @OA\Parameter(
     *       in="body",
     *       @OA\Schema(
     *         @OA\Property(property="email", type="string", example="test@te2st.com"),
     *       )
     *     )
     * )
     */
    public function restoreByEmail(Request $request): ResponseInterface
    {
        $data = $request->getParsedBody();

        /** @var RestoreService $restoreService */
        $restoreService = $this->container->get(RestoreService::class);

        $response = $restoreService->restoreByLogin($data['email']);

        return $this->response->setContent($response)->getResponse();
    }

    /**
     * @OA\Post(
     *     tags={"Восстановление доступа"},
     *     path="/api/app/security/restore/changePassword",
     *     summary="Смена пароля",
     *     @OA\Parameter(
     *       in="body",
     *       @OA\Schema(
     *         @OA\Property(property="code", type="string", example="code"),
     *         @OA\Property(property="email", type="string", example="test@te2st.com"),
     *         @OA\Property(property="password", type="string", example="123123"),
     *       )
     *     )
     * )
     */
    public function restoreChangePassword(Request $request): ResponseInterface
    {
        $data = $request->getParsedBody();

        /** @var RestoreService $restoreService */
        $restoreService = $this->container->get(RestoreService::class);

        $response = $restoreService->updatePassword($data['USER_CHECKWORD'], $data['USER_LOGIN'], $data['password']);

        return $this->response->setContent($response)->getResponse();
    }
}
