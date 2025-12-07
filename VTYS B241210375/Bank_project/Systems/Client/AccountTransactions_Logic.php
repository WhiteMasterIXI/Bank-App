<?php
require '../config/database.php';


if (!isset($_POST['submit'])) {
    header('location:' . ROOT_URL . 'index.php');
    die();
}

$account_id = filter_var($_POST['account_id'], FILTER_SANITIZE_NUMBER_INT);
$description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$action = filter_var($_POST['action'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);


if (!$account_id) {
    $_SESSION['Account_Transaction_fail'] = "kart numarası alınamadı";
} else if (!$action) {
    $_SESSION['Account_Transaction_fail'] = "yapılacak işlem seçilemedi";
}

if (isset($_SESSION['Account_Transaction_fail'])) {
    header('location: ' . ROOT_URL . 'Systems/Client/CardTransactions.php');
    die();
}

// Burayı otomatik hale getiriyoruz bazı şartları sağlıyorsa personele gitmeden de halledebiliriz.
if ($action == 'ACCOUNT_FREEZE') {
    $Query = "UPDATE accounts SET status = 'frozen' WHERE id =$account_id";
    $Result = pg_query($connection, $Query);
} elseif ($action == 'ACCOUNT_DELETE') {
    // hesabında para yoksa ve kart bağlı değilse (RESTRICT var) hesap silinebilir.
    $Select_Account = "SELECT balance FROM accounts WHERE id = $account_id";
    $Result = pg_query($connection, $Select_Account);
    $Account = pg_fetch_assoc($Result);

    if ($Account['balance'] != 0) {
        $amount = $Account['balance'];
        $_SESSION['Account_Transaction_fail'] = "Hesabınızdaki $amount i aktarmanız gerekli";
    }

    if (isset($_SESSION['Account_Transaction_fail'])) {
        header('location: ' . ROOT_URL . 'Systems/Client/AccountTransactions.php');
        die();
    }
    $Query = "DELETE FROM accounts WHERE id = $account_id";
    $Result = pg_query($connection, $Query);
} elseif ($action == 'ACCOUNT_ACTIVATE') {
    $Query = "UPDATE accounts SET status = 'active' WHERE id =$account_id";
    $Result = pg_query($connection, $Query);
}

if($Result){
    $_SESSION['Account_Transaction_success'] = "İşlem başarıyla gerçekleştirildi.";
}else{
    $_SESSION['Account_Transaction_fail'] = "İşlem başarısız Hesabınızda kart bulunmaktadır. ";
}



header('location: ' . ROOT_URL . 'Systems/Client/AccountTransactions.php');
die();
