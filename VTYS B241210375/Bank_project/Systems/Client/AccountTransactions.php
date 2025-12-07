<?php include '../partials/header.php';

// Transfer tablosundan genel bilgileri çekme

// CREATE TABLE transfer_transactions (
// id SERIAL PRIMARY KEY,
// transaction_id INT UNIQUE REFERENCES transactions(id) ON DELETE CASCADE,
// sender_account_id INT REFERENCES accounts(id) ON UPDATE CASCADE ON DELETE RESTRICT,
// receiver_account_id INT REFERENCES accounts(id) ON UPDATE CASCADE ON DELETE RESTRICT,
// external_bank_name VARCHAR(255),   -- başka bankaya EFT ise
// external_iban VARCHAR(34),
// description TEXT

$Account_Query = "SELECT id FROM accounts WHERE user_id = $user_id";
$Account_Result = pg_query($connection, $Account_Query);
?>

<div class="ManagementPanel">
    <!-- Orta Kısım: İşlem Listesi -->
    <div class="Transaction_Grid_Client">
        <div class="Sidebar">
            <ul>
                <li><a href="Transactions.php">🏛️ Ana Sayfa</a></li>
                <li><a href="TransferTransactions.php">🔁 Havale işlemleri</a></li>
                <li><a href="CardTransactions.php">💳 Kart işlemleri</a></li>
                <li><a href="AccountTransactions.php">🧾 Hesap işlemleri</a></li>
                <li><a href="PaymentTransactions.php">🕘 İşlem Kayıtları</a></li>
            </ul>
        </div>

        <!-- Sağ Kısım: Detay -->
        <div class="ClientDetailSection">
            <div class="card_informations">
                <h5>Hesap İşlemleri</h5>
                <a href="Add_Account.php">+ Hesap Ekle</a>
            </div>
            <?php if (isset($_SESSION['Account_Transaction_fail'])) : ?>
                <p><?= $_SESSION['Account_Transaction_fail'];
                    unset($_SESSION['Account_Transaction_fail']); ?></p>
            <?php elseif (isset($_SESSION['Account_Transaction_success'])) : ?>
                <p><?= $_SESSION['Account_Transaction_success'];
                    unset($_SESSION['Account_Transaction_success']); ?></p>
            <?php endif ?>
            <?php if (pg_num_rows($Account_Result) > 0) : ?>
                <form action="AccountTransactions_Logic.php" class="DetailContent" method="POST">
                    <div class="CardActions">
                        <label for="action"><strong>Hesaplarım</strong></label>
                        <select name="account_id">
                            <?php while ($acc = pg_fetch_assoc($Account_Result)) : ?>
                                <option value="<?= $acc['id'] ?>">Hesap_<?= $acc['id'] ?></option>
                            <?php endwhile ?>
                        </select>
                        <label for="action"><strong>Yapılacak İşlem:</strong></label>
                        <select name="action">
                            <option value="ACCOUNT_FREEZE">Dondur</option>
                            <option value="ACCOUNT_ACTIVATE">Aktif Et</option>
                            <option value="ACCOUNT_DELETE">Kapat</option>
                        </select>

                        <label for="description"><strong>Açıklama:</strong></label>
                        <textarea name="description" rows="3" placeholder="Opsiyonel açıklama..."></textarea>
                    </div>

                    <div class="DetailButtons">
                        <button name="submit" class="ClientBtnUpdate">Uygula</button>
                        <button type="reset" class="ClientBtnDelete">Temizle</button>
                    </div>
                </form>
            <?php else : ?>
                <div class="DetailContent">
                    <div class="CardActions">
                        <p>Henüz bir hesap oluşturmamışsınız.</p>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>


<?php include '../partials/footer.php'; ?>