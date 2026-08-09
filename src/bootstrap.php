<?php
declare(strict_types=1);
$config=require __DIR__.'/../config/config.php';
date_default_timezone_set($config['timezone']);
$pdo=new PDO('mysql:host='.$config['db']['host'].';port='.$config['db']['port'].';dbname='.$config['db']['name'].';charset='.$config['db']['charset'],$config['db']['user'],$config['db']['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
function json_response(array $data,int $status=200):never{http_response_code($status);header('Content-Type: application/json; charset=utf-8');echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
function input_json():array{$raw=file_get_contents('php://input');$d=json_decode($raw?:'{}',true);return is_array($d)?$d:[];}
function order_number():string{return 'SMB-'.date('ymdHis').'-'.strtoupper(bin2hex(random_bytes(3)));}
