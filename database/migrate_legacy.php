<?php

declare(strict_types=1);

/**
 * One-time migration from the OLD database to the new schema.
 *
 * Run it from the project root:
 *
 *     php database/migrate_legacy.php --confirm
 *
 * What it does:
 *   1. renames the old tables    customer  -> legacy_customer
 *                                products  -> legacy_products
 *   2. creates the new tables from database/schema.sql
 *   3. copies the data across:
 *        - the old plain text passwords are hashed with password_hash()
 *        - product_name / product_price / product_image become name / price / image
 *        - "product_images/foo.jpg" becomes just "foo.jpg"
 *   4. leaves the legacy_* tables in place so nothing is lost.
 *
 * Afterwards move the image files:
 *     mv admin/product_images/* public/uploads/products/
 *
 * IMPORTANT: the old passwords were stored in plain text, so they are still
 * usable after the migration - but tell your users to change them, and give
 * one account the "admin" role:
 *
 *     UPDATE customers SET role = 'admin' WHERE email = 'you@example.com';
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Support\Env;

if (PHP_SAPI !== 'cli') {
    exit("This script may only be run from the command line.\n");
}

if (!in_array('--confirm', $argv, true)) {
    exit(
        "This will rename your existing tables and create the new schema.\n" .
        "Make a backup first:  mysqldump -u root -p hw > backup.sql\n" .
        "Then run:             php database/migrate_legacy.php --confirm\n"
    );
}

Env::load(__DIR__ . '/../.env');

$config = require __DIR__ . '/../config/database.php';

$pdo = new PDO(
    sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $config['host'], $config['port'], $config['name'], $config['charset']),
    $config['user'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

/** @return string[] */
$tables = static function (PDO $pdo): array {
    return $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
};

$existing = $tables($pdo);

echo "Step 1: renaming the legacy tables\n";

foreach (['customer' => 'legacy_customer', 'products' => 'legacy_products'] as $old => $new) {
    if (in_array($old, $existing, true) && !in_array($new, $existing, true)) {
        $pdo->exec("RENAME TABLE `{$old}` TO `{$new}`");
        echo "  {$old} -> {$new}\n";
    } else {
        echo "  {$old}: nothing to rename\n";
    }
}

echo "Step 2: creating the new schema\n";

$schema = file_get_contents(__DIR__ . '/schema.sql');

if ($schema === false) {
    exit("Could not read database/schema.sql\n");
}

$pdo->exec($schema);
echo "  customers, products, orders, order_items created\n";

$existing = $tables($pdo);

echo "Step 3: copying the data\n";

if (in_array('legacy_customer', $existing, true)) {
    $insert = $pdo->prepare(
        'INSERT INTO customers (name, email, password, role) VALUES (:name, :email, :password, :role)'
    );

    $copied = 0;

    foreach ($pdo->query('SELECT * FROM legacy_customer') as $row) {
        $email = trim((string) ($row['email'] ?? ''));

        if ($email === '') {
            continue;
        }

        $insert->execute([
            'name'     => trim((string) ($row['name'] ?? 'Customer')),
            'email'    => $email,
            // The old column held the password in plain text; hash it now.
            'password' => password_hash((string) ($row['password'] ?? ''), PASSWORD_DEFAULT),
            'role'     => 'customer',
        ]);

        $copied++;
    }

    echo "  {$copied} customer(s) copied, passwords hashed\n";
}

if (in_array('legacy_products', $existing, true)) {
    $insert = $pdo->prepare(
        'INSERT INTO products (name, price, stock, image) VALUES (:name, :price, :stock, :image)'
    );

    $copied = 0;

    foreach ($pdo->query('SELECT * FROM legacy_products') as $row) {
        // The old column stored "product_images/foo.jpg"; keep only "foo.jpg".
        $image = (string) ($row['product_image'] ?? '');
        $image = $image === '' ? null : basename($image);

        $insert->execute([
            'name'  => trim((string) ($row['product_name'] ?? 'Product')),
            'price' => (float) ($row['product_price'] ?? 0),
            'stock' => 10, // the old table had no stock column
            'image' => $image,
        ]);

        $copied++;
    }

    echo "  {$copied} product(s) copied\n";
}

echo "\nDone. Remaining manual steps:\n";
echo "  mv admin/product_images/* public/uploads/products/\n";
echo "  mysql> UPDATE customers SET role = 'admin' WHERE email = 'you@example.com';\n";
