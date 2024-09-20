 <?php
 session_start();
 if(isset($_POST['submit']) && isset($_POST['username']) && isset($_POST['password'])) {
     include 'config.php';

     $username = stripslashes($_POST['username']);
     $password = stripslashes($_POST['password']);

     $dbconn = mysqli_connect($dbhost, $dbuser, $dbpass, $db);
     if (!$dbconn) {
         die("Connection failed: " . mysqli_connect_error());
     }

     $query = "SELECT role FROM ".$dbtable." WHERE username=? AND password=?";
     $stmt = mysqli_prepare($dbconn, $query);
     mysqli_stmt_bind_param($stmt, "ss", $username, $password);
     mysqli_stmt_execute($stmt);
     $result = mysqli_stmt_get_result($stmt);
     $gotCreds = mysqli_fetch_assoc($result);

     if($gotCreds) {
         if($gotCreds['role'] == 'med_admin') {
             $_SESSION['med_admin'] = 'adminadmin';
             header("Location: med_admin_screen.php");
         } elseif($gotCreds['role'] == 'receptionist') {
             $_SESSION['receptionist'] = 'receptionistic';
             header("Location: med_store_reception.php");
         } elseif($gotCreds['role'] == 'doctor') {
             $_SESSION['doctor'] = 'doctordoctor';
             header("Location: med_store_doctor.php");
         }
     } else {
         header("Location: index.html");
     }

     mysqli_close($dbconn);
 } else {
     header("Location: index.html");
 }
 ?>
