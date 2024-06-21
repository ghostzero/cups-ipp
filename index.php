<?php

require __DIR__.'/vendor/autoload.php';

use Smalot\Cups\Builder\Builder;
use Smalot\Cups\Manager\PrinterManager;
use Smalot\Cups\Transport\Client;
use Smalot\Cups\Transport\ResponseParser;

$client = new Client();
$builder = new Builder();
$responseParser = new ResponseParser();

$printerManager = new PrinterManager($builder, $client, $responseParser);

$printers = $printerManager->getList();

foreach ($printers as $printer) {
    print_r($printer->getName());
}