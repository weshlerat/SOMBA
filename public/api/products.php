<?php
require __DIR__.'/../../src/bootstrap.php';
$stmt=$pdo->query("SELECT p.id,p.name,p.slug,p.price,p.currency,p.delivery_type,g.name game,g.slug game_slug FROM products p JOIN games g ON g.id=p.game_id WHERE p.active=1 AND g.active=1 ORDER BY g.name,p.price");
json_response(['products'=>$stmt->fetchAll()]);
