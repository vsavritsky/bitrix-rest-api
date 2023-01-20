<?php

\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule("search");
\Bitrix\Main\Loader::includeModule('highloadblock');
\Bitrix\Main\Loader::includeModule('sale');

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';
