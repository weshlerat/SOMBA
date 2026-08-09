<?php
declare(strict_types=1);
require_once __DIR__.'/../config/bootstrap.php';
if(session_status()!==PHP_SESSION_ACTIVE) session_start();
function admin_require(): void { if(empty($_SESSION['somba_admin'])) { header('Location: ./index.php'); exit; } }
function h(mixed $v): string { return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }
function csrf_token(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_check(): void { if(!hash_equals($_SESSION['csrf']??'',(string)($_POST['csrf']??''))) { http_response_code(419); exit('Invalid CSRF token'); } }
function redirect_self(): never { header('Location: '.$_SERVER['PHP_SELF']); exit; }
function flash(string $msg,string $type='ok'): void { $_SESSION['flash']=[$type,$msg]; }
function take_flash(): ?array { $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f; }
function admin_layout_start(string $title): void { $f=take_flash(); echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.h($title).' — SOMBA</title><link rel="stylesheet" href="assets/admin.css"></head><body><aside><h2>SOMBA</h2><a href="index.php">Dashboard</a><a href="games.php">Jeux</a><a href="products.php">Packs</a><a href="orders.php">Commandes</a><a href="users.php">Utilisateurs</a><a href="payments.php">Paiements</a><a href="media.php">Médias</a><a href="settings.php">Réglages</a><a href="audit.php">Journal</a><a href="?logout=1">Déconnexion</a></aside><main class="main"><h1>'.h($title).'</h1>'; if($f) echo '<div class="flash '.h($f[0]).'">'.h($f[1]).'</div>'; }
function admin_layout_end(): void { echo '</main></body></html>'; }
