<?php
    session_start();
    // Uništi sve varijable sesije
    session_unset();
    // Uništi samu sesiju
    session_destroy();
    // Vrati korisnika na stranicu za prijavu
    header("Location: LogIn.php");
    exit();
?>