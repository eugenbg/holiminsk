<?php
require_once './vendor/autoload.php';
ini_set('default_charset', 'utf-8');

$nosql = new NoSQLite\NoSQLite('mydb.sqlite');
$store = $nosql->getStore('ranges');

if(count($_POST)) {
    $password = $_POST['password'];
    if($password != 'moidrugdurak') die('wrong password');

    $store->deleteAll();
    foreach ($_POST['ranges'] as $key => $range) {
        $key = (string)$key;
        $store->set($key, $range);
    }
}

$ranges = [
    0 => ['from' => 0, 'to' => 49, 'price' => 2.7],
    1 => ['from' => 50, 'to' => 99, 'price' => 2.5],
    2 => ['from' => 100, 'to' => 299, 'price' => 2.3],
    3 => ['from' => 300, 'to' => 499, 'price' => 2.15],
    4 => ['from' => 500, 'to' => 999, 'price' => 1.99]
];

$storedRanges = $store->getAll();
if(count($storedRanges)) {
    foreach ($storedRanges as $key => $price) {
        $ranges[$key]['price'] = $price;
    }
}

?>

<form method="post">
    <?php foreach ($ranges  as $range): ?>
        <p>
            От: <?=$range['from'];?>,
            До: <?=$range['to'];?><br/>
            <input name="ranges[]" type="text" value="<?=$range['price']?>">
        </p>
    <?php endforeach; ?>
    Пароль
    <input type="text" name="password">
    <br/>
    <button>Сохранить</button>
</form>
