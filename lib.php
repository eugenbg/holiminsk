<?php
require_once './vendor/autoload.php';

function saveData($store, $id) {
    $id = (string)$id;
    $data = $_POST;
    $data['id'] = $id;
    $store->set($id, json_encode($data));
}


function generateAgreement($id, $docsPath) {
    \PhpOffice\PhpWord\Settings::setTempDir(__DIR__ . '/tmp');
    $path = sprintf('%s/dogovor.docx', $docsPath);

    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('./template.docx');

    foreach ($_POST as $field => $value) {
        if($field == 'rekviziti') {
            $value = preg_replace('~\R~u', '</w:t><w:br/><w:t>', $value);
        }
        $templateProcessor->setValue(sprintf('${%s}', $field), $value);
    }
    $templateProcessor->setValue('${number}', $id);
    $templateProcessor->setValue('${date}', date('Y-m-d'));


    $templateProcessor->saveAs($path);
    return $path;
}


function generateInvoice($id, $docsPath) {

    $fileName = 'template_invoice.xlsx';

    /** Load $inputFileName to a Spreadsheet object **/
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $spreadsheet = $reader->load($fileName);

    $date = date('d.m.Y');
    $header = sprintf('СЧЕТ-ФАКТУРА   № %s от %s г.', $id, $date);
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('F8', $header);

    $company = $_POST['company'];
    $companyText = sprintf('Покупатель: %s', $company);
    $spreadsheet
        ->setActiveSheetIndex(0)
        ->setCellValue('B10', $companyText);
    
    
    $company = $_POST['company'];
    $companyText = sprintf($company);
    $spreadsheet
        ->setActiveSheetIndex(0)
        ->setCellValue('F29', $companyText);
    
    
     $post = $_POST['post'];
    $post = sprintf( $post);
    $spreadsheet
        ->setActiveSheetIndex(0)
        ->setCellValue('F30', $post);
    
    
    $postName = $_POST['postName'];
    $postName = sprintf('_______________________/%s', $postName);
    $spreadsheet
        ->setActiveSheetIndex(0)
        ->setCellValue('F31', $postName);
    
    

    $rekviziti = $_POST['rekviziti'];
    $rekviziti = explode("\r\n", $rekviziti);
    $i = 11;
    foreach ($rekviziti as $line) {
        $spreadsheet
            ->setActiveSheetIndex(0)
            ->setCellValue('B' . $i, $line);
        $i++;
    }

    $qty = $_POST['qty'];
    $spreadsheet
        ->setActiveSheetIndex(0)
        ->setCellValue('E19', $qty);

    $price = _getPrice($qty);
    $spreadsheet
        ->setActiveSheetIndex(0)
        ->setCellValue('F19', $price);

    $amount = $qty * $price;
    $amountText = sprintf('Всего стоимость с НДС: %s', num2str($amount));

    $spreadsheet
        ->setActiveSheetIndex(0)
        ->setCellValue('B25', $amountText);

    $spreadsheet->getActiveSheet()
        ->getProtection()->setPassword('PhpSpreadsheet');
    $spreadsheet->getActiveSheet()
        ->getProtection()->setSheet(true);
    $spreadsheet->getActiveSheet()
        ->getProtection()->setSort(true);
    $spreadsheet->getActiveSheet()
        ->getProtection()->setInsertRows(true);
    $spreadsheet->getActiveSheet()
        ->getProtection()->setFormatCells(true);
    $spreadsheet->getActiveSheet()->getStyle('B25')
        ->getProtection()
        ->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_PROTECTED);

    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $drawing->setPath('./images/holi-stamp.png');
    $drawing->setHeight(150);
    $drawing->setWorksheet($spreadsheet->getActiveSheet());
    $drawing->setCoordinates('B28');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $path = sprintf("%s/invoice.xlsx", $docsPath);
    $writer->save($path);

    return $path;
}


function generateNaklad($id, $docsPath) {

    $fileName = 'template_naklad.xlsx';

    /** Load $inputFileName to a Spreadsheet object **/
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $spreadsheet = $reader->load($fileName);

    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('T3', $_POST['unp']);

    $date = date('d.m.Y');
    $spreadsheet->setActiveSheetIndex(0)
        ->setCellValue('B5', $date);

    $spreadsheet
        ->setActiveSheetIndex(0)
        ->setCellValue('H9', $_POST['company']);

    $agreementText = sprintf('Договор купли-продажи № %s от %s', $id, $date);
    $spreadsheet
        ->setActiveSheetIndex(0)
        ->setCellValue('H11', $agreementText);

    $qty = $_POST['qty'];
    $spreadsheet
        ->setActiveSheetIndex(0)
        ->setCellValue('L16', $qty);

    $price = _getPrice($qty);
    $spreadsheet
        ->setActiveSheetIndex(0)
        ->setCellValue('N16', $price);

    $amountText = sprintf('Всего стоимость с НДС: %s', num2str($price * $qty));
    $spreadsheet
        ->setActiveSheetIndex(0)
        ->setCellValue('I22', $amountText);

    $acceptedByText = sprintf('%s %s', $_POST['post'], $_POST['postName']);
    $spreadsheet
        ->setActiveSheetIndex(0)
        ->setCellValue('J28', $acceptedByText);

/*    $spreadsheet->getActiveSheet()
        ->getProtection()->setPassword('abc123456!');*/
    $spreadsheet->getActiveSheet()
        ->getProtection()->setSheet(true);
    $spreadsheet->getActiveSheet()
        ->getProtection()->setSort(true);
    $spreadsheet->getActiveSheet()
        ->getProtection()->setInsertRows(true);
    $spreadsheet->getActiveSheet()
        ->getProtection()->setFormatCells(true);
    $spreadsheet->getActiveSheet()->getStyle('N16')
        ->getProtection()
        ->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_PROTECTED);

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $path = sprintf("%s/naklad.xlsx", $docsPath);
    $writer->save($path);

    return $path;
}


function _getPrice($qty) {

    $nosql = new NoSQLite\NoSQLite('mydb.sqlite');
    $store = $nosql->getStore('ranges');

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

    foreach ($ranges as $range) {
        if($qty >= $range['from'] && $qty <= $range['to']) {
            return $range['price'];
        }
    }

    return 1.5;
}


function sendEmail($id, $baseUrl) {
    $to      = '7744704@gmail.com';
    $subject = 'Holiminsk - заказ';
    $message = sprintf('<a href="%sshow-order.php?id=%s">Заказ %s</a>', $baseUrl, $id, $id);
    $headers = 'From: orders@holiminsk.by' . "\r\n" .
        'Reply-To: 7744704@gmail.com' . "\r\n" .
        'Content-Type: text/html; charset=UTF-8' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    $headers .= "";

    mail($to, $subject, $message, $headers);
}

/**
 * Возвращает сумму прописью
 * @author runcore
 * @uses morph(...)
 */
function num2str($num) {
    $nul='ноль';
    $ten=array(
        array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
    );
    $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
    $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
    $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
    $unit=array( // Units
        array('копейка' ,'копейки' ,'копеек',	 1),
        array('рубль'   ,'рубля'   ,'рублей'    ,0),
        array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
        array('миллион' ,'миллиона','миллионов' ,0),
        array('миллиард','милиарда','миллиардов',0),
    );
    //
    list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
    $out = array();
    if (intval($rub)>0) {
        foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
            if (!intval($v)) continue;
            $uk = sizeof($unit)-$uk-1; // unit key
            $gender = $unit[$uk][3];
            list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
            else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
            // units without rub & kop
            if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
        } //foreach
    }
    else $out[] = $nul;
    $out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
    $out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
    return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}

/**
 * Склоняем словоформу
 * @ author runcore
 */
function morph($n, $f1, $f2, $f5) {
    $n = abs(intval($n)) % 100;
    if ($n>10 && $n<20) return $f5;
    $n = $n % 10;
    if ($n>1 && $n<5) return $f2;
    if ($n==1) return $f1;
    return $f5;
}