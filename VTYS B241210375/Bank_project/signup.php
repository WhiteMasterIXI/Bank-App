<?php

require 'config/database.php';

// get back form data if there was a registration error 
$firstname = $_SESSION['signup-data']['Name'] ?? null;
$lastname = $_SESSION['signup-data']['Surname'] ?? null;
$email = $_SESSION['signup-data']['Email'] ?? null;
$password = $_SESSION['signup-data']['Password'] ?? null;
$Birth = $_SESSION['signup-data']['Birth'] ?? null;
$Tel = $_SESSION['signup-data']['Telephone'] ?? null;
$Address = $_SESSION['signup-data']['Address'] ?? null;
unset($_SESSION['signup-data']);



$Branch_Query = "SELECT * FROM branches";
$Branch_Result = pg_query($connection,$Branch_Query);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <title>Bank</title>
    <link rel="stylesheet" href="Style/style.css">
</head>

<body>


    <section>
        <div class="section__container">
            <h2>Bank</h2>
            <div class="form__container">
                <div class="information">
                    Hoşgeldiniz
                    <?php if (isset($_SESSION['signup-fail'])) : ?>
                        <p><?= $_SESSION['signup-fail'] ?></p>
                    <?php unset($_SESSION['signup-fail']);
                    endif ?>
                </div>

                <form class="sign_form" action="signup-logic.php" method="POST">
                    <div class="double_container">
                        <input value="<?= $firstname ?>" name="Name" placeholder="Name" type="text">
                        <input value="<?= $lastname ?>" name="Surname" placeholder="Surname" type="text">
                    </div>
                    <p>Email </p><input value="<?= $email ?>" name="Email" placeholder="Email" type="email">
                    <p>Password </p><input value="<?= $password ?>" name="Password" placeholder="Password" type="password">
                    <p>Birth </p><input name="Birth" type="date">
                    <p>Gender</p>
                    <select name="Gender" id="">
                        <option value="1">Male</option>
                        <option value="2">Female</option>
                    </select>

                    <div class="double_container">
                        <p>Branch</p>
                        <select name="Branch" id="">
                            <?php while($branch = pg_fetch_assoc($Branch_Result)) : ?>
                            <option value="<?= $branch['id'] ?>"><?= $branch['name'] ?></option>
                            <?php endwhile ?>
                        </select>
                        <input name="Telephone" placeholder="Telephone" type="tel">
                    </div>
                    <p>Address</p>
                    <textarea placeholder="Address" name="Address" id=""><?= $Address ?></textarea>
                    <div class="form_submit_container">
                        <button class="form__button" name="submit" type="submit">Sign up</button>
                        <a href="signin.php">If already have an account!</a>
                    </div>
                </form>
            </div>
        </div>
    </section>


    <?php include 'partials/footer.php'?>