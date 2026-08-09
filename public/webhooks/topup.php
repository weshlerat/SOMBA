<?php
require __DIR__.'/../../src/bootstrap.php';
$raw=file_get_contents('php://input')?:'{}';$event=json_decode($raw,true);if(!is_array($event))json_response(['error'=>'invalid_payload'],400);
$eventId=(string)($event['id']??$event['event_id']??hash('sha256',$raw));
try{$s=$pdo->prepare('INSERT INTO webhook_events(provider,event_id,payload) VALUES(?,?,?)');$s->execute(['topup.dev',$eventId,$raw]);}catch(PDOException $e){json_response(['received'=>true,'duplicate'=>true]);}
// Verify the provider signature and map the exact callback status/order fields before production.
json_response(['received'=>true]);
