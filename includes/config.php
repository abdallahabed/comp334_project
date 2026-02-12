<?php

//check for session to use
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

require_once __DIR__ . '/db.php.inc';

function isLoggedIn(){
    return isset($_SESSION['user_id']);
}

function isFreelancer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Freelancer';
}

function isClient(){
    return isset($_SESSION['role']) && $_SESSION['role'] ==='Client';

}

function redirect($url){
    header("location: $url");
    exit();
}
?>



