<?php include '../partials/header.php';
require 'Setting_Permissions.php';
// Transfer tablosundan genel bilgileri çekme

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
 JOIN card_transactions ct ON ct.transaction_id = ta.transaction_id
 JOIN users u ON u.id = t.user_id
 WHERE ta.trans_type LIKE '%CARD%' AND personel_id = $user_id AND ta.status = 'PENDING'";

$PTTQ_Result = pg_query($connection, $Personal_Transfer_Transactions_Query);


if (isset($_POST['transaction_id']))
    $_SESSION['card_transaction_id'] = $_POST['transaction_id'];

?>

<div class="ManagementPanel">
    <!-- Sol Menü -->
    <aside class="Sidebar">
        <div class="card_informations center">
            <h5>Kart İşlemleri</h5>
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

                    $simge = '₺';
                    if ($transfer['currency'] == "TRY") {
                        $simge = '₺';
                    } elseif ($transfer['currency'] == "EUR") {
                        $simge = '€';
                    } elseif ($transfer['currency'] == "USD") {
                        $simge = '$';
                    }
                ?>
                    <form action="Cards.php" method="POST">
                        <button class="Transaction__Item">
                            <input type="hidden" name="transaction_id" value="<?= $transfer['transaction_id'] ?>">
                            <p><strong>İşlem No:</strong> <?= $transfer['transaction_id'] ?></p>
                            <p>Müşteri: <?= $transfer['name'] . ' ' . $transfer['surname'] ?> </p>
                            <?php if (isset($transfer['amount'])) : ?>
                                <p>Limit İsteği: <?= $transfer['amount'] . ' ' . $simge ?> </p>
                            <?php endif ?>
                        </button>
                    </form>
                <?php endwhile ?>
            </div>
        </div>

        <!-- Sağ Kısım: Detay -->
        <div class="Detail__Section" id="detailSection">
            <?php if (isset($_SESSION['Card_Transaction_fail'])) : ?>
                <p><?= $_SESSION['Card_Transaction_fail'];
                    unset($_SESSION['Card_Transaction_fail']); ?></p>
            <?php elseif (isset($_SESSION['Card_Transaction_success'])) : ?>
                <p><?= $_SESSION['Card_Transaction_success'];
                    unset($_SESSION['Card_Transaction_success']); ?></p>
            <?php endif ?>
            <h5>İşlem Detayı</h5>
            <div class="Detail__Content">
                <!-- Dinamik içerik geleceği yer (PHP ile doldurulacak) -->
                <?php if (isset($_SESSION['card_transaction_id'])): ?>
                    <?php
                    $id = $_SESSION['card_transaction_id'];
                    $query = "SELECT action ,new_limit,description,card_id  FROM card_transactions WHERE transaction_id = $id";
                    $result = pg_query($connection, $query);
                    $detail = pg_fetch_assoc($result);

                    $SELECT_USER_Query = "SELECT u.name,u.surname FROM users u JOIN transactions a ON a.id = $id AND a.user_id = u.id";
                    $user_result = pg_query($connection, $SELECT_USER_Query);
                    $user = pg_fetch_assoc($user_result);

                    $card_transaction;
                    $transaction_no;
                    if ($detail['action'] == "CARD_CREATE") {
                        $card_transaction = "Create";
                        $transaction_no = 1;
                    } elseif ($detail['action'] == "CARD_UPDATE") {
                        $card_transaction = "Update";
                        $transaction_no = 2;
                    }

                    ?>

                    <form action="Card_Transaction_Logic.php" method="POST" class="DetailForm">
                        <input type="hidden" name="transaction_id" value="<?= $id ?>">
                        <input type="hidden" name="Card_Trans_Type" value="<?= $transaction_no ?>">
                        <p><strong>İşlem: </strong><?= $card_transaction ?></p><!-- Burada decode yapabiliriz yapcam sonra -->
                        <p><strong>İşlem No:</strong> <?= $id ?></p>
                        <p><strong>Müşteri:</strong> <?= $user['name'] . ' ' . $user['surname'] ?></p>
                        <?php if (isset($detail['new_limit'])) : ?>
                            <p><strong>Kart Limiti:</strong> <?= $detail['new_limit'] ?>₺</p>
                        <?php endif ?>
                        <?php if (isset($detail['card_id'])) : ?>
                            <p><strong>Kredi Numarası:</strong> <?= $detail['card_id'] ?> </p>
                        <?php endif ?>
                        <?php if (isset($detail['description'])) : ?>
                            <p><strong>Açıklama:</strong> <?= $detail['description'] ?></p>
                        <?php endif ?>
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