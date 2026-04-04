/**
 * Backend Validation Script
 * Validates the Laravel backend project structure, PHP file consistency,
 * route-controller mapping, model-migration alignment, and config correctness.
 * Run: node validate-backend.js
 */
const fs = require('fs');
const path = require('path');

const BACKEND = path.join(__dirname, 'backend');
let passed = 0;
let failed = 0;
const errors = [];

function assert(condition, message) {
  if (condition) {
    passed++;
    console.log(`  ✓ ${message}`);
  } else {
    failed++;
    errors.push(message);
    console.log(`  ✗ FAIL: ${message}`);
  }
}

function fileExists(relPath) {
  return fs.existsSync(path.join(BACKEND, relPath));
}

function readFile(relPath) {
  return fs.readFileSync(path.join(BACKEND, relPath), 'utf-8');
}

function fileContains(relPath, ...strings) {
  const content = readFile(relPath);
  return strings.every(s => content.includes(s));
}

// ─── 1. Required Files ───────────────────────────────────────────
console.log('\n═══ 1. Required Files ═══');

const requiredFiles = [
  'composer.json',
  'artisan',
  'bootstrap/app.php',
  'bootstrap/providers.php',
  'public/index.php',
  'config/app.php',
  'config/database.php',
  'config/cors.php',
  'config/logging.php',
  'config/cache.php',
  'config/session.php',
  'routes/api.php',
  '.env.example',
  'Dockerfile',
  'docker/entrypoint.sh',
  'docker/nginx.conf',
  'docker/supervisord.conf',
  'app/Models/User.php',
  'app/Models/Wallet.php',
  'app/Models/Transaction.php',
  'app/Http/Controllers/Controller.php',
  'app/Http/Controllers/UserController.php',
  'app/Http/Controllers/WalletController.php',
  'app/Http/Controllers/TransferController.php',
  'app/Http/Controllers/TransactionController.php',
  'app/Http/Requests/CreateUserRequest.php',
  'app/Http/Requests/CreateWalletRequest.php',
  'app/Http/Requests/TransferRequest.php',
  'app/Services/UserService.php',
  'app/Services/WalletService.php',
  'app/Services/TransferService.php',
  'app/Services/TransactionService.php',
  'app/Exceptions/ResourceNotFoundException.php',
  'app/Exceptions/DuplicateResourceException.php',
  'app/Exceptions/InsufficientBalanceException.php',
  'app/Exceptions/InvalidTransferException.php',
  'app/Exceptions/IdempotencyConflictException.php',
  'app/Providers/AppServiceProvider.php',
  'database/seeders/DatabaseSeeder.php',
];

for (const f of requiredFiles) {
  assert(fileExists(f), `File exists: ${f}`);
}

// Migrations
const migDir = path.join(BACKEND, 'database/migrations');
const migrations = fs.readdirSync(migDir).filter(f => f.endsWith('.php'));
assert(migrations.length === 3, `3 migration files found (got ${migrations.length})`);
assert(migrations.some(f => f.includes('create_users_table')), 'Migration: create_users_table');
assert(migrations.some(f => f.includes('create_wallets_table')), 'Migration: create_wallets_table');
assert(migrations.some(f => f.includes('create_transactions_table')), 'Migration: create_transactions_table');

// ─── 2. Composer.json ────────────────────────────────────────────
console.log('\n═══ 2. Composer.json ═══');
const composer = JSON.parse(readFile('composer.json'));
assert(composer.require['php'] !== undefined, 'PHP version constraint defined');
assert(composer.require['laravel/framework'] !== undefined, 'Laravel framework dependency');
assert(composer.autoload?.['psr-4']?.['App\\'] === 'app/', 'PSR-4 autoload: App\\ -> app/');
assert(composer.autoload?.['psr-4']?.['Database\\Seeders\\'] === 'database/seeders/', 'PSR-4 autoload: Database\\Seeders\\');

// ─── 3. PHP Namespace/Class Consistency ──────────────────────────
console.log('\n═══ 3. PHP Namespace & Class Checks ═══');

const nsChecks = [
  ['app/Models/User.php', 'namespace App\\Models;', 'class User'],
  ['app/Models/Wallet.php', 'namespace App\\Models;', 'class Wallet'],
  ['app/Models/Transaction.php', 'namespace App\\Models;', 'class Transaction'],
  ['app/Http/Controllers/UserController.php', 'namespace App\\Http\\Controllers;', 'class UserController'],
  ['app/Http/Controllers/WalletController.php', 'namespace App\\Http\\Controllers;', 'class WalletController'],
  ['app/Http/Controllers/TransferController.php', 'namespace App\\Http\\Controllers;', 'class TransferController'],
  ['app/Http/Controllers/TransactionController.php', 'namespace App\\Http\\Controllers;', 'class TransactionController'],
  ['app/Services/UserService.php', 'namespace App\\Services;', 'class UserService'],
  ['app/Services/WalletService.php', 'namespace App\\Services;', 'class WalletService'],
  ['app/Services/TransferService.php', 'namespace App\\Services;', 'class TransferService'],
  ['app/Services/TransactionService.php', 'namespace App\\Services;', 'class TransactionService'],
  ['app/Http/Requests/CreateUserRequest.php', 'namespace App\\Http\\Requests;', 'class CreateUserRequest'],
  ['app/Http/Requests/CreateWalletRequest.php', 'namespace App\\Http\\Requests;', 'class CreateWalletRequest'],
  ['app/Http/Requests/TransferRequest.php', 'namespace App\\Http\\Requests;', 'class TransferRequest'],
  ['app/Exceptions/ResourceNotFoundException.php', 'namespace App\\Exceptions;', 'class ResourceNotFoundException'],
  ['app/Exceptions/DuplicateResourceException.php', 'namespace App\\Exceptions;', 'class DuplicateResourceException'],
  ['app/Exceptions/InsufficientBalanceException.php', 'namespace App\\Exceptions;', 'class InsufficientBalanceException'],
  ['app/Exceptions/InvalidTransferException.php', 'namespace App\\Exceptions;', 'class InvalidTransferException'],
  ['app/Exceptions/IdempotencyConflictException.php', 'namespace App\\Exceptions;', 'class IdempotencyConflictException'],
  ['app/Providers/AppServiceProvider.php', 'namespace App\\Providers;', 'class AppServiceProvider'],
];

for (const [file, ns, cls] of nsChecks) {
  assert(fileContains(file, ns, cls), `${file}: namespace + class correct`);
}

// ─── 4. Route Definitions ────────────────────────────────────────
console.log('\n═══ 4. API Routes ═══');
const routes = readFile('routes/api.php');

const expectedRoutes = [
  ["Route::get('/users'", 'UserController', 'index'],
  ["Route::post('/users'", 'UserController', 'store'],
  ["Route::get('/users/{id}'", 'UserController', 'show'],
  ["Route::post('/wallets'", 'WalletController', 'store'],
  ["Route::get('/wallets/{id}'", 'WalletController', 'show'],
  ["Route::post('/wallets/{id}/deposit'", 'WalletController', 'deposit'],
  ["Route::post('/transfer'", 'TransferController', 'store'],
  ["Route::get('/transactions'", 'TransactionController', 'index'],
];

for (const [routeDef, controller, method] of expectedRoutes) {
  assert(
    routes.includes(routeDef) && routes.includes(controller) && routes.includes(method),
    `Route: ${routeDef} -> ${controller}@${method}`
  );
}

// ─── 5. Controller-Service Wiring ────────────────────────────────
console.log('\n═══ 5. Controller-Service Wiring ═══');

assert(fileContains('app/Http/Controllers/UserController.php', 'UserService'), 'UserController uses UserService');
assert(fileContains('app/Http/Controllers/WalletController.php', 'WalletService'), 'WalletController uses WalletService');
assert(fileContains('app/Http/Controllers/TransferController.php', 'TransferService'), 'TransferController uses TransferService');
assert(fileContains('app/Http/Controllers/TransactionController.php', 'TransactionService'), 'TransactionController uses TransactionService');

// ─── 6. Model Features ──────────────────────────────────────────
console.log('\n═══ 6. Models ═══');
assert(fileContains('app/Models/User.php', "'email'", "'full_name'", '$fillable'), 'User model: fillable fields');
assert(fileContains('app/Models/User.php', 'wallet'), 'User model: wallet relationship');
assert(fileContains('app/Models/Wallet.php', "'user_id'", "'balance'", '$fillable'), 'Wallet model: fillable fields');
assert(fileContains('app/Models/Wallet.php', 'user'), 'Wallet model: user relationship');
assert(fileContains('app/Models/Transaction.php', "'from_wallet_id'", "'to_wallet_id'", "'amount'", "'status'"), 'Transaction model: fillable fields');

// ─── 7. Migration Schema ────────────────────────────────────────
console.log('\n═══ 7. Migration Schema ═══');
const usersMig = readFile(path.join('database/migrations', migrations.find(f => f.includes('users'))));
assert(usersMig.includes("'email'") && usersMig.includes('unique'), 'Users migration: unique email');
assert(usersMig.includes("'full_name'"), 'Users migration: full_name column');

const walletsMig = readFile(path.join('database/migrations', migrations.find(f => f.includes('wallets'))));
assert(walletsMig.includes("'user_id'") && walletsMig.includes('unique'), 'Wallets migration: unique user_id FK');
assert(walletsMig.includes("'balance'") && walletsMig.includes('decimal'), 'Wallets migration: decimal balance');
assert(walletsMig.includes("'version'"), 'Wallets migration: version column');

const txMig = readFile(path.join('database/migrations', migrations.find(f => f.includes('transactions'))));
assert(txMig.includes("'from_wallet_id'") && txMig.includes('nullable'), 'Transactions migration: nullable from_wallet_id');
assert(txMig.includes("'to_wallet_id'"), 'Transactions migration: to_wallet_id');
assert(txMig.includes("'amount'") && txMig.includes('decimal'), 'Transactions migration: decimal amount');
assert(txMig.includes("'status'"), 'Transactions migration: status column');
assert(txMig.includes("'idempotency_key'") && txMig.includes('unique'), 'Transactions migration: unique idempotency_key');

// ─── 8. Service Business Logic ───────────────────────────────────
console.log('\n═══ 8. Service Business Logic ═══');
assert(fileContains('app/Services/UserService.php', 'DB::transaction', 'DuplicateResourceException'), 'UserService: transaction + duplicate check');
assert(fileContains('app/Services/WalletService.php', 'lockForUpdate', 'deposit'), 'WalletService: pessimistic locking on deposit');
assert(fileContains('app/Services/TransferService.php', 'lockForUpdate', 'deadlock'), 'TransferService: pessimistic locking + deadlock prevention');
assert(fileContains('app/Services/TransferService.php', 'idempotency', 'IdempotencyConflictException'), 'TransferService: idempotency handling');
assert(fileContains('app/Services/TransferService.php', 'bcsub', 'bcadd'), 'TransferService: bcmath for precision');
assert(fileContains('app/Services/TransferService.php', 'FAILED', 'recordFailedTransaction'), 'TransferService: failed transaction recording');
assert(fileContains('app/Services/TransactionService.php', 'orderBy', 'limit'), 'TransactionService: ordered + limited query');

// ─── 9. Exception Handling ───────────────────────────────────────
console.log('\n═══ 9. Exception Handling (bootstrap/app.php) ═══');
const bootstrap = readFile('bootstrap/app.php');
assert(bootstrap.includes('ResourceNotFoundException'), 'Exception handler: ResourceNotFoundException');
assert(bootstrap.includes('InsufficientBalanceException'), 'Exception handler: InsufficientBalanceException');
assert(bootstrap.includes('DuplicateResourceException'), 'Exception handler: DuplicateResourceException');
assert(bootstrap.includes('InvalidTransferException'), 'Exception handler: InvalidTransferException');
assert(bootstrap.includes('IdempotencyConflictException'), 'Exception handler: IdempotencyConflictException');
assert(bootstrap.includes('ValidationException'), 'Exception handler: ValidationException');
assert(bootstrap.includes('InvalidArgumentException'), 'Exception handler: InvalidArgumentException');
assert(bootstrap.includes('renderable'), 'Exception handler: uses renderable()');

// ─── 10. Laravel 11 Compatibility ────────────────────────────────
console.log('\n═══ 10. Laravel 11 Compatibility ═══');
assert(readFile('public/index.php').includes('handleRequest'), 'public/index.php: uses handleRequest (L11)');
assert(readFile('artisan').includes('handleCommand'), 'artisan: uses handleCommand (L11)');
assert(bootstrap.includes('Application::configure'), 'bootstrap/app.php: uses Application::configure (L11)');

// ─── 11. Docker / Infrastructure ─────────────────────────────────
console.log('\n═══ 11. Docker & Infrastructure ═══');
assert(fileContains('Dockerfile', 'php:8.3-fpm', 'pdo_mysql', 'bcmath', 'nginx', 'supervisor'), 'Dockerfile: PHP 8.3 + extensions + nginx + supervisor');
assert(fileContains('docker/entrypoint.sh', 'migrate --force', 'supervisord'), 'Entrypoint: migrations + supervisor start');
assert(fileContains('docker/nginx.conf', '8080', 'fastcgi_pass'), 'Nginx: port 8080 + PHP-FPM');
assert(fileContains('docker/supervisord.conf', 'php-fpm', 'nginx'), 'Supervisor: manages php-fpm + nginx');

// Docker Compose (one level up)
const composePath = path.join(__dirname, 'docker-compose.yml');
const compose = fs.readFileSync(composePath, 'utf-8');
assert(compose.includes('mysql:8.0'), 'docker-compose: MySQL 8.0');
assert(compose.includes('8080:8080'), 'docker-compose: backend port 8080');
assert(compose.includes('3000:80'), 'docker-compose: frontend port 3000');
assert(compose.includes('service_healthy'), 'docker-compose: depends_on healthcheck');
assert(compose.includes('DB_HOST: mysql'), 'docker-compose: DB_HOST env');

// ─── 12. Config Files ────────────────────────────────────────────
console.log('\n═══ 12. Config Files ═══');
assert(fileContains('config/database.php', 'mysql', 'DB_HOST', 'DB_DATABASE'), 'database.php: MySQL config with env vars');
assert(fileContains('config/cors.php', 'allowed_origins', 'allowed_methods'), 'cors.php: CORS configuration');
assert(fileContains('config/app.php', 'APP_KEY', 'APP_ENV'), 'app.php: app config');

// ─── 13. API Response Format Consistency ─────────────────────────
console.log('\n═══ 13. API Response Format ═══');
// UserService response fields
assert(fileContains('app/Services/UserService.php', "'id'", "'email'", "'fullName'", "'walletId'", "'createdAt'"), 'UserService: camelCase response keys');
// WalletService
assert(fileContains('app/Services/WalletService.php', "'id'", "'userId'", "'balance'"), 'WalletService: camelCase response keys');
// TransferService
assert(fileContains('app/Services/TransferService.php', "'transactionId'", "'fromWalletId'", "'toWalletId'", "'amount'", "'status'"), 'TransferService: camelCase response keys');
// TransactionService
assert(fileContains('app/Services/TransactionService.php', "'transactionId'", "'fromWalletId'", "'toWalletId'"), 'TransactionService: camelCase response keys');

// ─── Summary ─────────────────────────────────────────────────────
console.log('\n' + '═'.repeat(50));
console.log(`RESULTS: ${passed} passed, ${failed} failed out of ${passed + failed} checks`);
if (failed > 0) {
  console.log('\nFailed checks:');
  errors.forEach(e => console.log(`  ✗ ${e}`));
  process.exit(1);
} else {
  console.log('\n✓ All checks passed! Backend is structurally sound.');
  process.exit(0);
}
