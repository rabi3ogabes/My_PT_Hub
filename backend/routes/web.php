<?php

/*
| Web routes are split per area in routes/web/*.php (marketing, auth, app, admin, webhooks).
*/
foreach (glob(__DIR__.'/web/*.php') ?: [] as $file) {
    require $file;
}
