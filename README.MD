# sdk引入
        直接拷贝整个目录到项目中使用即可
# 说明
* 此版本为php>=5.3版本调用
* 请求调用频率为每分钟一次

# 币种支持
* USD: 美元
* CNY: 人民币

# 支付通道支持
* ALIPAY

---

# 异步回调说明
### 订单支付成功后，payxgo会异步返回交易结果到IPN url，请求方式为HTTP POST。请尽可能根据IPN url收到的结果处理商户系统中的订单状态。 payxgo会在3小时内自动重复多次发送异步通知，直到商户服务器回复“ok”消息在响应中
* ipn_url 必须是在Internet中可以访问的地址，不能是内网地址(如localhost)或内网IP
* 多次的IPN异步通知，不要重复处理
* 使用POST方式发送IPN通知信息，注意接受参数的处理方式
* 必须保证异步通知页面(ipn_url)上无任何字符，如空格，HTML标签等，程序执行完成后必须打印输入“ok”(不包括引号)，如果商户反馈给payxgo的字符不是ok这2个字符，payxgo服务器会不断重发通知，最多发送3次
* 程序执行完成后，ipn_url页面不能执行页面跳转，否则payxgo会因收不到正确结果而重发结果通知
* RESPONSE SCHEMA: application/json

---

# 传参说明
```php
	$client = new PayxgoClient($apiDomain, $secretKey, $accessKey, $cookie);
```
* $apiDomain: 支付服务器地址，在用户注册端，  可在api token页面获取
* $secretKey: 支付私钥， 在api token页面获取
* $accessKey: 访问密钥， 在api token页面获取
* $cookie: 刷新时携带
    * 请求支付链接，可忽略此参数(见下方示例)
    * 刷新时必须携带, 不能修改cookie的任何内容

---


# 请求支付链接(链接可生成二维码用于扫码支付)(示例)
```go
// 获取支付链接和支付信息
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
		'ipnUrl'=>'https://www.xxxx.com/go/v1/ipnCallback' // 异步回调通知地址
	);
	echo $client->securepay($params)."</br>";
```
### 结果(示例)
#### 支付信息(示例):
```json
{
    "data":{
        "payUrl":"https://qr.alipay.com/upxwhdspbkdqsjvpa7",   // 支付链接
        "payAmount":"2.00",   // 支付金额
        "payCoin":"USD",   // 支付币种
        "refresh":1,    // 是否支持刷新
        "orderNum":"109666486571008",  // 订单号
    },
    "status":{"code":0,"message":"成功"}}  // 请求状态
```
#### 返回的cookie信息(示例)
每次请求后可以从 $client->cookie中获取;
```
ctoken=3F7W-We78gvUB9b7;zone=GZ00G;sessionId=GZ00kgkaKU8vvwDuy9tYdfhBu41D1ssuperapiGZ00RZ42;gateway=aHR0cHM6Ly9vcGVuYXBpLmFsaXBheS5jb20vZ2F0ZXdheS5kbz9hcHBfaWQ9MjAxOTAzMTI2MzUzNDI2NSZiaXpfY29udGVudD0lN0IlMjJleHRlbmRfcGFyYW1zJTIyJTNBJTdCJTIyYmFzZV9jdXJyZW5jeSUyMiUzQSUyMlVTRCUyMiUyQyUyMmNoZWNrX3BtcyUyMiUzQSUyMkYlMjIlMkMlMjJjaGVja190cmFkZV9pbmZvJTIyJTNBJTIyRiUyMiUyQyUyMmNsZWFyaW5nSW5zdElkJTIyJTNBJTIyQk9DSEswMDI4OENOWSUyMiUyQyUyMmV4Y2hhbmdlX2N1cnJlbmN5JTIyJTNBJTIyQ05ZJTIyJTJDJTIyZXh0ZXJuYWxfbWVyY2hhbnRfaW5kdXN0cnklMjIlM0ElMjI3Mzk5JTIyJTJDJTIyZXh0ZXJuYWxfc2l0ZV9pZCUyMiUzQSUyMjEwMjIxODEwMDAwMDAwMDAwMDElMjIlMkMlMjJvcmdfcGlkJTIyJTNBJTIyMjE4MTExMDAwNzg0ODQzNCUyMiUyQyUyMnNldHRsZV9hbW91bnQlMjIlM0ElMjIwLjE0JTIyJTJDJTIyc2V0dGxlX3JhdGUlMjIlM0ElMjI2Ljc4NDIzNDY2JTIyJTJDJTIyc2V0dGxlX3JhdGVfaWQlMjIlM0ElMjIyMDIzMDIwN0M1NDgxNTI5MTclMjIlN0QlMkMlMjJtZXJjaGFudF9vcmRlcl9ubyUyMiUzQSUyMjE1MTE4Mjg0MjY3NTIlMjIlMkMlMjJvdXRfdHJhZGVfbm8lMjIlM0ElMjIyMDIzMDIwNzE5MDc0MTAxMDAwMTUwMDg0NDY1NTYyJTIyJTJDJTIycHJvZHVjdF9jb2RlJTIyJTNBJTIyTkVXX09WRVJTRUFTX1NFTExFUiUyMiUyQyUyMnNldHRsZV9jdXJyZW5jeSUyMiUzQSUyMkNOWSUyMiUyQyUyMnN1Yl9tZXJjaGFudCUyMiUzQSU3QiUyMmNhdGVnb3J5X2lkJTIyJTNBJTIyNTY5OCUyMiUyQyUyMm1lcmNoYW50X2VuZ19uYW1lJTIyJTNBJTIyTkVTU0JJVCUyMElOQyUyMiUyQyUyMm1lcmNoYW50X2lkJTIyJTNBJTIyMjE4MTQxMDAxMzA2NDUzOSUyMiUyQyUyMm1lcmNoYW50X25hbWUlMjIlM0ElMjJORVNTQklUJTIwSU5DJTIyJTJDJTIybWVyY2hhbnRfdHlwZSUyMiUzQSUyMm1lcmNoYW50JTIyJTdEJTJDJTIyc3ViamVjdCUyMiUzQSUyMk5FU1NCSVQlMjBJTkMlMjIlMkMlMjJ0aW1lb3V0X2V4cHJlc3MlMjIlM0ElMjIxMTltJTIyJTJDJTIydG90YWxfYW1vdW50JTIyJTNBJTIyMC4wMiUyMiUyQyUyMnRyYWRlX2luZm9ybWF0aW9uJTIyJTNBJTdCJTIyYnVzaW5lc3NfdHlwZSUyMiUzQSUyMjQlMjIlN0QlMkMlMjJ0cmFuc19jdXJyZW5jeSUyMiUzQSUyMlVTRCUyMiU3RCZjaGFyc2V0PVVURi04Jm1ldGhvZD1hbGlwYXkudHJhZGUucGFnZS5wYXkmcmV0dXJuX3VybD1odHRwcyUzQSUyRiUyRmJndy5uaWhhb3BheS5jb20lMkZjYWxsYmFjayUyRmFtcyUyRjg0OTExNzMyZTA5NzRhNGRiMDcxMWExZWJkNWIwZjEzJnNpZ249Vk5sa3NnWlVOSSUyRjExV01zMk9TTWUxM3hkMnolMkIwSU1OMkszdzVkRVBJUm5ETVlhd3cxV1hSbGRHbVpLVHoyM3J2ZzA1VDJVa2dvNjNydWVnN3NNUG9NS2VpM2JkZzl5b0ZIVEM5Tnlrc0ZUWnJoNGhFRU9NVXhOb3VzNFJiTEQ0MEV4cVliUlRaUHkzZDBjcUVaVlRlN1I1YiUyQjEyYlg5SXVad2FnTTM5dG9wRWVZYkhyR0prYlA3OXM5QU5lRWNkYmZNWVhCclolMkJuZDJNdnp1bDBIak1kMSUyQlY3OWUlMkZXa1p5Y2Z3WlklMkZqZnQ2MzVobGlSJTJGTWU4Qk1za2ZoQmd1TUdraUxFcEpVMDNRVW4yNm5MaDF5T3VUVzVzJTJCVUd3OUtlZ3BMdWVzZzJ1T0o0WkFFeThOJTJGMTNRWnF0R0ozdVpCbVVoTEhPUmRmYVNsek9nNm9iVCUyQkRSUSUzRCUzRCZzaWduX3R5cGU9UlNBMiZ0aW1lc3RhbXA9MjAyMy0wMi0wNyUyMDEwJTNBNTUlM0EzMCZ2ZXJzaW9uPTEuMA==;model=ALIPAY
```



# 刷新支付链接(示例)
```php
// 刷新支付链接  刷新请求1分钟一次
	$client = new PayxgoClient(
		'api server domian',
		'your secret key', 
		'your access key',
		'last cookie'
	);
	echo $client->qrRefresh([]);
```
### 结果(示例)
#### 刷新返回信息(示例)
```json
{
    "data":{
        "refresh":"https://qr.alipay.com/upx035pmpup4e6vy6b", // 刷新后的支付链接
    },
    "status":{   // 请求状态
        "code":0,
        "message":"成功"
    }
}
```
#### 返回的cookie信息(示例)
```
ctoken=ypxN19ijRKZ7Outa;zone=GZ00G;sessionId=GZ00A2iaFJQvegEK0vNRzRQOt0X6QOsuperapiGZ00RZ41;gateway=aHR0cHM6Ly9vcGVuYXBpLmFsaXBheS5jb20vZ2F0ZXdheS5kbz9hcHBfaWQ9MjAxOTAzMTI2MzUzNDI2NSZiaXpfY29udGVudD0lN0IlMjJleHRlbmRfcGFyYW1zJTIyJTNBJTdCJTIyYmFzZV9jdXJyZW5jeSUyMiUzQSUyMlVTRCUyMiUyQyUyMmNoZWNrX3BtcyUyMiUzQSUyMkYlMjIlMkMlMjJjaGVja190cmFkZV9pbmZvJTIyJTNBJTIyRiUyMiUyQyUyMmNsZWFyaW5nSW5zdElkJTIyJTNBJTIyQk9DSEswMDI4OENOWSUyMiUyQyUyMmV4Y2hhbmdlX2N1cnJlbmN5JTIyJTNBJTIyQ05ZJTIyJTJDJTIyZXh0ZXJuYWxfbWVyY2hhbnRfaW5kdXN0cnklMjIlM0ElMjI3Mzk5JTIyJTJDJTIyZXh0ZXJuYWxfc2l0ZV9pZCUyMiUzQSUyMjEwMjIxODEwMDAwMDAwMDAwMDElMjIlMkMlMjJvcmdfcGlkJTIyJTNBJTIyMjE4MTExMDAwNzg0ODQzNCUyMiUyQyUyMnNldHRsZV9hbW91bnQlMjIlM0ElMjIwLjE0JTIyJTJDJTIyc2V0dGxlX3JhdGUlMjIlM0ElMjI2Ljc4NDIzNDY2JTIyJTJDJTIyc2V0dGxlX3JhdGVfaWQlMjIlM0ElMjIyMDIzMDIwN0M1NDgxNTI5MTclMjIlN0QlMkMlMjJtZXJjaGFudF9vcmRlcl9ubyUyMiUzQSUyMjE1MTE4Mjg0MjY3NTIlMjIlMkMlMjJvdXRfdHJhZGVfbm8lMjIlM0ElMjIyMDIzMDIwNzE5MDc0MTAxMDAwMTUwMDg0NDY1NTYyJTIyJTJDJTIycHJvZHVjdF9jb2RlJTIyJTNBJTIyTkVXX09WRVJTRUFTX1NFTExFUiUyMiUyQyUyMnNldHRsZV9jdXJyZW5jeSUyMiUzQSUyMkNOWSUyMiUyQyUyMnN1Yl9tZXJjaGFudCUyMiUzQSU3QiUyMmNhdGVnb3J5X2lkJTIyJTNBJTIyNTY5OCUyMiUyQyUyMm1lcmNoYW50X2VuZ19uYW1lJTIyJTNBJTIyTkVTU0JJVCUyMElOQyUyMiUyQyUyMm1lcmNoYW50X2lkJTIyJTNBJTIyMjE4MTQxMDAxMzA2NDUzOSUyMiUyQyUyMm1lcmNoYW50X25hbWUlMjIlM0ElMjJORVNTQklUJTIwSU5DJTIyJTJDJTIybWVyY2hhbnRfdHlwZSUyMiUzQSUyMm1lcmNoYW50JTIyJTdEJTJDJTIyc3ViamVjdCUyMiUzQSUyMk5FU1NCSVQlMjBJTkMlMjIlMkMlMjJ0aW1lb3V0X2V4cHJlc3MlMjIlM0ElMjIxMTltJTIyJTJDJTIydG90YWxfYW1vdW50JTIyJTNBJTIyMC4wMiUyMiUyQyUyMnRyYWRlX2luZm9ybWF0aW9uJTIyJTNBJTdCJTIyYnVzaW5lc3NfdHlwZSUyMiUzQSUyMjQlMjIlN0QlMkMlMjJ0cmFuc19jdXJyZW5jeSUyMiUzQSUyMlVTRCUyMiU3RCZjaGFyc2V0PVVURi04Jm1ldGhvZD1hbGlwYXkudHJhZGUucGFnZS5wYXkmcmV0dXJuX3VybD1odHRwcyUzQSUyRiUyRmJndy5uaWhhb3BheS5jb20lMkZjYWxsYmFjayUyRmFtcyUyRjg0OTExNzMyZTA5NzRhNGRiMDcxMWExZWJkNWIwZjEzJnNpZ249Vk5sa3NnWlVOSSUyRjExV01zMk9TTWUxM3hkMnolMkIwSU1OMkszdzVkRVBJUm5ETVlhd3cxV1hSbGRHbVpLVHoyM3J2ZzA1VDJVa2dvNjNydWVnN3NNUG9NS2VpM2JkZzl5b0ZIVEM5Tnlrc0ZUWnJoNGhFRU9NVXhOb3VzNFJiTEQ0MEV4cVliUlRaUHkzZDBjcUVaVlRlN1I1YiUyQjEyYlg5SXVad2FnTTM5dG9wRWVZYkhyR0prYlA3OXM5QU5lRWNkYmZNWVhCclolMkJuZDJNdnp1bDBIak1kMSUyQlY3OWUlMkZXa1p5Y2Z3WlklMkZqZnQ2MzVobGlSJTJGTWU4Qk1za2ZoQmd1TUdraUxFcEpVMDNRVW4yNm5MaDF5T3VUVzVzJTJCVUd3OUtlZ3BMdWVzZzJ1T0o0WkFFeThOJTJGMTNRWnF0R0ozdVpCbVVoTEhPUmRmYVNsek9nNm9iVCUyQkRSUSUzRCUzRCZzaWduX3R5cGU9UlNBMiZ0aW1lc3RhbXA9MjAyMy0wMi0wNyUyMDEwJTNBNTUlM0EzMCZ2ZXJzaW9uPTEuMA==;model=ALIPAY
```