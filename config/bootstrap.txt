<?php
declare(strict_types=1);
$local=__DIR__.'/config.local.php';
$base=require __DIR__.'/config.local.php';
if(is_file($local)){$base=array_replace_recursive($base,require $local);}
date_default_timezone_set($base['app']['timezone']??'Africa/Brazzaville');
function config(string $key,mixed $default=null):mixed{global $base;$v=$base;foreach(explode('.',$key) as $p){if(!is_array($v)||!array_key_exists($p,$v))return $default;$v=$v[$p];}return $v;}
function db():PDO{static $pdo;if($pdo)return $pdo;$c=config('db');$pdo=new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset={$c['charset']}",$c['user'],$c['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);return $pdo;}
function json_response(array $data,int $status=200):never{http_response_code($status);header('Content-Type: application/json; charset=utf-8');echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
