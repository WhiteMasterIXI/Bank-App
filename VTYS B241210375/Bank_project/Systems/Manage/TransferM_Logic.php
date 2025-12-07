<?php
require '../config/database.php';

$Approve_state;
if (isset($_POST['approve'])) {
    $Approve_state = "APPROVED";
} else if (isset($_POST['reject'])) {
    $Approve_state = "REJECTED";
} else {
    header('location: ' . ROOT_URL . 'index.php');
    die();
}

$transaction_id = filter_var($_POST['transaction_id'], FILTER_SANITIZE_NUMBER_INT);

if (!$transaction_id) {
    $_SESSION['Transaction_Fail'] = "Işlem id si alınamadı işlem başarısız.";
}

if (isset($_SESSION['Transaction_Fail'])) {
    header('location: ' . ROOT_URL . 'Systems/Manage/TransferM.php');
    die();
}

// her şey düzgünse buraya gelicek ve işlemin sonucuna göre gerçekleştireceğiz

$description = '';
if($Approve_state == "APPROVED"){
    $Select_Transaction_Informations = "SELECT sender_account_id,receiver_account_id,external_iban,amount FROM transfer_transactions WHERE transaction_id = $transaction_id";
    $STI_Result = pg_query($connection,$Select_Transaction_Informations);
    $STI = pg_fetch_assoc($STI_Result);

    $sender_account_id = $STI['sender_account_id'];
    $receiver_account_id = $STI['receiver_account_id'];
    $amount = $STI['amount'];
    $external_iban = $STI['external_iban'];

        $Sender_Account_Update = "UPDATE accounts SET balance = balance - {$amount} WHERE id = $sender_account_id";
        $SAU_Result = pg_query($connection,$Sender_Account_Update);
    if (!empty($external_iban)){
       $description = "Transfer işlemi onaylandı. Gönderici hesap ID: $sender_account_id, alıcı IBAN: $external_iban, miktar: $amount TL.";
    }else{
        $Receiver_Account_Update = "UPDATE accounts SET balance = balance + {$amount} WHERE id = $receiver_account_id";
        $RAU_Result = pg_query($connection,$Receiver_Account_Update);
        $description = "Transfer işlemi onaylandı. Gönderici hesap ID: $sender_account_id, alıcı hesap ID: $receiver_account_id, miktar: $amount TL.";
    }
}else{
    $description = "Transfer işlemi reddedildi. İşlem ID: $transaction_id, gönderici hesap ID: $sender_account_id, miktar: $amount TL.";
}



    $UPDATE_Transaction_Approval_Query = "UPDATE transaction_approvals SET status = '$Approve_state' WHERE transaction_id = $transaction_id";
    $UTAQ = pg_query($connection,$UPDATE_Transaction_Approval_Query);

    $Select_User_info = "SELECT user_id,branch_id FROM transactions WHERE id = $transaction_id";
    $User_Result = pg_query($connection,$Select_User_info);
    $User = pg_fetch_assoc($User_Result);

    $personel_id = $_SESSION['user_id'];

    $Log_Record_Query = "INSERT INTO log_records 
    (branch_id,transaction_id, user_id, personel_id, status, log_type, description)
    VALUES 
    ({$User['branch_id']},$transaction_id, {$User['user_id']},$personel_id, '$Approve_state', 'TRANSFER', '$description')";

pg_query($connection, $Log_Record_Query);

unset($_SESSION['transaction_id']);
header('location: ' . ROOT_URL . 'Systems/Manage/Transfer.php');
die();
