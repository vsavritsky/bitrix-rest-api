<?php

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$swagger = \OpenApi\Generator::scan([$_SERVER['DOCUMENT_ROOT'].'/local/src/Controller']);

// header('Content-Type: application/x-yaml');
echo $swagger->toJson();
