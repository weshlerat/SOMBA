<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';
date_default_timezone_set($config['app']['timezone'] ?? 'Africa/Brazzaville');

$pdo = new PDO('mysql:host=' . $config['db']['host'] . ';port=' . $config['db']['port'] . ';dbname=' . $config['db']['name'] . ';charset=' . $config['db']['charset'], $config['db']['user'], $config['db']['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);

function config(string $key, mixed $default = null): mixed { global $config; $value = $config; foreach (explode('.', $key) as $part) { if (!is_array($value) || !array_key_exists($part, $value)) return $default; $value = $value[$part]; } return $value; }
function json_response(array $data, int $status = 200): never { http_response_code($status); header('Content-Type: application/json; charset=utf-8'); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit; }
function input_json(): array { $data = json_decode(file_get_contents('php://input') ?: '{}', true); return is_array($data) ? $data : []; }
function order_number(): string { return 'SMB-' . date('ymdHis') . '-' . strtoupper(bin2hex(random_bytes(3))); }
function http_json(string $method, string $url, array $headers = [], ?array $body = null): array { $ch = curl_init($url); $httpHeaders = ['Accept: application/json']; if ($body !== null) $httpHeaders[] = 'Content-Type: application/json'; foreach ($headers as $header) $httpHeaders[] = $header; curl_setopt_array($ch, [CURLOPT_CUSTOMREQUEST => strtoupper($method), CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_HTTPHEADER => $httpHeaders]); if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); $raw = curl_exec($ch); $error = curl_error($ch); $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch); if ($raw === false || $error !== '') throw new RuntimeException('HTTP request failed: ' . $error); $decoded = json_decode($raw, true); return ['status' => $status, 'body' => is_array($decoded) ? $decoded : [], 'raw' => $raw]; }
