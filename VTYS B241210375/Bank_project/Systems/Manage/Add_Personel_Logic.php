<?php

include '../config/database.php';


if (isset($_POST['submit'])) {
    $Username = filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $Surname = filter_var($_POST['surname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $Email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $Pass = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $Role_id = filter_var($_POST['role_id'],FILTER_SANITIZE_NUMBER_INT);
    $Branch = filter_var($_POST['branch_id'], FILTER_SANITIZE_NUMBER_INT);

    if (!$Username) {
        $_SESSION['Add_Personel_Fail'] = "Please enter personel Username";
    } else if (!$Surname) {
        $_SESSION['Add_Personel_Fail'] = "Please enter personel Surname";
    } else if (!$Email || !str_contains($Email,'@')) {
        $_SESSION['Add_Personel_Fail'] = "Please enter personel Email";
    } else if (!$Pass) {
        $_SESSION['Add_Personel_Fail'] = "Please enter personel Password";
    } else if (!$Role_id) {
        $_SESSION['Add_Personel_Fail'] = "Please enter personel Role";
    }else if (!$Branch) {
        $_SESSION['Add_Personel_Fail'] = "Please enter personel Branch";
    }

    $Pass = password_hash($Pass, PASSWORD_DEFAULT);
    // şifre doğrulama password_verify($HashPass,$Pass);

    $User_Fetch_Query = "SELECT * FROM users WHERE email='$Email' LIMIT 1";
    $result = pg_query($connection, $User_Fetch_Query);

    if (pg_num_rows($result) > 0) {
        $_SESSION['Add_Personel_Fail'] = "this Email address already had taken";
    }


    if (isset($_SESSION['Add_Personel_Fail'])) {
        $_SESSION['signup-data'] = $_POST;
        header('location: ' . ROOT_URL . 'Systems/Manage/Personel_Management.php');
        die();
    } else {
        $Insert_User_query = "INSERT INTO users (name, surname, email, password, is_admin, is_personel, branch_id)
        VALUES ('$Username','$Surname','$Email','$Pass',0,1,'$Branch') RETURNING id";
        $insert_result = pg_query($connection, $Insert_User_query);

        $user_fetch = pg_fetch_assoc($insert_result);
        $id = $user_fetch['id'];
        $Insert_Personel_Query = "INSERT INTO personels (user_id,role_id,branch_id,hire_date) VALUES($id,$Role_id,$Branch,CURRENT_DATE)";
        $personel_insert_result = pg_query($connection, $Insert_Personel_Query);

//         CREATE TABLE personels(
    //   user_id INT PRIMARY KEY,
    //   role_id INT REFERENCES roles(id) ON UPDATE CASCADE ON DELETE SET NULL,
    //   branch_id INT REFERENCES branches(id) ON UPDATE CASCADE ON DELETE SET NULL,
    //   hire_date DATE,
    //   salary NUMERIC(10,2),
    //   last_assigned TIMESTAMP DEFAULT 2000-01-01,
    //   CONSTRAINT fk_personels_user FOREIGN KEY (user_id) REFERENCES users(id)
//       ON UPDATE CASCADE
//       ON DELETE CASCADE
// );

    }
}



header('location: ' . ROOT_URL . 'Systems/Manage/Personel_Management.php');
die();