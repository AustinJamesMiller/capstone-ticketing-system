<?php

// Before we can destroy the session we have start it
session_start();

// This deletes and erases all session variables and ends the session
session_destroy();

// Redirect the user back to the main page
header('Location: index.php');

?>
