<?php
include '../config/database.php';

if (isset($_POST['submit'])) {
    $account_id = filter_var($_POST['account_id'], FILTER_SANITIZE_NUMBER_INT);
    $card_type_id = filter_var($_POST['card_type'], FILTER_SANITIZE_NUMBER_INT);
    $card_limit = filter_var($_POST['Limit'], FILTER_SANITIZE_NUMBER_INT);

    if (!$account_id) {
        $_SESSION['Card_Record_fail'] = "Hesabınızı seçmeyi unutmayınız.";
    } else if (!$card_type_id) {
        $_SESSION['Card_Record_fail'] = "Kartınızı seçmeyi unutmayınız.";
    } else if (!$card_limit) {
        $_SESSION['Card_Record_fail'] = "Limitinizi seçmeyi unutmayınız.";
    }

    // burada banka hesabına sadece banka kartı kredi hesabına sadece kredi 
    //hibritte ise hepsi olabilecek şekilde yerleştiriyoruz 
    // 1 Banka 2 Kredi 3 Hibrit 4 Sanal Hesap Türleri : 1 Banka 2 Kredi 3 Hibrit

    $Select_acc_type = "SELECT account_type_id FROM accounts WHERE id = $account_id ";
    $Select_Result = pg_query($connection, $Select_acc_type);
    $Select_fetch = pg_fetch_assoc($Select_Result);
    $acc_type_id = $Select_fetch['account_type_id'];

    if ($acc_type_id == 1 && !($card_type_id == 1 || $card_type_id == 4)) {
        $_SESSION['Card_Record_fail'] = "Banka Hesabınıza uygun bir kart seçiniz.";
    } else if ($acc_type_id == 2 && !($card_type_id == 2 || $card_type_id == 4)) {
        $_SESSION['Card_Record_fail'] = "Kredi Hesabınıza uygun bir kart seçiniz.";
    }

    // 1 Banka 2 Kredi 3 Hibrit 4 Sanal
    $Select_Credit_Card_Count = "SELECT COUNT(*) AS total FROM cards WHERE id = $card_type_id account_id = $account_id";
    $Card_Count_Result = pg_query($connection, $Select_Credit_Card_Count);
    $Card_Count = $Card_Count_Result['total'];

    // en fazla 1 banka , 3 kredi , 3 hibrit, 2 sanal tanımlama imkanı veriyorum.
    // hızlandırmak için bu banka türlerine indexer koyabiliriz de mantıklı değil çok fazla
    // üretiliyor 

    switch ($card_type_id) {
        case 1:
            if ($Card_Count >= 1) {
                $_SESSION['Card_Record_fail'] = "Maksimum 1 banka kartı tanımlayabilirsiniz.";
            }
            break;
        case 2:
            if ($Card_Count >= 3) {
                $_SESSION['Card_Record_fail'] = "Maksimum 3 Kredi kartı tanımlayabilirsiniz.";
            }
            break;
        case 3:
            if ($Card_Count >= 3) {
                $_SESSION['Card_Record_fail'] = "Maksimum 3 Hybrid kart tanımlayabilirsiniz.";
            }
            break;
        case 4:
            if ($Card_Count >= 2) {
                $_SESSION['Card_Record_fail'] = "Maksimum 2 Sanal kart tanımlayabilirsiniz.";
            }
            break;
        default:
    }


    // banka hesabıysa kişi limit girse bile 0 olur karttaki limit para yükleyince artar

    if ($card_type_id == 1) {
        $card_limit = 0;
    }

    if (isset($_SESSION['Card_Record_fail'])) {
        header('location: ' . ROOT_URL . 'Systems/Client/Add_Card.php');
        die();
    }
    //     CREATE TABLE transactions (
    //     id SERIAL PRIMARY KEY,
    //     user_id INT REFERENCES users(id) ON UPDATE CASCADE,               -- işlemi başlatan kullanıcı
    //     branch_id INT REFERENCES branches(id) ON UPDATE CASCADE ON DELETE SET NULL,
    //     trans_type INT REFERENCES transaction_types(id) ON UPDATE CASCADE, -- işlem tipi (örn: EFT, payment)
    //     account_id INT REFERENCES accounts(id) ON UPDATE CASCADE DEFAULT NULL,
    //     amount NUMERIC(15,2) DEFAULT 0.00,                                -- genel tutar (bazı işlemler için boş kalabilir)
    //     currency CHAR(3) DEFAULT 'TRY',
    //     status VARCHAR(20) DEFAULT 'PENDING',                             -- PENDING, APPROVED, REJECTED
    //     created_at TIMESTAMP DEFAULT now(),
    //     approved_at TIMESTAMP
    // );

    $user_id = $_SESSION['user_id'];

    $User_Query = "SELECT branch_id FROM users WHERE id = $user_id LIMIT 1";
    $User_Result = pg_query($connection, $User_Query);
    $User_fetch = pg_fetch_assoc($User_Result);
    $Branch_id = $User_fetch['branch_id'];

    $Transaction_Type = 'CARD_CREATE';

    $Transaction_Query = "INSERT INTO transactions (user_id,branch_id,trans_type,account_id,amount) VALUES ($user_id,$Branch_id,'$Transaction_Type',$account_id,$card_limit) RETURNING id";
    $Transaction_Result = pg_query($connection, $Transaction_Query);
    $Transaction_fetch = pg_fetch_assoc($Transaction_Result);
    $transaction_id = $Transaction_fetch['id'];
    //     CREATE TABLE card_transactions 
    //     transaction_id INT PRIMARY KEY REFERENCES transactions(id) ON DELETE CASCADE,
    //     card_id INT REFERENCES cards(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    //     action VARCHAR(50),              -- create, close, freeze, unfreeze, limit_change
    //     new_limit NUMERIC(15,2),
    //     description TEXT
    $Card_Transaction_Query = "INSERT INTO card_transactions (transaction_id,card_type_id,action,new_limit) VALUES ($transaction_id,$card_type_id,'$Transaction_Type',$card_limit)";
    $Card_Transaction_Result = pg_query($connection, $Card_Transaction_Query);

    $_SESSION['Card_Record_success'] = "Kart işleminiz başarıyla yapılmıştır.";

    //     transaction_approvals a göndericez yetkili personel de ordan onaylayacak
    //     CREATE TABLE transaction_approvals (
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
    // ALTER TABLE transaction_approvals ALTER COLUMN trans_type TYPE VARCHAR(50);
    // eşit dağıtım için en son işlem yapma zamanına göre personeli çekmemiz gerek yetkili olanı
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
    WHERE role_id = 5
    ORDER BY last_assigned ASC
    LIMIT 1
";

    $Personel_Result = pg_query($connection, $Select_Personel_Query);
    $Personel = pg_fetch_assoc($Personel_Result);
    $personel_id = $Personel['user_id'];

    $Transaction_Approvals_Query = "INSERT INTO transaction_approvals (transaction_id,trans_type,personel_id,user_id) VALUES ($transaction_id,'$Transaction_Type',$personel_id,$user_id)";
    $Transaction_Approvals_Result = pg_query($connection, $Transaction_Approvals_Query);

    $Update_Last_Assigned = "
    UPDATE personels 
    SET last_assigned = NOW()
    WHERE user_id = $personel_id
";
    pg_query($connection, $Update_Last_Assigned);
}

header('location: ' . ROOT_URL . 'Systems/Client/Add_Card.php');
die();
