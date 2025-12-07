<?php

include __DIR__ . '/config/database.php';

// Bağlantı kontrolü
if (!$connection) {
    header('Location: ' . ROOT_URL . 'index.php');
    die();
}


if (isset($_POST['submit'])) {
    $Username = filter_var($_POST['Name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $Surname = filter_var($_POST['Surname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $Email = filter_var($_POST['Email'], FILTER_SANITIZE_EMAIL);
    $Pass = filter_var($_POST['Password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $Birth = $_POST['Birth'];
    $Gender = filter_var($_POST['Gender'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $Branch = filter_var($_POST['Branch'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $Tel = filter_var($_POST['Telephone'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $Address = filter_var($_POST['Address'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$Username) {
        $_SESSION['signup-fail'] = "Please enter your Username";
    } else if (!$Surname) {
        $_SESSION['signup-fail'] = "Please enter your Surname";
    } else if (!$Email) {
        $_SESSION['signup-fail'] = "Please enter your Email";
    } else if (!$Pass) {
        $_SESSION['signup-fail'] = "Please enter your Password";
    } else if (!$Birth) {
        $_SESSION['signup-fail'] = "Please enter your Birthday";
    } else if (!$Gender) {
        $_SESSION['signup-fail'] = "Please enter your Gender";
    } else if (!$Branch) {
        $_SESSION['signup-fail'] = "Please enter your Branch";
    } else if (!$Tel) {
        $_SESSION['signup-fail'] = "Please enter your Telephone Number";
    } else if (!$Address) {
        $_SESSION['signup-fail'] = "Please enter your address";
    }
    $Pass = password_hash($Pass, PASSWORD_DEFAULT);
    // şifre doğrulama password_verify($HashPass,$Pass);

    $User_Fetch_Query = "SELECT * FROM users WHERE email='$Email' LIMIT 1";
    $result = pg_query($connection, $User_Fetch_Query);

    if (pg_num_rows($result) > 0) {
        $_SESSION['signup-fail'] = "this Email address already had taken";
    }


    if (isset($_SESSION['signup-fail'])) {
        $_SESSION['signup-data'] = $_POST;
        header('location: ' . ROOT_URL . 'signup.php');
        die();
    } else {
        $Insert_User_query = "INSERT INTO users (name, surname, email, password, date_of_birth, gender, is_admin, is_personel, branch_id, address, phone_number)
        VALUES ('$Username','$Surname','$Email','$Pass','$Birth','$Gender',0,0,'$Branch','$Address','$Tel')";
        $insert_result = pg_query($connection, $Insert_User_query);


        $fetch_user_id_query = "SELECT id FROM users WHERE email = '$Email' LIMIT 1";
        $fetch_Result = pg_query($connection, $fetch_user_id_query);
        $user_id = pg_fetch_assoc($fetch_Result);


        $id = $user_id['id'];
        $Insert_Customer_Query = "INSERT INTO customers (user_id,branch_id) VALUES($id,$Branch)";
        $customer_insert = pg_query($connection, $Insert_Customer_Query);

        if (pg_last_error($connection)) {
            echo (pg_last_error($connection));
            header('location: ' . ROOT_URL . 'signin.php');
        } else {

            header('location: ' . ROOT_URL . 'signin.php');
            die();
        }
    }
}



header('location: ' . ROOT_URL . 'signup.php');
die();
