<?php
declare(strict_types=1);
final class TopupClient {
 private string $key; private string $base;
 public function __construct(string $key,string $base='https://topup.dev'){$this->key=$key;$this->base=rtrim($base,'/');}
 private function request(string $method,string $path,array $body=[],?string $idem=null):array{$ch=curl_init($this->base.$path);$h=['Authorization: Bearer '.$this->key,'Content-Type: application/json','Accept: application/json'];if($idem)$h[]='Idempotency-Key: '.$idem;curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$h,CURLOPT_POSTFIELDS=>$body?json_encode($body,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE):null,CURLOPT_TIMEOUT=>45]);$raw=curl_exec($ch);$err=curl_error($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);if($err)throw new RuntimeException($err);$data=json_decode((string)$raw,true);if($status<200||$status>=300)throw new RuntimeException(($data['message']??$data['error']??'Topup.dev error').' ['.$status.']');return is_array($data)?$data:[];}
 public function create(string $sku,array $player,string $callback,string $idempotency):array{return $this->request('POST','/api/v1/orders',['sku'=>$sku,'player'=>$player,'callback_url'=>$callback],$idempotency);}
}
