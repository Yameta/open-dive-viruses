<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("BX_CRONTAB", true);
define("NO_AGENT_CHECK", true);

$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/ext_www/open-dive.ru";

// Подключаем пролог Битрикса
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Вызов агента
MyCustomAgent::checkActivity();
?>
