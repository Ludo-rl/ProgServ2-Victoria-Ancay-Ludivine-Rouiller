<?php
session_start();
$_SESSION = [];      // on vide les données de la session
session_destroy();   // et on détruit la session
header("Location: login.php");
exit;
