<?php 
include '../config/database.php';

$account_id = filter_var($_POST['account'],FILTER_SANITIZE_NUMBER_INT);
$transfer_account = filter_var($_POST['Payee'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Transfer_type = filter_var($_POST['Transfer_type'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$amount = filter_var($_POST['amount'],FILTER_SANITIZE_NUMBER_INT);
$description = filter_var($_POST['description'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if(!$account_id || !$transfer_account || !$Transfer_type || !$amount){
    $_SESSION['Transfer_Fail'] = "Hiçbir alanı boş bırakmayınız";
    header('location: '.ROOT_URL . 'Systems/Client/TransferTransactions.php');
    die();
}

$user_id = $_SESSION['user_id'];

$Select_Account_Query = "SELECT branch_id,currency,balance,status FROM accounts WHERE id = $account_id LIMIT 1";
$Account_Query_Result = pg_query($connection,$Select_Account_Query);
$Account_fetch = pg_fetch_assoc($Account_Query_Result);

// hesap aktarım yapabilir mi onu kontrol ediyoruz
$Account_Status = $Account_fetch['status'];
$Balance = $Account_fetch['balance'];

if ($amount <= 0) {
    $_SESSION['Transfer_Fail'] = "Tutarı negatif bir miktar giremezsiniz.";
}else if($amount > $Balance){
    $_SESSION['Transfer_Fail'] = "Işlem başarısız Hesapta yeterince bakiye yok.";
}else if($Account_Status != 'active'){
    $_SESSION['Transfer_Fail'] = "Hesabınız '$Account_Status' modunda aktif etmeniz gerekli";
}else if($account_id == $transfer_account){
    $_SESSION['Transfer_Fail'] = "Aynı hesaba havale yapamazsınız";
}


if(isset($_SESSION['Transfer_Fail'])){
    header('location: '.ROOT_URL . 'Systems/Client/TransferTransactions.php');
    die();
}

$branch_id = $Account_fetch['branch_id'];
$currency = $Account_fetch['currency'];


$INSERT_Transaction_Query = "INSERT INTO transactions (user_id,branch_id,trans_type,account_id,amount,currency) VALUES ($user_id,$branch_id,'$Transfer_type',$account_id,$amount,'$currency') RETURNING id";
$Transaction_Query_Result = pg_query($connection,$INSERT_Transaction_Query);
$Transaction_Fetch = pg_fetch_assoc($Transaction_Query_Result);

$Transaction_id = $Transaction_Fetch['id'];

// CREATE TABLE transactions (
//     id SERIAL PRIMARY KEY,
//     user_id INT REFERENCES users(id) ON UPDATE CASCADE,               -- işlemi başlatan kullanıcı
//     branch_id INT REFERENCES branches(id) ON UPDATE CASCADE ON DELETE SET NULL,
//     trans_type VARCHAR(50), -- işlem tipi (örn: EFT, payment)
//     account_id INT REFERENCES accounts(id) ON UPDATE CASCADE DEFAULT NULL,
//     amount NUMERIC(15,2) DEFAULT 0.00,                                -- genel tutar (bazı işlemler için boş kalabilir)
//     currency CHAR(3) DEFAULT 'TRY',
//     status VARCHAR(20) DEFAULT 'PENDING',                             -- PENDING, APPROVED, REJECTED
//     created_at TIMESTAMP DEFAULT now(),
//     approved_at TIMESTAMP
// );

$INSERT_Transfer_Transactions_Query;

if(str_contains($transfer_account,'TR')){
$INSERT_Transfer_Transactions_Query = "INSERT INTO transfer_transactions (transaction_id,sender_account_id,external_bank_name,amount,currency,external_iban,description)
VALUES ($Transaction_id,$account_id,'DenemeBank',$amount,'$currency','$transfer_account','$description')";
}else{
$INSERT_Transfer_Transactions_Query = "INSERT INTO transfer_transactions (transaction_id,sender_account_id,receiver_account_id,amount,currency,description)
VALUES ($Transaction_id,$account_id,$transfer_account,$amount,'$currency','$description')";
}
$Transfer_Transactions_Result = pg_query($connection,$INSERT_Transfer_Transactions_Query);

// CREATE TABLE transfer_transactions (
//     transaction_id INT PRIMARY KEY REFERENCES transactions(id) ON DELETE CASCADE,
//     sender_account_id INT REFERENCES accounts(id) ON UPDATE CASCADE ON DELETE RESTRICT,
//     receiver_account_id INT REFERENCES accounts(id) ON UPDATE CASCADE ON DELETE RESTRICT,
//     external_bank_name VARCHAR(255),   -- başka bankaya EFT ise
//     amount NUMERIC(15,2) DEFAULT 0.00,
//     currency CHAR(3) DEFAULT 'TRY',
//     external_iban VARCHAR(34),
//     created_at TIMESTAMP DEFAULT now(),
//     description TEXT
// );

// CREATE TABLE transaction_approvals (
//     transaction_id INT PRIMARY KEY REFERENCES transactions(id) ON DELETE CASCADE,
// 	branch_id INT REFERENCES branches(id) ON UPDATE CASCADE ON DELETE SET NULL,
// 	trans_type INT REFERENCES transaction_types(id) ON UPDATE CASCADE ON DELETE SET NULL,
//     personel_id INT REFERENCES personels(user_id) ON UPDATE CASCADE,
// 	user_id INT REFERENCES users(id) ON UPDATE CASCADE,
//     status VARCHAR(20) DEFAULT 'PENDING', -- PENDING, APPROVED, REJECTED
//     assigned_at TIMESTAMP DEFAULT now(),
//     processed_at TIMESTAMP,
// 	CONSTRAINT uq_transaction_approval UNIQUE(transaction_id)
// );

// 1	"Müdür"
    // 2	"Müdür Yardımcısı"
    // 3	"Şube Müdürü"
    // 4	"Kredi Uzmanı"
    // 5	"Müşteri Temsilcisi"
    // 6	"Gişe Görevlisi"
    // 7	"Güvenlik Görevlisi"
    // 8	"Hizmetli"
    
    $Select_Personel_Query = "
    SELECT user_id 
    FROM personels 
    WHERE role_id IN (4,5,6)
    ORDER BY last_assigned ASC
    LIMIT 1
";
// şimdilik deneme için müdüre yolluyorum

$trans_type = "TRANSFER";

$Personel_Result = pg_query($connection, $Select_Personel_Query);
$Personel = pg_fetch_assoc($Personel_Result);
$personel_id = $Personel['user_id'];
    $Transaction_Approvals_Query = "INSERT INTO transaction_approvals (transaction_id,branch_id,trans_type,personel_id,user_id) VALUES ($Transaction_id,$branch_id,'$trans_type',$personel_id,$user_id)";
    $Transaction_Approvals_Result = pg_query($connection,$Transaction_Approvals_Query);

$Update_Last_Assigned = "
    UPDATE personels 
    SET last_assigned = NOW()
    WHERE user_id = $personel_id
";
pg_query($connection, $Update_Last_Assigned);

$_SESSION['Transfer_Success'] = "İşlem başarıyla oluşturuldu.";
    header('location: '.ROOT_URL . 'Systems/Client/TransferTransactions.php');
    die();
?>