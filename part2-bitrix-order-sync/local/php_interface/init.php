<?php

use Bitrix\Main\EventManager;

require_once __DIR__ . '/lib/OrderExportHandler.php';

EventManager::getInstance()->addEventHandler(
    'sale',
    'OnSaleOrderSaved',
    ['OrderExportHandler', 'onSaleOrderSaved']
);
