<?php

// Test against the installed MySQL engine using schema-only copies and a restricted disposable account.
// No application records are copied, changed, migrated, or deleted.
$project = dirname(__DIR__);
require $project.'/vendor/autoload.php';
$app = require $project.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$credentialsPath = getenv('HELPDESK_MYSQL_ADMIN_CREDENTIALS') ?: getenv('LOCALAPPDATA').'/PMMA-Support-Local/credentials.json';
$credentials = json_decode(file_get_contents($credentialsPath), true, 512, JSON_THROW_ON_ERROR);
$source = Illuminate\Support\Facades\DB::connection()->getPdo();
$schema = [];
foreach ($source->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
    if (! preg_match('/^[a-z_]+$/', $table)) { throw new RuntimeException('Unexpected schema table.'); }
    $schema[] = $source->query('SHOW CREATE TABLE `'.$table.'`')->fetch(PDO::FETCH_NUM)[1];
}
$identifier = bin2hex(random_bytes(6));
$database = 'pmma_helpdesk_test_'.$identifier;
$user = 'helpdesk_test_'.$identifier;
$password = bin2hex(random_bytes(24));
if (! preg_match('/^pmma_helpdesk_test_[a-f0-9]{12}$/', $database)) { throw new RuntimeException('Unsafe test name.'); }
$root = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', $credentials['mysql_root_password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$schemaFile = tempnam(sys_get_temp_dir(), 'pmma-test-schema-');
file_put_contents($schemaFile, json_encode($schema, JSON_THROW_ON_ERROR));
$databaseCreated = false;
$userCreated = false;
$exitCode = 1;
try {
    $root->exec('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $databaseCreated = true;
    $account = $root->quote($user)."@'127.0.0.1'";
    $root->exec('CREATE USER '.$account.' IDENTIFIED BY '.$root->quote($password));
    $userCreated = true;
    $root->exec('GRANT ALL PRIVILEGES ON `'.$database.'`.* TO '.$account);
    $environment = array_merge(getenv(), [
        'APP_ENV' => 'testing', 'MAIL_MAILER' => 'array',
        'HELPDESK_TEST_DATABASE' => $database, 'HELPDESK_TEST_USER' => $user,
        'HELPDESK_TEST_PASSWORD' => $password, 'HELPDESK_TEST_SCHEMA' => $schemaFile,
    ]);
    echo 'Testing MySQL '. $root->query('SELECT VERSION()')->fetchColumn().' with a schema-only disposable database.'.PHP_EOL;
    $process = proc_open([PHP_BINARY, $project.'/vendor/bin/phpunit', $project.'/tests/Feature/HelpdeskPresentationTest.php'],
        [0 => ['pipe', 'r'], 1 => STDOUT, 2 => STDERR], $pipes, $project, $environment, ['bypass_shell' => true]);
    if (! is_resource($process)) { throw new RuntimeException('Unable to start test process.'); }
    fclose($pipes[0]);
    $exitCode = proc_close($process);
} finally {
    // Both names are generated above and are unrelated to the configured application database/account.
    if ($databaseCreated) { $root->exec('DROP DATABASE `'.$database.'`'); }
    if ($userCreated) { $root->exec('DROP USER '.$root->quote($user)."@'127.0.0.1'"); }
    if (is_file($schemaFile)) { unlink($schemaFile); }
    echo 'Disposable test database and account removed. Application database was not modified.'.PHP_EOL;
}
exit($exitCode);
