<?php

require_once(dirname(__FILE__) . '/../PayxgoClient.php');

$client = new PayxgoClient(
    'api server domian',
    'your secret key', 
    'your access key'
);
$params = array(
    'currency'=>'USD', // 支付币种
    'amount'=>2,  // 支付金额
    'vendor'=>'alipay',  // 支付通道 目前只支付alipay
    'orderNum'=>'mXHxDUIH59lCwAn', // 客户订单号(必须具有唯一性， 否则将请求失败)
    'ipnUrl'=>'https://www.saleoner.com/go/v1/ipnCallback' // 异步回调通知地址
);
echo $client->securepay($params)."</br>";
sleep(3);
$client = new PayxgoClient(
    'api server domian',
    'your secret key', 
    'your access key',
    $client->cookie
);
echo $client->qrRefresh([]);