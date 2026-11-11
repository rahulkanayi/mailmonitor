# Mail Delivery Monitor

A PHP 8+ and MySQL dashboard for viewing hourly mail delivery statistics from multiple VPS servers.

## Setup

1. Import `schema.sql` into MySQL:

   ```sh
   mysql -u root -p < schema.sql
   ```

2. Configure the database connection in the `.env` file in this directory, or set the equivalent environment variables:

   ```text
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=mailmonitor
   DB_USER=mailmonitor
   DB_PASSWORD=your-database-password
   ```

   Environment variables override values in `.env`. The checked-in `.env` file is ignored by Git; do not commit database credentials.

3. Create a login. Generate a password hash with PHP:

   ```sh
   php -r 'echo password_hash("replace-with-a-strong-password", PASSWORD_DEFAULT), PHP_EOL;'
   ```

   Insert the generated hash (not the plain-text password):

   ```sql
   INSERT INTO mailmonitor.users (username, password_hash)
   VALUES ('admin', 'paste-the-generated-hash-here');
   ```

4. Point a PHP 8+ web server's document root at this directory. For a local PHP development server, run `php -S 127.0.0.1:8000` from this directory and open `http://127.0.0.1:8000/login.php`.

The web server must use HTTPS in production so session cookies are marked secure.

## VPS data ingestion

Each hourly sender should insert one row into `mail_stats`. Store `recorded_at` in MySQL's server timezone consistently (UTC is recommended):

```sql
INSERT INTO mailmonitor.mail_stats
    (server_name, total, qp, dirdel, pending, deferred, bounced, recorded_at)
VALUES
    (?, ?, ?, ?, ?, ?, ?, ?);
```

Ingestion credentials should be limited to the required `INSERT` privilege on `mail_stats`.

## Queries used by the pages

The dashboard returns exactly one newest row per server, breaking timestamp ties by the larger row ID, then sorts server names alphabetically:

```sql
SELECT current.server_name, current.total, current.qp, current.dirdel,
       current.pending, current.deferred, current.bounced, current.recorded_at
FROM mail_stats AS current
WHERE NOT EXISTS (
    SELECT 1
    FROM mail_stats AS newer
    WHERE newer.server_name = current.server_name
      AND (
          newer.recorded_at > current.recorded_at
          OR (newer.recorded_at = current.recorded_at AND newer.id > current.id)
      )
)
ORDER BY current.server_name ASC;
```

The server history uses a prepared server-name parameter and returns the newest 24 rows:

```sql
SELECT server_name, total, qp, dirdel, pending, deferred, bounced, recorded_at
FROM mail_stats
WHERE server_name = ?
ORDER BY recorded_at DESC, id DESC
LIMIT 24;
```

The dashboard's authenticated `server_suggestions.php` endpoint returns up to 10 server names matching the typed prefix as JSON. The dashboard search supports keyboard navigation as well as mouse selection.
