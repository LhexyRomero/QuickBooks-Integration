<?php
    session_start();
    unset($_SESSION['sessionAccessToken']);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
?>