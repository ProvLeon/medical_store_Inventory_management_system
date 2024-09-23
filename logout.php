<?php
require_once 'session_config.php';

session_destroy();
header("Location: index.html");
exit();
