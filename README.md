# Mini Shop — a plain PHP e-commerce application

A small online shop written in **plain PHP** (no framework), organised the way a
professional project is organised: a clear folder structure, one job per layer,
prepared statements everywhere and security handled in one place instead of
being copy-pasted across pages.

It is deliberately small enough to read in an afternoon.

---

## 1. What the application does

**For visitors and customers**

| Page | URL |
|---|---|
| Home page with featured products | `/` |
| Product catalogue | `/products` |
| Product details | `/products/{id}` |
| Register / Login / Logout | `/register`, `/login`, `/logout` |
| My account (and account deletion) | `/account` |
| Order history | `/orders` |
| Shopping cart | `/cart` |
| Checkout | `/checkout` |

**For administrators** (a user whose `role` is `admin`)

| Page | URL |
|---|---|
| Dashboard | `/admin` |
| Products (list / add / edit / delete) | `/admin/products` |
| Orders (list, details, change status) | `/admin/orders` |
| Customers | `/admin/customers` |

---

## 2. Technology

```
PHP 8.1+          language
MySQL 5.7+ / 8    database
PDO               database driver (with prepared statements)
Composer          PSR-4 autoloading, nothing else
Bootstrap 3       CSS framework (bundled in public/assets/vendor)
jQuery            only what Bootstrap needs
```

No framework, no ORM, no template engine, no dependency container library.

---

## 3. How to run it

```bash
# 1. install the autoloader (there are no external packages to download)
composer install
composer dump-autoload

# 2. create the database and import the tables
mysql -u root -p -e "CREATE DATABASE hw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p hw < database/schema.sql
mysql -u root -p hw < database/seed.sql          # optional demo data

# 3. configure your credentials
cp .env.example .env
#    then edit .env: DB_USER, DB_PASSWORD, ...

# 4. start the built-in web server
php -S localhost:8000 -t public public/index.php
```

Open <http://localhost:8000>.

Demo accounts from `database/seed.sql`:

```
admin@shop.test    / admin123        (administrator)
customer@shop.test / password123     (customer)
```

### Running under Apache

Point the virtual host **document root at `public/`**. `public/.htaccess`
already forwards every request to `index.php`. If the app lives in a sub folder
(`http://localhost/assessment/public`), set that prefix in `.env`:

```
APP_BASE_PATH=/assessment/public
```

### Coming from the old version of this project?

`database/migrate_legacy.php` converts the old `customer` / `products` tables to
the new schema and hashes the plain-text passwords:

```bash
mysqldump -u root -p hw > backup.sql        # always back up first
php database/migrate_legacy.php --confirm
mv admin/product_images/* public/uploads/products/
mysql -u root -p hw -e "UPDATE customers SET role='admin' WHERE email='you@example.com';"
```

---

## 4. Folder structure

```
assessment/
│
├── app/                     ← all PHP classes (never reachable from the web)
│   ├── Controllers/         ← receive a request, decide what happens next
│   │   └── Admin/           ← the admin panel controllers
│   ├── Services/            ← business logic ("what does 'place an order' mean?")
│   ├── Repositories/        ← every SQL query lives here
│   ├── Models/              ← one class per table row (typed data holders)
│   ├── Middleware/          ← access control that runs before a controller
│   ├── Validation/          ← server-side form validation
│   ├── Database/            ← the single PDO connection
│   ├── Helpers/             ← global functions: e(), view(), csrf_field(), ...
│   └── Support/             ← the small framework: Router, Request, Session, ...
│
├── views/                   ← HTML only
│   ├── layouts/             ← header / navbar / footer / flash message
│   ├── auth/  customer/  products/  cart/  checkout/
│   ├── admin/               ← dashboard, products, orders, customers
│   └── errors/              ← the 403 / 404 / 500 page
│
├── public/                  ← THE ONLY folder the web server exposes
│   ├── index.php            ← front controller: every request starts here
│   ├── .htaccess            ← pretty URLs
│   ├── assets/              ← css, js, images, bootstrap
│   └── uploads/products/    ← uploaded product images (PHP execution disabled)
│
├── routes/web.php           ← the URL map
├── config/                  ← app.php, database.php (values come from .env)
├── database/                ← schema.sql, seed.sql, migrate_legacy.php
├── storage/logs/            ← error log
├── bootstrap.php            ← wires everything together
└── .env                     ← credentials, never committed
```

**Why is `public/` separate?** Only that folder is reachable from the browser.
`.env`, `app/`, `vendor/` and `storage/` sit one level above it, so nobody can
download your database password by guessing a URL.

---

## 5. The layers

```
Request
   ↓
public/index.php        front controller — the single entry point
   ↓
Router                  which controller handles this URL?
   ↓
Middleware              may this visitor be here at all?
   ↓
Controller              read input, validate, call a service, choose a view
   ↓
Service                 the business rules
   ↓
Repository              the SQL
   ↓
PDO → MySQL
```

and back again:

```
MySQL → Repository → Model → Service → Controller → View → HTML
```

### What belongs where

| Layer | Does | Never does |
|---|---|---|
| **Controller** | reads the request, calls a validator, calls a service, redirects or renders a view | write SQL, hash passwords, move uploaded files |
| **Service** | business rules: "a cart line may not exceed the stock", "an order is one transaction" | write SQL, print HTML |
| **Repository** | prepared statements, returns Models | contain business rules |
| **Model** | holds one row as a typed object | talk to the database |
| **Middleware** | "must be logged in", "must be admin" | anything else |
| **Validation** | checks user input, returns error messages | save anything |
| **View** | HTML, small loops and conditions | SQL, business logic |

### The patterns used (and why)

* **Front controller** — one entry point (`public/index.php`) means session
  handling, error handling and routing are set up exactly once.
* **Repository pattern** — all SQL for a table sits in one class. When a query is
  slow or wrong, you know which file to open.
* **Service layer** — business rules live away from both HTML and SQL, so the
  same rule cannot drift apart in two pages.
* **Dependency injection** — a class receives what it needs through its
  constructor. `app/Support/Container.php` is the one place where objects are
  wired together; you can read it top to bottom.
* **Middleware** — access control is attached to a *route*, so protecting a page
  cannot be forgotten inside a controller method.

Patterns that were deliberately **not** used: interfaces for classes with a
single implementation, abstract factories, events, a DI library, an ORM. They
would add files without solving a problem this project has.

---

## 6. Database

```
customers ──1───┐
                │ N
              orders ──1─────┐
                             │ N
                        order_items ──N──1── products
```

### `customers`
| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK | |
| name | VARCHAR(100) | |
| email | VARCHAR(190) | **UNIQUE** |
| password | VARCHAR(255) | `password_hash()` output, never plain text |
| role | ENUM('customer','admin') | decides admin access |
| created_at / updated_at | TIMESTAMP | |

### `products`
| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK | |
| name | VARCHAR(150) | |
| description | TEXT NULL | |
| price | DECIMAL(10,2) | `DECIMAL`, not `FLOAT` — money must be exact |
| stock | INT UNSIGNED | |
| image | VARCHAR(255) NULL | only the file name, the folder comes from config |

### `orders`
| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK | |
| customer_id | FK → customers.id | `ON DELETE CASCADE` |
| total_amount | DECIMAL(10,2) | always recalculated on the server |
| status | ENUM(pending, paid, shipped, completed, cancelled) | |
| shipping_name / phone / address / city | | copied into the order on purpose |

### `order_items`
| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK | |
| order_id | FK → orders.id | `ON DELETE CASCADE` |
| product_id | FK → products.id, NULL-able | `ON DELETE SET NULL` |
| product_name, price | | **copied** from the product |
| quantity, subtotal | | |

> **Why copy the product name and price into `order_items`?**
> Because an invoice must stay correct. If the shop raises the price next month
> or deletes the product, the customer's old order still shows what they really
> bought and paid.

### Changes compared with the old database

| Old | New | Why |
|---|---|---|
| table `customer` | `customers` | plural table names, consistent everywhere |
| no `id` column | `id` primary key | rows need a stable identity |
| plain-text `password` | bcrypt hash | see the security section |
| — | `role` | admin access has to be stored somewhere |
| `products.product_name` | `products.name` | the table already says "product" |
| `products.product_price` | `products.price` | |
| `products.product_image` (`product_images/x.jpg`) | `products.image` (`x.jpg`) | the folder is configuration, not data |
| — | `stock` | needed for a real checkout |
| — | `orders`, `order_items` | orders did not exist before |

`database/migrate_legacy.php` performs this migration on an existing database.

---

## 7. Request lifecycle, step by step

Example: a visitor opens `/products/3`.

1. **Apache / the PHP server** sends the request to `public/index.php` — the only
   PHP file the browser can reach.
2. `bootstrap.php` runs: Composer autoloader → read `.env` → register the error
   handler → start the hardened session → create the container.
3. `routes/web.php` is loaded and returns the `Router`.
4. `Request::capture()` reads the method (`GET`) and the path (`/products/3`).
5. The router finds `GET /products/{id}` and runs the route middleware (none for
   this page).
6. The container builds `ProductController` and injects `ProductService`, which
   itself received `ProductRepository` and `FileUploadService`.
7. `ProductController::show()` checks that `3` really is a number, then asks the
   service for the product.
8. `ProductService` asks `ProductRepository`, which runs a prepared statement and
   turns the row into a `Product` model.
9. The controller calls `view('products/show', ['product' => $product])`.
10. The template is rendered, wrapped in `views/layouts/app.php`, and every value
    is printed through `e()`.

---

## 8. Authentication flow

**Registration** — `POST /register`

```
CSRF token checked
   ↓
AuthValidator: name, valid email, unique email, password ≥ 8, passwords match
   ↓
AuthService::register()
   ↓
password_hash($password, PASSWORD_DEFAULT)      ← salted bcrypt
   ↓
CustomerRepository::create()  (prepared statement)
   ↓
session_regenerate_id(true) and the user is stored in the session
```

**Login** — `POST /login`

```
CSRF token checked
   ↓
AuthValidator: email + password present
   ↓
AuthService::attempt()
   ↓
CustomerRepository::findByEmail()      ← ONE query
   ↓
password_verify($password, $customer->passwordHash)
   ↓
session_regenerate_id(true) → $_SESSION['user'] = id, name, email, role
```

> The old code ran **two separate queries** — one for the email and one for the
> password — so the password of *any* registered user let you log in as *any*
> other user. Loading the customer once and verifying against that customer's
> own hash is what fixes it.

The session stores the **user id** plus a small snapshot (name, email, role).
It never stores the password hash.

**Logout** — `POST /logout` (a form with a CSRF token, not a link): the session
is destroyed and the session cookie is cleared.

---

## 9. How admin protection works

```php
// routes/web.php
$router->get('/admin/products', [AdminProductController::class, 'index'], [AdminMiddleware::class]);
```

`AdminMiddleware::handle()` runs **before** the controller is even created:

1. `AuthMiddleware` first — not logged in → redirect to `/login`
   (the page you wanted is remembered and you land there after logging in).
2. `is_admin()` reads `role` from the session — not an admin → **403 page**.

Because the check lives on the route, a new admin page is protected by adding
one array element. There is no way to "forget the if-statement" in a controller.

A customer who types `/admin/products` sees a 403 page, and a POST to
`/admin/products/5/delete` never reaches the controller.

---

## 10. Product lifecycle (CRUD)

```
/admin/products/create   →  Admin\ProductController::create()   → the form
POST /admin/products     →  ::store()   → validate → ProductService::create()
/admin/products/{id}/edit→  ::edit()    → the form, pre-filled
POST /admin/products/{id}→  ::update()  → validate → ProductService::update()
POST /admin/products/{id}/delete → ::destroy() → ProductService::delete()
```

**Creating a product**

1. CSRF token verified.
2. `ProductValidator` checks name (2–150 chars), price (positive, max 2 decimals),
   stock (whole number), description length.
3. `FileUploadService::validate()` checks the image: upload error, size ≤ 2 MB,
   **real** mime type read from the file content, and `getimagesize()`.
4. Errors → back to the form with the messages and everything the user typed.
5. `FileUploadService::store()` moves the file under a **random** name
   (`bin2hex(random_bytes(16)).jpg`); the browser's file name is never used.
6. `ProductRepository::create()` inserts with a prepared statement.

**Updating** keeps the current image when no new file is sent. When a new image
is uploaded, the new file is stored *first* and only then is the old one deleted.

**Deleting** is a `POST` form with a CSRF token — never a link like the old
`product.php?status=delete&id=1`. A link is followed by crawlers, by browsers
that prefetch, and by any other website that embeds it.

---

## 11. Cart lifecycle

The cart lives in the session and holds **only ids and quantities**:

```php
$_SESSION['cart'] = [
    12 => 2,   // product 12, quantity 2
    15 => 1,
];
```

`CartService` is the only class that touches it:

| Method | What it does |
|---|---|
| `add()` | checks the product exists and there is enough stock |
| `updateQuantity()` | 0 or less removes the line |
| `remove()` / `clear()` | |
| `items()` | loads the products **from the database** and returns `CartItem[]` |
| `subtotal()` / `count()` | |

Names and prices are always read fresh from the database. A price in a hidden
form field would be ignored — that is the whole point.

Guests may fill a cart; `/checkout` requires a login (`AuthMiddleware`), and the
cart survives the login because it is stored per session.

---

## 12. Checkout lifecycle

`POST /checkout` → `CheckoutController::store()` → `CheckoutService::placeOrder()`

```
1. CSRF token verified
2. CheckoutValidator: name, phone, address, city
3. BEGIN TRANSACTION
4. SELECT ... FOR UPDATE  → the product rows are locked
5. for every cart line:
      does the product still exist?
      is there enough stock?
      subtotal = price FROM THE DATABASE × quantity
6. INSERT INTO orders        (total calculated in step 5)
7. INSERT INTO order_items   (product name + price copied)
8. UPDATE products SET stock = stock - qty WHERE id = ? AND stock >= ?
9. COMMIT      ← or ROLLBACK if anything above failed
10. the cart is emptied only after the commit
```

**Why a transaction?** Without it a crash between step 6 and 7 leaves an order
with no items, or stock that was reduced for an order that was never stored.
With it, either everything happened or nothing did.

**Why `FOR UPDATE` and `AND stock >= ?`?** Two customers can click "place order"
for the last item at the same moment. The lock and the conditional update make
sure only one of them succeeds.

---

## 13. Security: what was wrong, and what replaced it

| Old problem | New solution |
|---|---|
| Passwords stored in plain text | `password_hash()` on registration, `password_verify()` on login |
| Login accepted any user's password | one query per customer, verified against that customer's own hash |
| SQL injection in every query (`"... WHERE email = '$email'"`) | PDO prepared statements with bound parameters, `ATTR_EMULATE_PREPARES = false` |
| `mysqli_connect()` hardcoded in every class | one `Database` class, credentials from `.env` |
| No CSRF protection | `csrf_field()` in every POST form, `require_csrf()` in every POST controller |
| Output printed raw (XSS) | `e()` — `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` — on every value in every view |
| No server-side validation | `AuthValidator`, `ProductValidator`, `CheckoutValidator` |
| Upload trusted `accept="image/*"` and the original file name | real mime type via `finfo`, `getimagesize()`, 2 MB limit, random file name, `.htaccess` disables PHP in the upload folder |
| Destructive actions through GET links (`?deleteCustomer=true`) | POST forms with a CSRF token |
| `header('Location: ...')` without `exit` — the protected page rendered anyway | `redirect()` always calls `exit` |
| Admin panel had no login at all | `AdminMiddleware` on every admin route + a `role` column |
| No session hardening | `HttpOnly`, `SameSite=Lax`, `Secure` over HTTPS, `session_regenerate_id(true)` after login |
| SQL errors printed to the screen (`die(mysqli_error())`) | `ErrorHandler`: friendly page for visitors, full detail in `storage/logs/` |
| Everything reachable from the web root | only `public/` is exposed; `.env`, `app/`, `vendor/` are outside it |
| Price could be sent from the browser | prices are always read from the database at checkout |
| `.env` did not exist, credentials were in the code | `.env` (git-ignored) + `.env.example` |

### Security checklist

**Authentication** — password hashing ✔ · verification ✔ · `session_regenerate_id()` ✔ · secure logout ✔ · `AuthMiddleware` ✔
**Authorization** — admin login ✔ · `AdminMiddleware` ✔ · a customer cannot reach admin actions ✔
**Database** — PDO ✔ · prepared statements ✔ · no string concatenation ✔ · checkout transaction ✔
**Input** — POST validated ✔ · route parameters validated ✔ · uploads validated ✔ · all server-side ✔
**Output** — `e()` everywhere ✔
**CSRF** — token generated ✔ · verified on every state change ✔
**Uploads** — mime ✔ · size ✔ · random name ✔ · PHP execution disabled ✔
**Session** — regeneration ✔ · `HttpOnly` / `SameSite` / `Secure` ✔ · no sensitive data stored ✔

---

## 14. Error handling

`.env` decides what a visitor sees:

```
APP_ENV=development   → the real message and the stack trace on screen
APP_ENV=production    → "Something went wrong on our side."
```

Either way the full detail is written to `storage/logs/app-YYYY-MM-DD.log`.
PHP warnings and notices are turned into exceptions, so nothing is silently
ignored. Expected, user-facing problems ("Your cart is empty") use
`App\Support\DomainException` and are shown as a normal flash message.

---

## 15. If you are new here: where do I put my code?

**"I want to add a new page"**

```
1. routes/web.php        add the route (+ middleware if it must be protected)
2. app/Controllers/      add the method
3. views/                add the template
```

**"I want to add a feature to products"** (say, a discount price)

```
1. database/schema.sql       add the column
2. app/Models/Product.php    add the property in the constructor + fromArray()
3. app/Repositories/…        add/extend the query
4. app/Services/…            add the rule (when does a discount apply?)
5. app/Validation/…          validate the new input
6. app/Controllers/…         pass it through
7. views/…                   show it (through e()!)
```

**"Where do I write SQL?"** Only in `app/Repositories/`.

**"Where do I check whether something is allowed?"** In `app/Middleware/` for a
whole page, in the service for a business rule.

**"My form does not work"** — it is almost always one of these:
* missing `<?= csrf_field() ?>` in the form,
* missing `enctype="multipart/form-data"` on a form with a file input,
* the route is registered as `get` but the form uses `method="post"`.

**Two rules that are never worth breaking**

1. Every value printed in a view goes through `e()`.
2. Every value that reaches a query is a bound parameter, never string
   concatenation.
