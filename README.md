# SOMBA

Gaming top-up platform built for standard PHP hosting.

## Stack
- PHP 8.3+
- MySQL/MariaDB
- PDO
- HTML5/CSS3/Vanilla JS

## Planned flow
Customer selects a product -> SOMBA creates an order -> customer is redirected to the configured Maketou payment cart -> Maketou confirmation updates payment status -> Topup.dev delivery is triggered once -> callback updates delivery status.

API credentials and provider SKUs are intentionally kept out of Git. Configure them through environment variables or the server config.

## Install
1. Import `database/somba.sql`.
2. Copy `config/config.example.php` to `config/config.php` and fill database/API settings.
3. Point the web root to `public/` if your host supports it; otherwise keep server rules blocking config/database access.
4. Configure Maketou and Topup.dev webhook URLs after deployment.
