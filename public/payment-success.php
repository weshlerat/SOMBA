<?php
declare(strict_types=1);
session_start();
require __DIR__.'/../src/bootstrap.php';
require_once __DIR__.'/../src/topup.php';

function page_error(string $message,int $status=400):never{http_response_code($status);?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SOMBA — Paiement</title></head><body><main><h1>Paiement non confirmé</h1><p><?=htmlspecialchars($message,ENT_QUOTES,'UTF-8')?></p></main></body></html><?php exit;}

$cartId=trim((string)($_GET['cartId']??$_GET['cart_id']??$_SESSION['somba_maketou_cart_id']??''));
$orderId=(int)($_SESSION['somba_order_id']??0);
if($cartId==='')page_error('Identifiant du panier Maketou introuvable.');

$s=$pdo->prepare('SELECT * FROM orders WHERE maketou_reference=? LIMIT 1');$s->execute([$cartId]);$order=$s->fetch();
if(!$order&&$orderId){$s=$pdo->prepare('SELECT * FROM orders WHERE id=? LIMIT 1');$s->execute([$orderId]);$order=$s->fetch();}
if(!$order)page_error('Commande SOMBA introuvable.',404);

try{$mk=http_json('GET','https://api.maketou.net/api/v1/stores/cart/'.rawurlencode($cartId),['Authorization: Bearer '.(string)config('maketou.api_key')]);}catch(Throwable $e){error_log('[SOMBA Maketou] '.$e->getMessage());page_error('Impossible de vérifier le paiement.',502);}
if($mk['status']!==200)page_error('Maketou n’a pas pu confirmer le panier.',502);
$cart=$mk['body'];$status=(string)($cart['status']??'');
if($status==='abandoned'){header('Location: /payment-cancelled.php?cartId='.rawurlencode($cartId));exit;}
if($status==='payment_failed'){header('Location: /payment-failed.php?cartId='.rawurlencode($cartId));exit;}
if($status!=='completed')page_error('Le paiement est encore en attente.');

$pdo->prepare("UPDATE orders SET payment_status='paid',payment_provider='maketou',maketou_reference=? WHERE id=?")->execute([$cartId,(int)$order['id']]);
$order['payment_status']='paid';
$result=topup_create_order($pdo,$order);
if(!$result['success']){?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SOMBA — Paiement confirmé</title></head><body><main><h1>Paiement confirmé ✓</h1><p>Ta commande est payée. La livraison est en cours de traitement.</p><p>Commande : <?=htmlspecialchars((string)$order['order_number'],ENT_QUOTES,'UTF-8')?></p></main></body></html><?php exit;}
unset($_SESSION['somba_order_id'],$_SESSION['somba_maketou_cart_id']);
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SOMBA — Commande confirmée</title></head><body><main><h1>Paiement confirmé ✓</h1><p>Ta commande est maintenant envoyée à Topup.dev pour livraison.</p><p>Commande : <?=htmlspecialchars((string)$order['order_number'],ENT_QUOTES,'UTF-8')?></p></main></body></html>
