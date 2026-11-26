<?php
require_once __DIR__ . '/auth.php';

logout();
header('Location: login.php?logged_out=1');
exit;
