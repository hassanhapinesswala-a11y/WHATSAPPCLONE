<?php
require 'db.php';
session_unset();
session_destroy();
echo "<script>window.location='login.php';</script>";
exit;
?>
 
