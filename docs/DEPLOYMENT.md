# Deploying to Namecheap shared hosting

Target: **bankofyrmaps.com** on a Namecheap cPanel plan (Stellar / Stellar Plus
/ Stellar Business). No build step — deployment is copying files and running one
migration command.

Throughout, `youruser` is your cPanel username. Your home directory is
`/home/youruser`.

---

## 1. Set the PHP version

cPanel → **Select PHP Version** → choose **PHP 8.1 or newer** (8.2+ preferred).

Make sure these extensions are ticked:

- `pdo_mysql` — required
- `mbstring` — required
- `gd` — required for image handling
- `fileinfo` — required for upload validation
- `zip` — needed if you publish zipped map packs

## 2. Create the database

cPanel → **MySQL® Databases**:

1. Create a database, e.g. `youruser_bankofyrmaps`
2. Create a user, e.g. `youruser_byrm`, with a long generated password
3. Add the user to the database with **ALL PRIVILEGES**
4. Write down all three values — they go into `config/config.php`

## 3. Get the code onto the server

The application must sit **outside** the web root, with only `public_html/`
served. Pick whichever of these your plan supports.

### Option A — cPanel Git Version Control (preferred)

cPanel → **Git™ Version Control** → Create:

- Clone URL: your repository URL
- Repository Path: `/home/youruser/bankofyrmaps`

Then point the domain at the app's public directory:

cPanel → **Domains** → bankofyrmaps.com → **Document Root** →
`/home/youruser/bankofyrmaps/public_html`

Deploying an update afterwards is **Manage → Update from Remote** in that same
screen, or `git pull` from Terminal.

### Option B — symlink, if the document root cannot be changed

Some plans lock the primary domain's document root to `~/public_html`. In that
case, replace it with a link:

```bash
cd /home/youruser
git clone <your-repo-url> bankofyrmaps
mv public_html public_html.backup
ln -s /home/youruser/bankofyrmaps/public_html public_html
```

Keep `public_html.backup` until the site is confirmed working, then delete it.

### Option C — FTP / File Manager, no git

Upload so the structure looks like this:

```
/home/youruser/app/          <- from the repo
/home/youruser/config/
/home/youruser/database/
/home/youruser/storage/
/home/youruser/public_html/  <- CONTENTS of the repo's public_html/
```

The front controller resolves paths relative to itself, so this works — but you
will be re-uploading by hand on every change. Prefer A or B.

## 4. Create the configuration file

Copy `config/config.example.php` to `config/config.php` and fill in:

```php
'app' => [
    'url'   => 'https://bankofyrmaps.com',
    'env'   => 'production',
    'debug' => false,          // MUST stay false in production
],
'db' => [
    'driver'   => 'mysql',
    'host'     => 'localhost',
    'database' => 'youruser_bankofyrmaps',
    'username' => 'youruser_byrm',
    'password' => 'the password you generated',
],
'security' => [
    'app_key' => '<64 random hex characters>',
],
```

Generate the app key:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

`config/config.php` is git-ignored, so it never leaves the server. **Do not skip
the app key** — it salts the hashed visitor IPs used for download counting.

## 5. Create the tables

### With Terminal or SSH

```bash
cd /home/youruser/bankofyrmaps
php database/migrate.php
```

### Without shell access

cPanel → **phpMyAdmin** → select your database → **Import** →
upload `database/migrations/001_initial.mysql.sql` → Go.

Then create the migrations bookkeeping table and mark that file as applied, so
future migrations do not try to re-run it. In the **SQL** tab:

```sql
CREATE TABLE IF NOT EXISTS migrations (
  migration VARCHAR(191) NOT NULL PRIMARY KEY,
  applied_at VARCHAR(25) NOT NULL
);
INSERT INTO migrations (migration, applied_at)
VALUES ('001_initial.mysql.sql', NOW());
```

## 6. Permissions

```bash
cd /home/youruser/bankofyrmaps
chmod 755 storage storage/maps storage/cache storage/logs
chmod 755 public_html/uploads public_html/uploads/previews
chmod 600 config/config.php
```

PHP must be able to write to `storage/` and `public_html/uploads/previews`.
Nothing else needs write access.

## 7. SSL

cPanel → **SSL/TLS Status** → **Run AutoSSL** for bankofyrmaps.com.

The HTTPS redirect is already in `public_html/.htaccess`. It is active by
default — if the certificate is not issued yet and the site shows a redirect
loop, comment out the three `RewriteCond`/`RewriteRule` lines under
"Force HTTPS", then uncomment them once AutoSSL succeeds.

## 8. Check it works

- `https://bankofyrmaps.com/` loads
- `https://bankofyrmaps.com/maps` loads
- `https://bankofyrmaps.com/nonsense` gives the styled 404, not an Apache error
- `https://bankofyrmaps.com/app/bootstrap.php` gives **404 or 403** —
  if it shows PHP source or runs, your document root is wrong. Fix that before
  going further.

## Deploying updates

```bash
cd /home/youruser/bankofyrmaps
git pull
php database/migrate.php     # only if new migrations landed
```

`config/config.php`, `storage/maps/` and `public_html/uploads/previews/` are all
git-ignored, so pulling never touches your credentials or your published maps.

## Backups

Two things matter and they are backed up differently:

| What | Where | How |
|---|---|---|
| Map files and previews | `storage/maps`, `public_html/uploads/previews` | cPanel → Backup, or download periodically |
| The catalogue itself | MySQL database | cPanel → Backup → Download a MySQL Database Backup |

The code is in git and does not need backing up. The maps and the database do.
Do this before every deployment that runs a migration.

## Troubleshooting

**500 error, blank page.** Check `storage/logs/php-error.log`, and cPanel →
Errors. Set `'debug' => true` briefly if you need the message on screen — then
set it straight back to `false`.

**404 on every page except the home page.** `mod_rewrite` is not picking up
`.htaccess`. Confirm the file uploaded (it starts with a dot, so File Manager
may hide it — enable "Show Hidden Files").

**Downloads return 404.** The file is missing from `storage/maps`, or
`uploads.map_dir` in the config points somewhere else.

**Preview images are broken.** `public_html/uploads/previews` is missing or not
writable.
