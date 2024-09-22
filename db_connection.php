<?php

require_once __DIR__ . '/config.php';
function Connect()
{
    $dbhost = DB_HOST;
    $dbuser = DB_USER;
    $dbpass = DB_PASS;
    $db = DB_NAME;

    $conn = new mysqli($dbhost, $dbuser, $dbpass, $db) or die("Connect failed: %s\n". $conn->error);

    return $conn;
}

function Close($conn)
{
    $conn->close();
}
