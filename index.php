<?php

// download file
// proses perbaris
// - menambahkan data baru
// - olah discounts
// - print out
// - export ke file baru (.jsonl)

use Rs\JsonLines\JsonLines;

require_once 'vendor/autoload.php';

// decode file .jsonl to array
$jsonLine = (new JsonLines())->delineFromFile('data.jsonl');
$json = json_decode($jsonLine, true);

// create full_name
$addFullName = array_map(function ($a){
    $a['customer']['full_name'] = $a['customer']['first_name'] . ' ' . $a['customer']['last_name'];
    return $a;
}, $json);

// create total_purchase and total_price with calculating shipping_price, discounts, quantity and unit_price
$totalPurchase = [];
foreach ($addFullName as $value) {

    // calculating total_price (unit_price * quantity)
    $totalPrice = 0;
    foreach ($value['items'] as $val) {
        $totalPrice = $totalPrice + ($val['unit_price'] * $val['quantity']);
    }
    $value['total_price'] = $totalPrice;

    // calculating discounts (with dollar or percentage type)
    if (!empty($value['discounts'])) {
        foreach ($value['discounts'] as $val) {
            if ($val['type'] == 'DOLLAR') {
                $value['total_price'] = $value['total_price'] - $val['value'];
            }

            if ($val['type'] == 'PERCENTAGE') {
                $disc = ($value['total_price'] * $val['value']) / 100;
                $value['total_price'] = $value['total_price'] - $disc;
            }
        }
    }

    // calculating final total
    $value['total_purchase'] = $value['total_price'] + $value['shipping_price'];
    array_push($totalPurchase, $value);
}
// print out result
// print_r($totalPurchase);

// export to file .jsonl
(new JsonLines())->enlineToFile($totalPurchase, 'newData.jsonl');
