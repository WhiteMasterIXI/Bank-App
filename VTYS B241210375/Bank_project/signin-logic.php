<?php
include __DIR__ . '/config/database.php';

// Bağlantı kontrolü
if (!$connection) {
    header('Location: ' . ROOT_URL . 'index.php');
    die();
}

if (isset($_POST['submit'])) {
    $Email = filter_var($_POST['Email'], FILTER_SANITIZE_EMAIL);
    $Pass = filter_var($_POST['Password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$Email) {
        $_SESSION['Signin-fail'] = "Please enter email address.";
    } elseif (!$Pass) {
        $_SESSION['Signin-fail'] = "Please enter password.";
    }

    if (isset($_SESSION['Signin-fail'])) {
        $_SESSION['signin-data'] = $_POST;
        header('location: ' . ROOT_URL . 'signin.php');
        die();
    }

    // Güvenli parametreli sorgu
    $query = "SELECT id, password, is_admin FROM users WHERE email = '$Email'";
    $result = pg_query($connection,$query);

    if ($result && pg_num_rows($result) === 1) {
        $user = pg_fetch_assoc($result);

        if (password_verify($Pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = $user['is_admin'];
            header('location: ' . ROOT_URL . 'index.php');
            die();
        } else {
            $_SESSION['Signin-fail'] = "Incorrect password.";
        }
    } else {
        $_SESSION['Signin-fail'] = "No account found with that email.";
    }


}    
$_SESSION['signin-data'] = $_POST;
    header('location: ' . ROOT_URL . 'signin.php');
    die();