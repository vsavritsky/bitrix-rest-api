<?php

namespace App\Response;

use BitrixRestApi\Responser\Response\BaseSuccessResponse;

class UserResponse extends BaseSuccessResponse
{
    public static $resultFields = [
        'user',
    ];
    protected ?array $user = [];

    public function __construct($object = null)
    {
        parent::__construct($object);
    }

    public function populate($user)
    {
        parent::populate($user);

        if ($user) {
            $data = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'phone' => $user->getPersonalPhone(),
                'email' => $user->getEmail(),
                'workCompany' => $user->getWorkCompany(),
                'workPosition' => $user->getWorkPosition(),
            ];
        }

        $this->setUser($data);
    }

    /**
     * @return array|null
     */
    public function getUser(): ?array
    {
        return $this->user;
    }

    /**
     * @param array|null $user
     */
    public function setUser(?array $user): void
    {
        $this->user = $user;
    }
}
