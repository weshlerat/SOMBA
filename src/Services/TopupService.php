<?php
declare(strict_types=1);
final class TopupService{
 public function __construct(private array $config){}
 public function createOrder(string $sku,array $customer,string $idempotencyKey):array{
  $payload=['sku'=>$sku,'customer'=>$customer,'idempotency_key'=>$idempotencyKey];
  return $this->request('POST','/api/v1/orders',$payload,['Idempotency-Key: '.$idempotencyKey]);
 }
 private function request(string $method,string $path,array $payload,array $headers=[]):array{
  $ch=curl_init('https://api.topup.dev'.$path);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>array_merge(['Authorization: Bearer '.$this->config['api_key'],'Content-Type: application/json','Accept: application/json'],$headers),CURLOPT_POSTFIELDS=>json_encode($payload)]);$body=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$data=json_decode((string)$body,true);return ['status'=>$code,'data'=>is_array($data)?$data:['raw'=>$body]];
 }
}
