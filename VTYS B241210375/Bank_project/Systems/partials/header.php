<?php require '../config/database.php'; ?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <title>Bank</title>
    <link rel="stylesheet" href="<?=ROOT_URL?>Style/style.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.2.0/css/line.css">
</head>

<body>

    <nav>
        <div class="nav__container">
            <a href="<?= ROOT_URL ?>index.php" class="Logo">
                <img src="<?= ROOT_URL ?>images/logo.png" alt="">
                <p>Bank</p>
            </a>
            <ul class="nav__options">
                <li class="nav__option"><a href="<?= ROOT_URL ?>index.php">Services</a></li>
                <li class="nav__option"><a href="<?= ROOT_URL ?>index.php">Contact</a></li>

                <?php
                if (!isset($_SESSION['user_id'])) : ?>
                    <li class="nav__option"><a href="signin.php">Login</a></li>
                <?php else : ?>
                    <li class="Profile">
                        <a href="" class="profile_head">My Account</a>
                        <ul class="Profile__options">
                            <?php
                            $user_id = $_SESSION['user_id'];
                            $user_query = "SELECT is_personel,is_admin FROM users WHERE id = $user_id";
                            $user_Result = pg_query($connection, $user_query);
                            $user = pg_fetch_assoc($user_Result);
                            ?>
                            <?php if ($user['is_personel'] == 1 || $_SESSION['is_admin'] == 1) : ?>
                                <li class="profile_option"><a href="<?= ROOT_URL ?>Systems/Manage/Transfer.php">Management</a></li>
                            <?php else : ?>
                                <li class="profile_option select"><a href="<?= ROOT_URL ?>Systems/Client/Transactions.php">Transactions</a></li>
                            <?php endif ?>
                            <li class="profile_option"><a href="<?= ROOT_URL ?>Systems/Statistic/Statistic.php">Informations</a></li>
                            <li class="profile_option"><a href="<?=ROOT_URL ?>logout.php">Exit</a></li>
                        </ul>
                    </li>
                <?php endif ?>
            </ul>
        </div>
    </nav>