<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Set flash message
|--------------------------------------------------------------------------
*/
function setFlash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type, // success | danger | warning | info
        'message' => $message
    ];
}

/*
|--------------------------------------------------------------------------
| Show flash message
|--------------------------------------------------------------------------
*/
function flash()
{
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];

        echo "
        <div class='alert alert-$type alert-dismissible fade show' role='alert'>
            $message
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";

        unset($_SESSION['flash']);
    }
}
