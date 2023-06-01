



<!-- $apiurl = 'https://holidays-jp.github.io/api/v1/2023/date.json';
$jsonholiday = file_get_contents($apiurl);
$jsonholiday = json_decode($jsonholiday,true);


//echo(getholidayfromapi());
file_put_contents("sampleholiapi.txt",json_encode($jsonholiday, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),FILE_APPEND ); -->


<?php
// APIアクセスURL
$url = 'https://holidays-jp.github.io/api/v1/2023/date.json';

// ストリームコンテキストのオプションを作成
$options = array(
    // HTTPコンテキストオプションをセット
    'http' => array(
        'method'=> 'GET',
        'header'=> 'Content-type: application/json; charset=UTF-8' //JSON形式で表示
    )
);

// ストリームコンテキストの作成

$raw_data = mb_convert_encoding(file_get_contents($url), 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');


// debug
//var_dump($raw_data);

// json の内容を連想配列として $data に格納する
$data = json_decode($raw_data,true);

if($data === NULL){
    echo "データがありません";

}else{
    foreach($data as $key => $day){
        echo (gettype( strtotime($key)));
        echo $day;
    }
}