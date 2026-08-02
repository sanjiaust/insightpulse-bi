<?php
require_once 'auth.php';
header('Location: ' . (is_logged_in() ? 'hub.php' : 'login.php'));
exit;
