<?php
declare(strict_types=1);
require __DIR__.'/../../src/bootstrap.php';

$raw=file_get_contents('php://input')?:'';
$signature=$_SERVER['HTTP_X_WEBHOOK_SIGNATURE']??$_SERVER['HTTP_X_TOPUP_SIGNATURE']??$_SERVER['HTTP_X_SIGNATURE']??'';
$secret=(string)config('topup.webhook_secret');
if($secret==='')json_response(['error'=>'webhook_not_configured'],500);
if($signature!==''){
    $given=preg_replace('/^sha256=/i','',$signature);
    if(!hash_equals(hash_hmac('sha256',$raw,$secret),$given))json_response(['error'=>'invalid_signature'],401);
}

$payload=json_decode($raw,true);
if(!is_array($payload))json_response(['error'=>'invalid_json'],400);
$eventType=(string)($payload['type']??$payload['event']??$payload['event_type']??'');
$eventId=(string)($payload['id']??$payload['event_id']??$payload['eventId']??'');
$orderData=is_array($payload['order']??null)?$payload['order']:$payload;
$providerOrderId=(string)($orderData['id']??$orderData['order_id']??$orderData['orderId']??'');

if($eventId!==''){
    try{$pdo->prepare('INSERT INTO webhook_events(provider,event_id,payload,processed) VALUES(?,?,?,0)')->execute(['topup.dev',$eventId,$raw]);}
    catch(PDOException $e){if((int)$e->errorInfo[1]===1062)json_response(['ok'=>true,'duplicate'=>true]);throw $e;}
}

$map=['order.fulfilled'=>'delivered','order.failed'=>'failed','order.refunded'=>'failed','order.accepted'=>'processing','order.awaiting_confirmation'=>'processing'];
$status=$map[$eventType]??null;
if($providerOrderId!==''&&$status!==null){
    $set="delivery_status=?";
    $params=[$status];
    if($status==='delivered'){$set.=',delivered_at=NOW()';}
    $set.=',provider_response=?';$params[]=json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);$params[]=$providerOrderId;
    $pdo->prepare("UPDATE orders SET $set WHERE provider_order_id=?")->execute($params);
}
if($eventId!=='')$pdo->prepare('UPDATE webhook_events SET processed=1 WHERE provider=? AND event_id=?')->execute(['topup.dev',$eventId]);
json_response(['ok'=>true]);
