<?php

namespace App\Database;

use Illuminate\Database\Connectors\PostgresConnector;

/**
 * Workaround for libpq versions that do not support SNI (Server Name Indication).
 *
 * Neon routes connections to the correct compute endpoint using SNI. When libpq
 * is too old to send SNI (e.g., XAMPP on Windows, PHP < 8.3 on some distros),
 * the connection is rejected with:
 *   "Endpoint ID is not specified."
 *
 * The fix is to embed the endpoint ID in the PostgreSQL `options` startup
 * parameter. Set DB_NEON_ENDPOINT in your .env to the first segment of the
 * Neon host (everything before the first dot), e.g.:
 *   DB_NEON_ENDPOINT=ep-holy-queen-azzs1vqy
 *
 * On production servers with modern libpq, leave DB_NEON_ENDPOINT unset —
 * the parent DSN is used unchanged and SNI handles routing automatically.
 */
class NeonPostgresConnector extends PostgresConnector
{
    public function getDsn(array $config): string
    {
        $dsn = parent::getDsn($config);

        if (! empty($config['neon_endpoint'])) {
            $dsn .= ";options=endpoint={$config['neon_endpoint']}";
        }

        return $dsn;
    }
}
