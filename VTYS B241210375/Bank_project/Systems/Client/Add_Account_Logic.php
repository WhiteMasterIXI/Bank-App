<?php
include '../config/database.php';

// CREATE TABLE accounts(
// 	id SERIAL PRIMARY KEY,
// 	branch_id INT REFERENCES branches(id) ON UPDATE CASCADE ON DELETE RESTRICT,
// 	account_type_id INT REFERENCES account_types(id) ON UPDATE CASCADE ON DELETE RESTRICT,
// 	user_id INT REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
// 	balance NUMERIC(15,2) DEFAULT 0.00,
// 	currency CHAR(3) DEFAULT 'TRY',
// 	status VARCHAR(20) DEFAULT 'active',
// 	created_at TIMESTAMP DEFAULT now(),
// 	CONSTRAINT fk_account_currency FOREIGN KEY (currency) 
// 	    REFERENCES currency_rates(currency_code)
// 	    ON UPDATE CASCADE
// 	    ON DELETE RESTRICT
// );

if (isset($_POST['submit'])) {
    $account_type_id = filter_var($_POST['account_type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $currency = filter_var($_POST['currency'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$account_type_id) {
        $_SESSION['Account_Record_fail'] = "Hesap türü alınamadı.";
    } elseif (!$currency) {
        $_SESSION['Account_Record_fail'] = "Hesap kuru alınamadı.";
    }

    if (isset($_SESSION['Account_Record_fail'])) {
        header('location: ' . ROOT_URL . 'Systems/Client/Add_Account.php');
        die();
    }

    $user_id = $_SESSION['user_id'];

    $USER_Query = "SELECT branch_id FROM users WHERE id = $user_id";
    $User_Result = pg_query($connection, $USER_Query);
    $User = pg_fetch_assoc($User_Result);
    $Branch_id = $User['branch_id'];

    $Account_Count_Query = "SELECT COUNT(*) AS total FROM accounts WHERE user_id = $user_id";
    $Count_Result = pg_query($connection, $Account_Count_Query);
    $Count_fetch = pg_fetch_assoc($Count_Result);
    $Count = $Count_fetch['total'];

    if ($Count < 3) {
        $Create_Account_Query = "INSERT INTO accounts (branch_id,account_type_id,user_id,balance,currency) VALUES ($Branch_id,$account_type_id,$user_id,0,'$currency')";
        $Create_Acc_Result = pg_query($connection, $Create_Account_Query);
        $_SESSION['Account_Record_success'] = "Hesabınız başarıyla oluşturuldu.";
    } else {
        $_SESSION['Account_Record_fail'] = "Hata En fazla 3 hesap oluşturabilirsiniz.";
    }


}


header('location: ' . ROOT_URL . 'Systems/Client/Add_Account.php');
die();
