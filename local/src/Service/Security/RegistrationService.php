<?php

namespace App\Service\Security;

use App\Repository\User\UserRepository;
use BitrixModels\Model\Filter;
use BitrixRestApi\Responser\Response\BaseErrorResponse;
use BitrixRestApi\Responser\Response\BaseSuccessResponse;
use CUser;
use App\Model\UserAddModel;
use BitrixModels\Service\PhoneService;

class RegistrationService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function registerByEmail(UserAddModel $userAddModel): BaseSuccessResponse|BaseErrorResponse
    {
        if (!$userAddModel->getEmail()) {
            return (new BaseErrorResponse())->setMessage('Email не заполнено');
        }

        if (!$userAddModel->getName()) {
            return (new BaseErrorResponse())->setMessage('Имя не заполнено');
        }

        if (!$userAddModel->getPhone()) {
            return (new BaseErrorResponse())->setMessage('Телефон не заполнен');
        }

        if (!$userAddModel->getPassword()) {
            return (new BaseErrorResponse())->setMessage('Пароль не заполнен');
        }

        if (strlen($userAddModel->getPassword()) < 6) {
            return (new BaseErrorResponse())->setMessage('Пароль должен быть не менее 6 символов длиной');
        }

        if ($this->userRepository->findOneByFilter(Filter::create()->eq('EMAIL', $userAddModel->getEmail()))) {
            return (new BaseErrorResponse())->setMessage('Пользователь с такой электронной почтой уже существует');
        }

        if ($this->userRepository->findOneByFilter(Filter::create()->eq('PERSONAL_PHONE', PhoneService::format($userAddModel->getPhone())))) {
            return (new BaseErrorResponse())->setMessage('Пользователь с таким номером телефона уже существует');
        }

        $randString = uniqid();

        $id = $this->userRepository->add(
            [
                "NAME" => $userAddModel->getName(),
                "LAST_NAME" => $userAddModel->getLastName(),
                "SECOND_NAME" => $userAddModel->getMiddleName(),
                "EMAIL" => $userAddModel->getEmail(),
                "LOGIN" => $userAddModel->getEmail(),
                "WORK_COMPANY" => $userAddModel->getWorkCompany(),
                "WORK_POSITION" => $userAddModel->getWorkPosition(),
                "PERSONAL_GENDER" => $userAddModel->getGender(),
                "PERSONAL_BIRTHDAY" => $userAddModel->getBirthday(),
                "ACTIVE" => "Y",
                "GROUP_ID" => [3, 4],
                "PASSWORD" => $userAddModel->getPassword(),
                "PERSONAL_PHONE" => PhoneService::format($userAddModel->getPhone()),
                "CONFIRM_PASSWORD" => $userAddModel->getPassword(),
                "CONFIRM_CODE" => $randString
            ]
        );

        //$emailNotification = new EmailNotification();
        //$emailNotification->send('NEW_USER_CONFIRM', [
        //    'EMAIL' => $userAddModel->getEmail(),
        //    'USER_ID' => $id,
        //    'CONFIRM_CODE' => $randString
        //]);

        if (intval($id) > 0) {
            $response = new BaseSuccessResponse();
        } else {
            $response = new BaseErrorResponse();
            $response->message = 'Ошибка создания пользователя';
            $response->errorCode = 'error_add_user';
        }

        return $response;
    }
}
