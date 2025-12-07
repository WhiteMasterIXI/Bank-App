<?php
require '../config/database.php';


if (!isset($_POST['submit'])) {
    header('location:' . ROOT_URL . 'index.php');
    die();
}

$card_id = filter_var($_POST['card_id'], FILTER_SANITIZE_NUMBER_INT);
$card_limit = filter_var($_POST['limit'], FILTER_SANITIZE_NUMBER_INT);
$description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$action = filter_var($_POST['action'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$Select_Card_Type_Query = "SELECT card_type_id,card_limit,card_available_limit FROM cards WHERE id = $card_id";
$Card_Type_Result = pg_query($connection, $Select_Card_Type_Query);
$Card = pg_fetch_assoc($Card_Type_Result);
$Card_Type = $Card['card_type_id'];

$_debt = $Card['card_limit'] - $Card['card_available_limit'];

if (!$card_id) {
    $_SESSION['Card_Transaction_fail'] = "kart numarası alınamadı";
} else if (!$action) {
    $_SESSION['Card_Transaction_fail'] = "yapılacak işlem seçilemedi";
} else if ($action == 'CARD_UPDATE' && $Card_Type == 1) {
    $_SESSION['Card_Transaction_fail'] = "Banka kartının limitini değiştiremezsiniz";
}else if($card_limit < $_debt){
    $_SESSION['Card_Transaction_fail'] = "Borç miktarınızdan daha az kart limiti talebinde bulunamazsınız.";
}

// Banka kartının limitini buradan değiştiremez para yatırması lazım dışarıdan
// veya Yönetici hesabı ile ordan müşteriye para ekleyebiliriz.


if (isset($_SESSION['Card_Transaction_fail'])) {
    header('location: ' . ROOT_URL . 'Systems/Client/CardTransactions.php');
    die();
}

// Burayı otomatik hale getiriyoruz bazı şartları sağlıyorsa personele gitmeden de halledebiliriz.
if ($action == 'CARD_FREEZE') {
    $Query = "UPDATE cards SET status = 'frozen' WHERE id =$card_id";
    $Result = pg_query($connection, $Query);
} elseif ($action == 'CARD_DELETE') {
    // kredi limiti max_limite eşitse yani borçlanmamışsa silebilir
    $Select_Card = "SELECT card_limit,card_available_limit FROM cards WHERE id = $card_id";
    $Card_Result = pg_query($connection, $Select_Card);
    $Card = pg_fetch_assoc($Card_Result);
    if ($Card['card_limit'] != $Card['card_available_limit']) {
        $_SESSION['Card_Transaction_fail'] = "Kartı silmek için borcunu kapatmanız gerek.";
        header('location: ' . ROOT_URL . 'Systems/Client/CardTransactions.php');
        die();
    }
    $Query = "DELETE FROM cards WHERE id = $card_id";
    $Result = pg_query($connection, $Query);
} elseif ($action == 'CARD_ACTIVATE') {
    $Query = "UPDATE cards SET status = 'active' WHERE id =$card_id";
    $Result = pg_query($connection, $Query);
} elseif ($action == 'CARD_UPDATE') {
    // kredi limit değişimi arkada personele gönderilicek
    $Select_Personel_Query = "
    SELECT user_id 
    FROM personels 
    WHERE role_id IN (4)
    ORDER BY last_assigned ASC
    LIMIT 1
";
    $user_id = $_SESSION['user_id'];

    $Transaction_Query = "INSERT INTO transactions (user_id,trans_type,amount) VALUES ($user_id,'$action',$card_limit) RETURNING id";
    $Transaction_Result = pg_query($connection, $Transaction_Query);
    $Transaction_fetch = pg_fetch_assoc($Transaction_Result);
    $transaction_id = $Transaction_fetch['id'];

    // card_transactiona aktarma
    $Card_Transaction_Query = "INSERT INTO card_transactions (transaction_id,card_id,action,new_limit) VALUES ($transaction_id,$card_id,'$action',$card_limit)";
    $Card_Transaction_Result = pg_query($connection, $Card_Transaction_Query);

    $Personel_Result = pg_query($connection, $Select_Personel_Query);
    $Personel = pg_fetch_assoc($Personel_Result);
    $personel_id = $Personel['user_id'];

    $Transaction_Approvals_Query = "INSERT INTO transaction_approvals (transaction_id,trans_type,personel_id,user_id) VALUES ($transaction_id,'$action',$personel_id,$user_id)";
    $Transaction_Approvals_Result = pg_query($connection, $Transaction_Approvals_Query);

    $Update_Last_Assigned = "
    UPDATE personels 
    SET last_assigned = NOW()
    WHERE user_id = $personel_id
";
    pg_query($connection, $Update_Last_Assigned);
}

$_SESSION['Card_Transaction_success'] = "İşlem başarılı.";

header('location: ' . ROOT_URL . 'Systems/Client/CardTransactions.php');
die();
