<?php

/**
 * DBReconnect Filter
 * Varient CMS Addon — Stance Auto Magazine
 *
 * Fixes the "Server has gone away" MySQL error on GoDaddy and other shared
 * hosts that aggressively close idle database connections.
 *
 * INSTALLATION:
 * 1. Drop this file into app/Filters/DBReconnect.php
 * 2. Register the filter in app/Config/Filters.php (see Config/Filters_snippet.php)
 *
 * WHAT IT DOES:
 * Runs a lightweight SELECT 1 query before every request.
 * If the database connection has dropped, it reconnects automatically.
 * Completely silent — no impact on normal operation.
 *
 * WHY YOU NEED THIS:
 * GoDaddy shared hosting closes database connections after a short idle period.
 * Without this filter, the next request after an idle period throws a fatal
 * "Server has gone away" error. GoDaddy won't change the timeout server-side,
 * so this filter handles it at the application level instead.
 */

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class DBReconnect implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $db = \Config\Database::connect();

        try {
            $db->query('SELECT 1');
        } catch (\Exception $e) {
            $db->reconnect();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing needed here
    }
}
