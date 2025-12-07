<?php
require '../config/database.php';

$transaction_state;
$state_string;
if (isset($_POST['approve'])) {
  $transaction_state = 1;
  $state_string = 'APPROVED';
} elseif (isset($_POST['reject'])) {
  $transaction_state = 0;
  $state_string = 'REJECTED';
} else {
  header('location: ' . ROOT_URL . 'index.php');
  die();
}

$type = filter_var($_POST['Card_Trans_Type'], FILTER_SANITIZE_NUMBER_INT);
$transaction_id = filter_var($_POST['transaction_id'], FILTER_SANITIZE_NUMBER_INT);


if ($transaction_state == 0) {
  $Record_Log_Query = "UPDATE transaction_approvals SET status = '$state_string' WHERE transaction_id = $transaction_id";
  $Record_Result = pg_query($connection, $Record_Log_Query);
  $_SESSION['Card_Transaction_success'] = "İşlem reddedildi";
} elseif ($transaction_state == 1) {

  // işlem bilgilerini çekmemiz gerek.
  $Select_Card_Transaction = "SELECT t.branch_id,t.user_id,t.account_id,ct.new_limit,ct.card_type_id,ct.card_id FROM transactions t JOIN card_transactions ct ON ct.transaction_id = t.id WHERE t.id = $transaction_id";
  $Card_Result = pg_query($connection, $Select_Card_Transaction);
  $Card = pg_fetch_assoc($Card_Result);
  $transaction_type;
  // echo 'Post Verileri.';
  // echo '<br>';
  // var_dump($_POST);
  // echo '<br>';
  // echo 'Card Verileri.';
  // echo '<br>';
  // var_dump($Card);


  $card_branch = $Card['branch_id'] ?? null;
  $card_user = $Card['user_id'] ?? null;
  $card_account = $Card['account_id'] ?? null;
  $card_limit = $Card['new_limit'] ?? null;
  $card_type = $Card['card_type_id'] ?? null;

  $card_id = $Card['card_id'] ?? null;


  //buraya bütün türler gelmiyor zaten limit değişimi ve kart oluşturma için geliyor

  $description = '';
  switch ($type) {
    case 1:
      $transaction_type = "CARD_CREATE";
      $card_no = generateCardNumber($connection);

      // kart numarası 16 haneli bi kurala göre oluştur
        $Card_Create_Query = "INSERT INTO cards (branch_id,user_id,account_id,card_type_id,card_limit,card_available_limit,card_number)
      VALUES ($card_branch,$card_user,$card_account,$card_type,$card_limit,$card_limit,'$card_no') ";
        $Create_Result = pg_query($connection, $Card_Create_Query);

      if ($card_limit == 0)
        $description = "$card_no numarasına sahip banka kartınız oluşturulmuştur.";
      else {
        $description = "$card_no numarasına sahip $card_limit limitli bir kartınız oluşturulmuştur.";
      }
      break;
    case 2: // kart limit güncelleme
      $transaction_type = "CARD_UPDATE";
      $Select_Card_Query = "SELECT card_limit,card_available_limit,card_number FROM cards WHERE id = $card_id";
      $Select_Card_Res = pg_query($connection, $Select_Card_Query);
      $Card_informations = pg_fetch_assoc($Select_Card_Res);


      echo '<br> Güncelleme için kart bilgileri <br>';
      var_dump($Card_informations);
      $New_Available_limit = $card_limit - ($Card_informations['card_limit'] - $Card_informations['card_available_limit']);
      $Card_Limit_Update = "UPDATE cards SET card_limit = $card_limit,card_available_limit = $New_Available_limit WHERE id = $card_id";
      pg_query($connection,$Card_Limit_Update);

      $card_number = $Card_informations['card_number'];
      $description = "$card_number numarasına sahip kartınızın limiti $card_limit e değiştirilmiştir";
      break;
    default:
      $transaction_type = "Tanımlanmayan işlem";
  }

  // echo '<br> ' . $description;
  $transaction_approval_query = "UPDATE transaction_approvals SET status = '$state_string' WHERE transaction_id = $transaction_id";
  pg_query($connection, $transaction_approval_query);
  $_SESSION['Card_Transaction_success'] = "İşlem onaylandı.";

  $personel_id = $_SESSION['user_id'];
  // Log kaydını ekleme
  $Log_Record_Query = "
INSERT INTO log_records
(transaction_id, user_id, personel_id, status, log_type, description)
VALUES
($transaction_id, $card_user, $personel_id, '$state_string', '$transaction_type', '$description')
";

  pg_query($connection, $Log_Record_Query);
}

unset($_SESSION['card_transaction_id']);

header('location: ' . ROOT_URL . 'Systems/Manage/Cards.php');
die();



function generateCardNumber($connection)
{
  // BIN 6 hane, toplam 16 olacak => 9 hane random + 1 check digit
  do {
    $number = CardNumber();
    $Select_Card_Query = "SELECT 1 FROM cards WHERE card_number = '$number' LIMIT 1";
    $Card_Result = pg_query($connection, $Select_Card_Query);
    $Card = pg_fetch_assoc($Card_Result);
  } while ($Card);

  return $number;
}

function CardNumber($bin = "535422")
{
  // BIN 6 hane, toplam 16 olacak => 9 random + 1 check
  $card = $bin;

  // 9 haneli gerçek rastgele sayı
  for ($i = 0; $i < 9; $i++) {
    $card .= random_int(0, 9);
  }

  // Luhn hesaplama
  $sum = 0;
  $reverse = strrev($card);

  for ($i = 0; $i < strlen($reverse); $i++) {
    $digit = intval($reverse[$i]);
    if ($i % 2 == 0) {
      $digit *= 2;
      if ($digit > 9) $digit -= 9;
    }
    $sum += $digit;
  }

  $checkDigit = (10 - ($sum % 10)) % 10;

  return $card . $checkDigit;
}
