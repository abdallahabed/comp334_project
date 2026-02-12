<?php

// $dbname = 'web1210802_freelance_db';
// $user = "web1210802_dbuser";
// $pass = "a.A.h.A@123";
$user = 'proj1210802';
$pass = 'pass1210802';

//$user = 'root';
//$pass = '';

$charset = 'utf8mb4';
$connectionString = "mysql:host=localhost;dbname=$dbname;charset=$charset";

try {

    $pdo = new PDO($connectionString , $user , $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
}
catch(PDOException $e){
        echo "connection to the data base has failed";
        die( $e->getMessage() );
}

