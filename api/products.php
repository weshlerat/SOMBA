<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
$config=require __DIR__.'/../config/config.php';
try{$pdo=new PDO("mysql:host={$config['db']['host']};port={$config['db']['port']};dbname={$config['db']['name']};charset={$config['db']['charset']}",$config['db']['user'],$config['db']['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);$q=$pdo->query("SELECT p.id,p.name,p.price,p.currency,g.name game FROM products p JOIN games g ON g.id=p.game_id WHERE p.active=1 AND g.active=1 ORDER BY g.name,p.price");echo json_encode(['products'=>$q->fetchAll()],JSON_UNESCAPED_UNICODE);}catch(Throwable $e){http_response_code(500);echo json_encode(['error'=>'Database unavailable']);}
