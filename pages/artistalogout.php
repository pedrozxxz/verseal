<?php
// logout.php
session_start();
session_unset();
session_destroy();
header("Location: artistahome.php");
exit();
?>