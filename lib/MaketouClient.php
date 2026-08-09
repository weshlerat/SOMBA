<?php
declare(strict_types=1);
final class MaketouClient {
 private string $key; private string $base='https://api.maketou.net';
 public function __construct(string $key){$this->key=$key;}
 private function request(string $method,string $path,?array $body=null):array{$ch=curl_init($this->base.$path);$headers=['Authorization: Bearer '.$this->key,'Accept: application/json','Content-Type: application/json'];curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$headers,CURLOPT_TIMEOUT=>30]);if($body!==null)curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($body,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));$raw=curl_exec($ch);$err=curl_error($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);if($err)throw new RuntimeException($err);$data=json_decode((string)$raw,true);if($status<200||$status>=300)throw new RuntimeException(($data['message']??$data['code']??'Maketou error').' ['.$status.']');return is_array($data)?$data:[];}
 public function checkout(string $productId,string $email,string $firstName,string $lastName,?string $phone,string $redirect,array $meta):array{return $this->request('POST','/api/v1/stores/cart/checkout',['productDocumentId'=>$productId,'email'=>$email,'firstName'=>$firstName,'lastName'=>$lastName,'phone'=>$phone,'redirectURL'=>$redirect,'meta'=>$meta]);}
 public function cart(string $id):array{return $this->request('GET','/api/v1/stores/cart/'.rawurlencode($id));}
}
