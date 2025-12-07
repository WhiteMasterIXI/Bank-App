<?php
require '../config/database.php';

if(!isset($_POST['submit'])){
    header('location: ' . ROOT_URL . 'index.php');
    die();
}

$card_id = filter_var($_POST['card_id'],FILTER_SANITIZE_NUMBER_INT);
$account_id = filter_var($_POST['account'],FILTER_SANITIZE_NUMBER_INT);
$debt = filter_var($_POST['debt'],FILTER_SANITIZE_NUMBER_INT);
$amount = filter_var($_POST['amount'],FILTER_SANITIZE_NUMBER_INT);

if(!$debt){
    $_SESSION['Debit_Payment_fail'] = "Işlem başarısız borç miktarı alınamadı.";
}elseif(!$amount){
    $_SESSION['Debit_Payment_fail'] = "Işlem başarısız lütfen ödeme miktarını boş bırakmayınız.";
}elseif(!$account_id){
    $_SESSION['Debit_Payment_fail'] = "Işlem başarısız Hesap numarası alınamadı.";
}elseif(!$card_id){
    $_SESSION['Debit_Payment_fail'] = "Işlem başarısız Kart numarası alınamadı.";
}

if($amount > $debt || $amount <= 0){
    $_SESSION['Debit_Payment_fail'] = "Işlem başarısız geçerli bir borç miktarı giriniz";
}

// accountta o kadar para var mı kontrol ediyoruz.

$Select_Account_Amount = "SELECT balance,status FROM accounts WHERE id = $account_id";
$Amount_Result = pg_query($connection,$Select_Account_Amount);
$Amount_fetch = pg_fetch_assoc($Amount_Result);
$Account_Amount = $Amount_fetch['balance'];
$Status = $Amount_fetch['status'];

if($Account_Amount < $amount){
    $_SESSION['Debit_Payment_fail'] = "Işlem başarısız hesabınızda yeterince para bulunmamaktadır";
}elseif($Status != 'active'){
    $_SESSION['Debit_Payment_fail'] = "Işlem başarısız hesabınız '$Status' modundadır.";
}


if(isset($_SESSION['Debit_Payment_fail'])){
    header('location: '. ROOT_URL . 'Systems/Client/User_Cards_Informations.php');
    die();
}

// artık işlemi gerçekleştirebiliriz

$Update_Card_Limit = "UPDATE cards SET card_available_limit = card_available_limit + $amount WHERE id = $card_id";
pg_query($connection,$Update_Card_Limit);

$Update_Account_Amount = "UPDATE accounts SET balance = balance - $amount WHERE id = $account_id";
pg_query($connection,$Update_Account_Amount);

$user_id = $_SESSION['user_id'];
$description = "$account_id numaralı hesaptan $card_id kartına $amount TL tutarında borç ödemesi gerçekleştirilmiştir.";
$Log_Record_Query = "INSERT INTO log_records (user_id,status,log_type,description) VALUES ($user_id,'APPROVED','CARD_DEBT','$description')";
pg_query($connection,$Log_Record_Query);

$_SESSION['Debit_Payment_success'] = "Işlem başarılı ödeme gerçekleştirildi";
header('location: '. ROOT_URL . 'Systems/Client/User_Cards_Informations.php');
    die();
    ?>