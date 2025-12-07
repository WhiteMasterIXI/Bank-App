<?php include '../partials/header.php';
require 'Setting_Permissions.php';
// Transfer tablosundan genel bilgileri çekme

// CREATE TABLE transfer_transactions (
// id SERIAL PRIMARY KEY,
// transaction_id INT UNIQUE REFERENCES transactions(id) ON DELETE CASCADE,
// sender_account_id INT REFERENCES accounts(id) ON UPDATE CASCADE ON DELETE RESTRICT,
// receiver_account_id INT REFERENCES accounts(id) ON UPDATE CASCADE ON DELETE RESTRICT,
// external_bank_name VARCHAR(255),   -- başka bankaya EFT ise
// external_iban VARCHAR(34),
// description TEXT

// CREATE TABLE transactions (
//     id SERIAL PRIMARY KEY,
//     user_id INT REFERENCES users(id) ON UPDATE CASCADE,               -- işlemi başlatan kullanıcı
//     branch_id INT REFERENCES branches(id) ON UPDATE CASCADE ON DELETE SET NULL,
//     trans_type VARCHAR(50) ON UPDATE CASCADE, -- işlem tipi (örn: EFT, payment)
//     account_id INT REFERENCES accounts(id) ON UPDATE CASCADE DEFAULT NULL,
//     amount NUMERIC(15,2) DEFAULT 0.00,                                -- genel tutar (bazı işlemler için boş kalabilir)
//     currency CHAR(3) DEFAULT 'TRY',
//     status VARCHAR(20) DEFAULT 'PENDING',                             -- PENDING, APPROVED, REJECTED
//     created_at TIMESTAMP DEFAULT now(),
//     approved_at TIMESTAMP
// );


// bu sorgu düzeltilecek transaction_approvals da bu kişiye ait olan
// ve transferle alakalı olanları çekeceğiz 
$user_id = $_SESSION['user_id'];


$Personal_Transfer_Transactions_Query =
    "SELECT t.id AS transaction_id,
t.user_id,
t.amount,
t.currency,
t.created_at,
u.name,
u.surname
 FROM transaction_approvals ta 
 JOIN transactions t ON t.id = ta.transaction_id
 JOIN transfer_transactions tt ON tt.transaction_id = ta.transaction_id
 JOIN users u ON u.id = t.user_id
 WHERE ta.trans_type = 'TRANSFER' AND personel_id = $user_id AND ta.status = 'PENDING' ";

$PTTQ_Result = pg_query($connection, $Personal_Transfer_Transactions_Query);


// sağda listelenecek işlemi seçiyor
if (isset($_POST['transaction_id']))
    $_SESSION['transaction_id'] = $_POST['transaction_id'];
?>

<div class="ManagementPanel">
    <!-- Sol Menü -->
    <aside class="Sidebar">
        <div class="card_informations center">
            <h5>Havale İşlemleri</h5>
        </div>
        <ul>
            <?php if (in_array(3, $user_permissions)) : // Transfer işlemleri yetkisi 
            ?>
                <li><a href="Transfer.php"><i class="uil uil-exchange"></i> Havale işlemleri</a></li>
            <?php endif ?>

            <?php if (in_array(6, $user_permissions)) : // Kart yetkisi 
            ?>
                <li><a href="Cards.php"><i class="uil uil-transaction"></i> Kart işlemleri</a></li>
            <?php endif ?>

            <?php if (in_array(4, $user_permissions)) : // Personel yetkisi 
            ?>
                <li><a href="Personel_Management.php"><i class="uil uil-users-alt"></i> Personel İşlemleri</a></li>
            <?php endif ?>

            <?php if (in_array(1, $user_permissions)) : // Log/Rapor yetkisi 
            ?>
                <li><a href="Reports.php"><i class="uil uil-presentation"></i> Raporlar</a></li>
            <?php endif ?>
        </ul>
    </aside>
    <!-- Orta Kısım: İşlem Listesi -->
    <div class="Transaction_Grid">
        <div class="Main__Section">
            <h5>Bekleyen İşlemler</h5>
            <div class="Transaction__List">
                <?php while ($transfer = pg_fetch_assoc($PTTQ_Result)) :
                    // transaction_id,
                    // t.user_id,
                    // t.amount,
                    // t.currency,
                    // t.created_at,
                    // u.name,
                    // u.surname
                ?>
                    <form action="Transfer.php" method="POST">
                        <button class="Transaction__Item">
                            <?php
                            $simge = '₺';
                            if ($transfer['currency'] == "TRY") {
                                $simge = '₺';
                            } elseif ($transfer['currency'] == "EUR") {
                                $simge = '€';
                            } elseif ($transfer['currency'] == "USD") {
                                $simge = '$';
                            }
                            ?>
                            <input type="hidden" name="transaction_id" value="<?= $transfer['transaction_id'] ?>">
                            <p><strong>İşlem No:</strong> <?= $transfer['transaction_id'] ?></p>
                            <p>Müşteri: <?= $transfer['name'] . ' ' . $transfer['surname'] ?> </p>
                            <p>Tutar: <?= $transfer['amount'] . ' ' . $simge ?> </p>
                        </button>
                    </form>
                <?php endwhile ?>
            </div>
        </div>

        <!-- Sağ Kısım: Detay -->
        <div class="Detail__Section" id="detailSection">
            <h5>İşlem Detayı</h5>
            <div class="Detail__Content">
                <!-- Dinamik içerik geleceği yer (PHP ile doldurulacak) -->
                <?php if (isset($_SESSION['transaction_id'])): ?>
                    <?php
                    $id = $_SESSION['transaction_id'];
                    $query = "SELECT * FROM transfer_transactions WHERE transaction_id = $id";
                    $result = pg_query($connection, $query);
                    $detail = pg_fetch_assoc($result);

                    $account_id = $detail['sender_account_id'];

                    $SELECT_USER_Query = "SELECT u.name,u.surname FROM users u JOIN accounts a ON a.id = $account_id AND a.user_id = u.id";
                    $user_result = pg_query($connection, $SELECT_USER_Query);
                    $user = pg_fetch_assoc($user_result);
                    ?>

                    <form action="TransferM_Logic.php" method="POST" class="DetailForm">
                        <input type="hidden" name="transaction_id" value="<?= $id ?>">
                        <p><strong>İşlem No:</strong> <?= $id ?></p>
                        <p><strong>Müşteri:</strong> <?= $user['name'] . ' ' . $user['surname'] ?></p>
                        <p><strong>Tutar:</strong> <?= $detail['amount'] ?>₺</p>
                        <p><strong>Hesap No:</strong> <?= $detail['sender_account_id'] ?> </p>
                        <?php if ($detail['external_bank_name']) : ?>
                            <p><strong>Alıcı:</strong> <?= $detail['external_bank_name'] ?> </p>
                            <p><strong>Iban:</strong> <?= $detail['external_iban'] ?> </p>
                            <p></p>
                        <?php else : ?>
                            <p><strong>Gönderilen Hesap:</strong> <?= $detail['receiver_account_id'] ?> </p>
                        <?php endif ?>
                        <p><strong>Açıklama:</strong> <?= $detail['description'] ?></p>
                        <div class="DetailButtons">
                            <button type="submit" name="approve" class="ClientBtnUpdate">Onayla</button>
                            <button type="submit" name="reject" class="ClientBtnDelete">Reddet</button>
                            <!-- Daha ileriye götürmek istersen arkada php de gerçekten gönderebiliriz sadece onay red yapacağım
                             zaten yeterince detaylı bir proje oldu -->
                        </div>
                    </form>

                <?php else: ?>
                    <p>Henüz bir işlem seçilmedi.</p>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>


<?php include '../partials/footer.php'; ?>