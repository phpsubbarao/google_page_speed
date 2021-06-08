<?php
echo "<pre>";
//error_reporting(0);
require 'vendor/autoload.php';
ini_set('max_execution_time', 0);
date_default_timezone_set("Asia/Calcutta");   //India time (GMT+5:30)


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

 // $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile('list.xlsx');
 // $reader->setReadDataOnly(TRUE);


$api_key = 'AIzaSyBEJzetKEqfXXaokCZAuWQ5ZZbNpbBTjHI';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1','URL');
$sheet->setCellValue('B1', 'Desktop');
$sheet->setCellValue('C1', 'Mobile');
$sheet->setCellValue('D1', 'Date');

$str = file_get_contents('tz_list.json');
$json = json_decode($str, true); // decode the JSON into an associative array


foreach ($json as $key => $value) {
 
  foreach ($data as $keyss => $valueaa) {
    foreach ($valueaa as $keys => $values) {

      $url_list[$values['A']] = $values['A'];
    }
  }

  $keys = $key + 2; 
  echo $keys."<br>";
  echo $value."<br>";
  $mobile = find_score( $value, 'mobile', $api_key );
  $desctop = find_score( $value, 'desktop', $api_key );

  echo "Mobile: " . $mobile . '; ';
  echo "Desctop: " . $desctop . '; '; 

  $sheet->setCellValue('A'.$keys, $value);
  $sheet->setCellValue('B'.$keys, $desctop);
  $sheet->setCellValue('C'.$keys, $mobile);
  $sheet->setCellValue('D'.$keys, date('Y-m-d H:i:s'));
  $writer = new Xlsx($spreadsheet);
  $writer->save('trianz_google_page_speed_'.date('Y_m_d').'.xlsx');
  flush();
  ob_flush();
  sleep(1);
  echo date('Y-m-d H:i:s')."<br>";

}

/**
 * Find PSI api score for certain device of certain url
 *
 * @param string $url
 * @param string $device Possible values: desctop, mobile
 * @param string $key
 *
 * @return string | integer
 */
function find_score( $url, $device, $key = '' ) {

  $url = 
  "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=" . $url . "&category=performance&fields=lighthouseResult%2Fcategories%2F*%2Fscore&prettyPrint=false&strategy=" . $device . "&key=" . $key;

  $init = curl_init();

  curl_setopt($init, CURLOPT_URL, $url);
  curl_setopt($init, CURLOPT_RETURNTRANSFER, true);

  $responce = curl_exec( $init );
  curl_close($init);

  $responce = json_decode( $responce );

  if ( ! empty( $responce->lighthouseResult->categories->performance->score ) ) {
    $score = $responce->lighthouseResult->categories->performance->score;
    $score = $score * 100;
  } else {
    $score = 'API Error';
  }

  return $score;
}