<?php

namespace App\Service\Security;

use BitrixModels\Model\Filter;
use App\Repository\User\UserRepository;
use BitrixRestApi\Responser\Response\BaseErrorResponse;
use BitrixRestApi\Responser\Response\BaseSuccessResponse;
use CUser;

class RestoreService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function restoreByLogin($login): BaseSuccessResponse|BaseErrorResponse
    {
        if (!$login) {
            return (new BaseErrorResponse())->setMessage('Email не заполнено');
        }
        $user = $this->userRepository->findOneByFilter((new Filter())->eq('EMAIL', $login));

        if (!$user) {
            $user = $this->userRepository->findOneByFilter((new Filter())->eq('PERSONAL_PHONE', $login));
        }

        if ($user && $user->getLogin()) {
            $login = $user->getLogin();
        }

        if (!$user) {
            return new BaseErrorResponse();
        }

        $r = CUser::SendUserInfo($user->getId(), SITE_ID, "", false, 'USER_PASS_REQUEST');

        return new BaseSuccessResponse();
    }

    public function updatePassword($code, $email, $password): BaseSuccessResponse|BaseErrorResponse
    {
        global $USER;
        $arResult = $USER->ChangePassword($email, $code, $password, $password);

        if ($arResult["TYPE"] == "OK") {
            return new BaseSuccessResponse();
        }

        $response = (new BaseErrorResponse())->setMessage('Ошибка при изменении пароля');

        if ($arResult["MESSAGE"]) {
            $response->setMessage(strip_tags($arResult["MESSAGE"]));
        }

        return $response;
    }
}
