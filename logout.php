 
<?php
session_start();
session_destroy();
header("Location: /karmiq/login.php");
exit();
?>