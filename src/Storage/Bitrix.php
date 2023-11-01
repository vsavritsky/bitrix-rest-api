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

    public function getUser($username): array | false
    {
        $userInfo = UserTable::getList([
            'filter' => ['LOGIN' => $username, '!BLOCKED' => 'Y'],
            'select' => ['ID', 'LOGIN', 'CONFIRM_CODE']
        ])->fetch();

        if (!$userInfo) {
            $userInfo = UserTable::getList([
                'filter' => ['PERSONAL_PHONE' => $username, '!BLOCKED' => 'Y'],
                'select' => ['ID', 'LOGIN', 'CONFIRM_CODE']
            ])->fetch();
        }

        if (!$userInfo) {
            $userInfo = UserTable::getList([
                'filter' => ['EMAIL' => $username, '!BLOCKED' => 'Y'],
                'select' => ['ID', 'LOGIN', 'CONFIRM_CODE']
            ])->fetch();
        }

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
        $result = $USER->Login($user['LOGIN'], $password);
        if ($result === true) {
            return true;
        }

        if ((string)$user['CONFIRM_CODE'] === (string)$password) {
            return true;
        }

        return false;
    }
}
