<?php

include  '../config/database.php';

if (isset($_POST['submit'])) {
    $Username = filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $Surname = filter_var($_POST['surname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $Email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $Pass = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $Branch = filter_var($_POST['branch_id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $Role = filter_var($_POST['role_id'], FILTER_SANITIZE_NUMBER_INT);

    $user_id = filter_var($_POST['user_id'],FILTER_SANITIZE_NUMBER_INT);

    if (!$Username) {
        $_SESSION['update_fail'] = "Please enter personel Username";
    } else if (!$Surname) {
        $_SESSION['update_fail'] = "Please enter personel Surname";
    } else if (!$Email) {
        $_SESSION['update_fail'] = "Please enter personel Email";
    } else if (!$Pass) {
        $_SESSION['update_fail'] = "Please enter personel Password";
    }  else if (!$Branch) {
        $_SESSION['update_fail'] = "Please enter personel Branch";
    } else if (!$Role) {
        $_SESSION['update_fail'] = "Please enter personel Role";
    } else if(!$user_id){
        $_SESSION['update_fail'] = "Personel id couldn't taken";
    }
    $Pass = password_hash($Pass, PASSWORD_DEFAULT);
    // şifre doğrulama password_verify($HashPass,$Pass);

    $User_Fetch_Query = "SELECT * FROM users WHERE email='$Email' AND id != $user_id LIMIT 1";
    $result = pg_query($connection, $User_Fetch_Query);

    if (pg_num_rows($result) > 0) {
        $_SESSION['update_fail'] = "this Email address already had taken";
    }


    if (isset($_SESSION['update_fail'])) {
        header('location: ' . ROOT_URL . 'Systems/Manage/Personel_Management.php');
        die();
    } else {
        $Update_User_query = "UPDATE users SET name = '$Username',surname = '$Surname',email = '$Email',password = '$Pass',branch_id = $Branch WHERE id = $user_id ";
        $Update_result = pg_query($connection, $Update_User_query);

        $Update_Personel_Role = "UPDATE personels SET role_id = $Role  WHERE user_id = $user_id";
        $Personel_Update_Result = pg_query($connection,$Update_Personel_Role);


        $_SESSION['update_success'] = "Personel successfully updated.";
    }
}


header('location: ' . ROOT_URL . 'Systems/Manage/Personel_Management.php');
die();
