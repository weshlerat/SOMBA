<?php
declare(strict_types=1);
session_start();
require __DIR__.'/../src/bootstrap.php';
$cartId=trim((string)($_GET['cartId']??$_GET['cart_id']??$_SESSION['somba_maketou_cart_id']??''));
if($cartId!==''){
 try{$mk=http_json('GET','https://api.maketou.net/api/v1/stores/cart/'.rawurlencode($cartId),['Authorization: Bearer '.(string)config('maketou.api_key')]);if($mk['status']===200&&($mk['body']['status']??'')==='payment_failed')$pdo->prepare("UPDATE orders SET payment_status='failed' WHERE maketou_reference=? AND payment_status<>'paid'")->execute([$cartId]);}catch(Throwable $e){error_log('[SOMBA Maketou] '.$e->getMessage());}
}
unset($_SESSION['somba_order_id'],$_SESSION['somba_maketou_cart_id']);
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SOMBA — Paiement échoué</title></head><body><main><h1>Paiement échoué</h1><p>Le paiement n’a pas pu être finalisé. Aucun top-up ne sera envoyé.</p><a href="/">Retour à SOMBA</a></main></body></html>
