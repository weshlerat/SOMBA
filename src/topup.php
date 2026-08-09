<?php
declare(strict_types=1);
require_once __DIR__.'/bootstrap.php';
function topup_create_order(PDO $pdo,array $order):array
{
    $s=$pdo->prepare('SELECT * FROM orders WHERE id=? LIMIT 1');$s->execute([(int)$order['id']]);$order=$s->fetch();
    if(!$order)return ['success'=>false,'error'=>'order_not_found'];
    if(!empty($order['provider_order_id']))return ['success'=>true,'provider_order_id'=>$order['provider_order_id'],'already_created'=>true];
    $s=$pdo->prepare('SELECT * FROM products WHERE id=? LIMIT 1');$s->execute([(int)$order['product_id']]);$product=$s->fetch();
    if(!$product||empty($product['provider_sku']))return ['success'=>false,'error'=>'missing_provider_sku'];
    $customer=json_decode((string)$order['customer_data'],true);$customer=is_array($customer)?$customer:[];
    $player=isset($customer['player'])&&is_array($customer['player'])?$customer['player']:[];
    if(!$player&&isset($customer['identifier']))$player=['uid'=>(string)$customer['identifier']];
    if(!$player&&isset($customer['uid']))$player=['uid'=>(string)$customer['uid']];
    if(!$player)return ['success'=>false,'error'=>'missing_player_data'];
    $payload=['sku'=>(string)$product['provider_sku'],'player'=>$player,'callback_url'=>rtrim((string)config('app.base_url'),'/').'/api/topup-webhook.php'];
    try{$response=http_json('POST','https://topup.dev/api/v1/orders',['Authorization: Bearer '.(string)config('topup.api_key'),'Idempotency-Key: '.(string)$order['order_number']],$payload);}catch(Throwable $e){error_log('[SOMBA topup] '.$e->getMessage());return ['success'=>false,'error'=>'topup_http_error'];}
    $body=$response['body'];
    if($response['status']<200||$response['status']>=300){$pdo->prepare("UPDATE orders SET delivery_status='failed',provider_response=? WHERE id=?")->execute([json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),(int)$order['id']]);return ['success'=>false,'error'=>'topup_rejected','status'=>$response['status']];}
    $providerId=$body['id']??$body['order']['id']??$body['orderId']??null;
    $pdo->prepare("UPDATE orders SET provider_order_id=?,provider_response=?,delivery_status='processing' WHERE id=?")->execute([(string)$providerId,json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),(int)$order['id']]);
    return ['success'=>true,'provider_order_id'=>$providerId,'response'=>$body];
}
