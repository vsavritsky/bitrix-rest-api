<?php

namespace BitrixRestApi\Storage;

use Bitrix\Main\UserTable;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\Application;
use OAuth2\Storage\Pdo;
use BitrixModels\Service\PhoneService;
use CUser;

class Bitrix extends Pdo
{
    protected \PDO $connection;
    protected CUser $user;
    protected PhoneService $phoneService;

    public function __construct(
        \PDO $connection,
        array $config = []
    ) {
        $config['user_table'] = 'b_user';

        $this->connection = $connection;
        $this->user = $GLOBALS['USER'];
        $this->phoneService = new PhoneService();

        parent::__construct($connection, $config);
    }

    public function getUser($username): array|false
    {
        try {
            $userInfo = $this->findUserByLogin($username)
                ?? $this->findUserByPhone($username)
                ?? $this->findUserByEmail($username);

            return $userInfo ? array_merge(['user_id' => $userInfo['ID']], $userInfo) : false;
        } catch (\Exception $e) {
            // Логирование ошибки
            return false;
        }
    }

    protected function findUserByLogin(string $login): ?array
    {
        return UserTable::getList([
            'filter' => ['=LOGIN' => $login, '!BLOCKED' => 'Y'],
            'select' => ['ID', 'LOGIN', 'CONFIRM_CODE']
        ])->fetch() ?: null;
    }

    protected function findUserByPhone(string $phone): ?array
    {
        // Сначала проверяем по PERSONAL_PHONE
        $userInfo = UserTable::getList([
            'filter' => ['=PERSONAL_PHONE' => $phone, '!BLOCKED' => 'Y'],
            'select' => ['ID', 'LOGIN', 'CONFIRM_CODE']
        ])->fetch();

        if (!$userInfo) {
            // Проверяем по очищенному номеру в LOGIN
            $formattedPhone = $this->phoneService->format($phone);
            $findPhone = '%' . str_replace('+7', '', $formattedPhone) . '%';

            $sql = "SELECT ID FROM b_user 
                    WHERE 
                    REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOGIN, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') LIKE :phone
                    AND BLOCKED = 'N'
                    LIMIT 1";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':phone', $findPhone);
            $result = $stmt->execute();

            if ($userId = $result->fetch()['ID'] ?? null) {
                $userInfo = UserTable::getList([
                    'filter' => ['=ID' => $userId],
                    'select' => ['ID', 'LOGIN', 'CONFIRM_CODE']
                ])->fetch();
            }
        }

        return $userInfo ?: null;
    }

    protected function findUserByEmail(string $email): ?array
    {
        return UserTable::getList([
            'filter' => ['=EMAIL' => $email, '!BLOCKED' => 'Y'],
            'select' => ['ID', 'LOGIN', 'CONFIRM_CODE']
        ])->fetch() ?: null;
    }

    public function setUser($username, $password, $firstName = null, $lastName = null): void
    {
        // Реализация может быть добавлена при необходимости
    }

    protected function checkPassword($user, $password): bool
    {
        try {
            // Проверка через стандартную аутентификацию Bitrix
            if ($this->user->Login($user['LOGIN'], $password) === true) {
                return true;
            }

            // Проверка кода подтверждения
            return (string)($user['CONFIRM_CODE'] ?? '') === (string)$password;
        } catch (\Exception $e) {
            // Логирование ошибки
            return false;
        }
    }
}