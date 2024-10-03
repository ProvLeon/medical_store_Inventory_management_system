<?php
require_once 'config.php';
require_once 'db_connection.php';

$dbconn = Connect();

// Fetch available medicines (quantity > 0)
$query = "SELECT * FROM " . DB_TABLE_MEDICINE . " WHERE quantity > 0 ORDER BY name";
$result = mysqli_query($dbconn, $query);
$availableMedicines = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

// Fetch sold out medicines
$query = "SELECT * FROM " . DB_TABLE_MEDICINE . " WHERE quantity = 0 ORDER BY name";
$result = mysqli_query($dbconn, $query);
$soldOutMedicines = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

mysqli_close($dbconn);

echo json_encode([
    'availableMedicines' => $availableMedicines,
    'soldOutMedicines' => $soldOutMedicines
]);
