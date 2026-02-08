<?php
// config/config.php

date_default_timezone_set("Asia/Jakarta");

// base url (ubah kalau folder beda)
define("BASE_URL", "http://localhost/absensi-guru");

// start session global
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
