<?php
$details = Array(
    'host' => "localhost",
    'user' => "username",
    'pass' => "password",
    'db' => "database"
);
$dbc=mysqli_connect($details['host'], $details['user'], $details['pass']);
if(!$dbc)
    die('Not connected'.mysqli_error());
mysqli_select_db($dbc, $details['db']) or die('Cant connect');