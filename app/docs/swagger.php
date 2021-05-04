<?php

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$swagger = OpenApi\scan([$_SERVER['DOCUMENT_ROOT'].'/local/php_interface/class/Api/App/']);

header('Content-Type: application/x-yaml');
echo $swagger->toJson();
