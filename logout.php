<?php

// before we can destroy the session we have start it
session_start();

// this deletes and erases all session variables and ends the session
session_destroy();

// redirect the user back to the main page
header('Location: index.php');

?>
