<?php

namespace BitrixRestApi\Storage;

use Bitrix\Main\UserTable;
use OAuth2\Storage\Pdo;

class Bitrix extends Pdo
{
    public function __construct($connection, array $config = [])
    {
        $config['user_table'] = 'b_user';

        parent::__construct($connection, $config);
    }

    public function getUser($username)
    {
        $userInfo = UserTable::getList([
            'filter' => ['LOGIN' => $username],
            'select' => ['ID', 'LOGIN']
        ])->fetch();

        if (!$userInfo) {
            return false;
        }

        return array_merge([
            'user_id' => $userInfo['ID']
        ], $userInfo);
    }

    public function setUser($username, $password, $firstName = null, $lastName = null)
    {
        
    }

    protected function checkPassword($user, $password)
    {
        global $USER;
        return $USER->Login($user['LOGIN'], $password);
    }
}
