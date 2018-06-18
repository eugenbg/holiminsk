<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <title>Заполните форму и скачайте готовый договор</title>
    <style>
		body {
			font: 14px/1.2 sans-serif;
/*			background: url(/i/bg1.jpg) no-repeat 50% 0 / 100% auto, url(/i/bg2.jpg) no-repeat 50% 100% / 100% auto, url(/i/bg3.jpg) no-repeat 50% 0, #ffc102;*/
			background: url(/i/bg1.jpg) no-repeat 50% 0 / 100% auto, #ffc102;
			padding: 10px;
			margin: 0;
		}
		body * {
			box-sizing: border-box;
		}
		.wrap {
			width: 100%;
			max-width: 500px;
			margin: 0 auto;
		}
		header {
			padding: 0 0 20px;
			border-bottom: 1px solid #fff;
			margin: 0 0 20px;
		}
		header a {
			display: block;
			width: 50%;
			margin: 0 auto;
		}
		header a img {
			display: block;
			width: 100%;
		}
		.wrap article {
			font-size: 18px;
			padding: 20px 0;
		}
		.wrap article p {
			margin: 2em 0;
		}
		.wrap article a {
			color: #fff;
			font-weight: bold;
		}
		footer {
			padding: 20px 0 0;
			border-top: 1px solid #fff;
			margin: 20px 0 0;
			overflow: hidden;
		}
		footer a {
			float: left;
			width: 40%;
		}
		footer a img {
			display: block;
			width: 130px;
			max-width: 100%;
		}
		footer div {
			float: right;
			width: 55%;
		}
	</style>
</head>
<body>

<div class="wrap">
	<header><a href="/"><img src="/i/logo.gif" alt=""></a></header>



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
sendEmail($id, $baseUrl);
?>

<article>
	<p>Договор: <a href="<?php echo $baseUrl . $agreementPath; ?>">скачать</a></p>
	<p>Счет-фактура: <a href="<?php echo $baseUrl . $invoicePath; ?>">скачать</a></p>
	<p>Накладная: <a href="<?php echo $baseUrl . $nakladPath; ?>">скачать</a></p>
</article>



	<footer><a href="/"><img src="/i/logo.gif" alt=""></a> <div>2015 Все права защищены. Зарегистрирован 16.02.16г. № 305710</div></footer>
</div>

</body>
</html>