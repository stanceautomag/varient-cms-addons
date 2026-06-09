<?php

/**
 * Filters.php — DBReconnect Registration Snippet
 * Varient CMS Addon — Stance Auto Magazine
 *
 * This is NOT a complete file replacement.
 * Make the two changes below to your existing app/Config/Filters.php
 *
 * CHANGE 1 — Add the import at the top of the file with the other use statements:
 *
 *     use App\Filters\DBReconnect;
 *
 *
 * CHANGE 2 — Add 'dbreconnect' to the $aliases array:
 *
 *     public array $aliases = [
 *         'csrf'         => CSRF::class,
 *         'toolbar'      => DebugToolbar::class,
 *         // ... other aliases ...
 *         'dbreconnect'  => DBReconnect::class,   // <-- ADD THIS LINE
 *     ];
 *
 *
 * CHANGE 3 — Add 'dbreconnect' to the $globals before array:
 *
 *     public array $globals = [
 *         'before' => [
 *             'csrf',
 *             'dbreconnect',   // <-- ADD THIS LINE
 *         ],
 *         'after' => [],
 *     ];
 */
