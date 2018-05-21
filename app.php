<?php

require_once './vendor/autoload.php';
require_once './lib.php';
ini_set('default_charset', 'utf-8');

//$baseUrl = 'http://goshaword.server.local/';
$baseUrl = 'http://holiminsk.by/doc/';
$nosql = new NoSQLite\NoSQLite('mydb.sqlite');
$store = $nosql->getStore('orders');
$orders = $store->getAll();
if(!count($orders)) {
    $id = 1000;
} else {
    $lastOrder = json_decode(array_pop($orders), true);
    $id = $lastOrder['id'];
}
$id++;

$docsPath = sprintf('results/order-%s', $id);
if(!is_dir($docsPath)) {
    mkdir($docsPath);
}

saveData($store, $id);
$agreementPath = generateAgreement($id, $docsPath);
$invoicePath = generateInvoice($id, $docsPath);
$nakladPath = generateNaklad($id, $docsPath);
?>

<p>Договор: <a href="<?php echo $baseUrl . $agreementPath; ?>">скачать</a></p>
<p>Счет-фактура: <a href="<?php echo $baseUrl . $invoicePath; ?>">скачать</a></p>
<p>Накладная: <a href="<?php echo $baseUrl . $nakladPath; ?>">скачать</a></p>
