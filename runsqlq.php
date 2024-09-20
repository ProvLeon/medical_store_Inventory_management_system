<?php
session_start();
if(!isset($_SESSION['med_admin']))
{
    header("Location: index.html");
    exit();
}
if(isset($_POST['sqlq']))
{
    $sqlq = $_POST['sqlq'];

    include 'config.php';
    $dbconn = mysqli_connect($dbhost, $dbuser, $dbpass, $db);
    if (!$dbconn) {
        exit('Error connecting to database: ' . mysqli_connect_error());
    }

    $result = mysqli_query($dbconn, $sqlq);
    if(!$result)
    {
        echo "Query Failed: " . mysqli_error($dbconn) . "<br />";
        exit;
    }

    $num_rows = mysqli_num_rows($result);
    echo "<pre>Fetched ".$num_rows." rows. Output:<br /><br />";
    echo "<table border=1><tr>";

    $field_info = mysqli_fetch_fields($result);
    foreach ($field_info as $field) {
        echo "<th>{$field->name}</th>";
    }
    echo "</tr>";

    while($row = mysqli_fetch_row($result))
    {
        echo "<tr>";
        foreach($row as $_column)
        {
            echo "<td>{$_column}</td>";
        }
        echo "</tr>";
    }
    echo "</table>";

    mysqli_free_result($result);
    mysqli_close($dbconn);
}
?>
