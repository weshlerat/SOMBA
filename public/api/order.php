<?php
require __DIR__.'/../../src/bootstrap.php';
$d=input_json();
$productId=(int)($d['product_id']??0);$customer=$d['customer']??[];$email=filter_var($d['email']??'',FILTER_VALIDATE_EMAIL)?$d['email']:null;
if(!$productId||!is_array($customer))json_response(['error'=>'invalid_request'],422);
$s=$pdo->prepare('SELECT * FROM products WHERE id=? AND active=1');$s->execute([$productId]);$p=$s->fetch();if(!$p)json_response(['error'=>'product_not_found'],404);
$order=order_number();$s=$pdo->prepare('INSERT INTO orders(order_number,product_id,customer_email,customer_data,amount,currency) VALUES(?,?,?,?,?,?)');$s->execute([$order,$productId,$email,json_encode($customer,JSON_UNESCAPED_UNICODE),$p['price'],$p['currency']]);
json_response(['order_number'=>$order,'amount'=>$p['price'],'currency'=>$p['currency'],'maketou_cart_id'=>$p['maketou_cart_id'],'message'=>'Order created. Redirect to the configured Maketou cart.']);
