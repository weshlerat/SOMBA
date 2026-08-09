<?php
declare(strict_types=1);
require __DIR__.'/../../src/bootstrap.php';

if(session_status()!==PHP_SESSION_ACTIVE)session_start();
$d=input_json();
$productId=(int)($d['product_id']??0);
$customer=is_array($d['customer']??null)?$d['customer']:[];
$email=filter_var($d['email']??'',FILTER_VALIDATE_EMAIL)?trim((string)$d['email']):null;
$firstName=trim((string)($d['first_name']??$customer['firstName']??''));
$lastName=trim((string)($d['last_name']??$customer['lastName']??''));
$phone=trim((string)($d['phone']??$customer['phone']??''));
if(!$productId||!$email||$firstName===''||$lastName===''||!$customer)json_response(['error'=>'invalid_request','required'=>['product_id','email','first_name','last_name','customer']],422);

$s=$pdo->prepare('SELECT * FROM products WHERE id=? AND active=1');$s->execute([$productId]);$p=$s->fetch();
if(!$p)json_response(['error'=>'product_not_found'],404);
if(empty($p['maketou_cart_id']))json_response(['error'=>'maketou_product_id_missing'],422);

$order=order_number();
$customerData=$customer;
$customerData['firstName']=$firstName;$customerData['lastName']=$lastName;if($phone!=='')$customerData['phone']=$phone;
$s=$pdo->prepare('INSERT INTO orders(order_number,product_id,customer_email,customer_data,amount,currency,payment_provider) VALUES(?,?,?,?,?,?,?)');
$s->execute([$order,$productId,$email,json_encode($customerData,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$p['price'],$p['currency'],'maketou']);
$orderId=(int)$pdo->lastInsertId();

$redirect=rtrim((string)config('app.base_url'),'/').'/payment-success.php';
$payload=['productDocumentId'=>(string)$p['maketou_cart_id'],'email'=>$email,'firstName'=>$firstName,'lastName'=>$lastName];
if($phone!=='')$payload['phone']=$phone;
$payload['redirectURL']=$redirect;
$payload['meta']=['orderId'=>(string)$orderId,'orderNumber'=>$order,'source'=>'somba'];

try{$mk=http_json('POST','https://api.maketou.net/api/v1/stores/cart/checkout',['Authorization: Bearer '.(string)config('maketou.api_key')],$payload);}catch(Throwable $e){$pdo->prepare("UPDATE orders SET payment_status='failed',provider_response=? WHERE id=?")->execute([json_encode(['error'=>$e->getMessage()],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$orderId]);json_response(['error'=>'maketou_unavailable'],502);}
if($mk['status']!==201){$pdo->prepare("UPDATE orders SET payment_status='failed',provider_response=? WHERE id=?")->execute([json_encode($mk['body'],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$orderId]);json_response(['error'=>'maketou_checkout_failed','details'=>$mk['body']],502);}

$cart=$mk['body']['cart']??[];$cartId=$cart['id']??null;$redirectUrl=$mk['body']['redirectUrl']??null;
if(!$cartId||!$redirectUrl){json_response(['error'=>'maketou_invalid_response'],502);}
$pdo->prepare('UPDATE orders SET maketou_reference=?,payment_url=?,provider_response=? WHERE id=?')->execute([(string)$cartId,(string)$redirectUrl,json_encode($mk['body'],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),$orderId]);
$_SESSION['somba_order_id']=$orderId;$_SESSION['somba_maketou_cart_id']=(string)$cartId;
json_response(['order_id'=>$orderId,'order_number'=>$order,'amount'=>$p['price'],'currency'=>$p['currency'],'maketou_cart_id'=>$cartId,'redirect_url'=>$redirectUrl]);
