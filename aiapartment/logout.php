<?php
session_start();
session_unset();
session_destroy();

// Also clear "Remember Me" cookie
setcookie("remember_user", "", time() - 3600, "/");

header("Location: login.php");
exit;
