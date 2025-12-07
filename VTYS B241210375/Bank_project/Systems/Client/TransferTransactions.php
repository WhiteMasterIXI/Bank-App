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
$user_id = $_SESSION['user_id'];

$Account_Query = "SELECT id FROM accounts WHERE user_id = $user_id";
$Account_Result = pg_query($connection, $Account_Query);

$accounts = [];
while ($acc = pg_fetch_assoc($Account_Result)) {
    $accounts[] = $acc;
}

$Iban_Query = "SELECT iban FROM iban_address WHERE user_id = $user_id";
$Iban_Result = pg_query($connection, $Iban_Query);
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

        <!-- Sağ Kısım: Detay  2 tane yer olacak dışarıya ise text girer yoksa seçenekler gelir -->
        <div class="ClientDetailSection">
            <div class="card_informations">
                <h5>Havale İşlemleri</h5>
                <a href="Add_Account.php">+ Hesap Ekle</a>
            </div>
            <?php if (isset($_SESSION['Transfer_Fail'])) : ?>
                <p><?= $_SESSION['Transfer_Fail'];
                    unset($_SESSION['Transfer_Fail']); ?></p>
            <?php elseif (isset($_SESSION['Transfer_Success'])) : ?>
                <p><?= $_SESSION['Transfer_Success'];
                    unset($_SESSION['Transfer_Success']); ?></p>
            <?php endif ?>
            <?php if (pg_num_rows($Account_Result) > 0) : ?>
                <form class="DetailContent" action="Transfer_Logic.php" method="POST">
                    <div class="CardActions">
                        <div class="payment_info">
                            <div class="payment_options">
                                <label for="action"><strong>Hesaplarım</strong></label>
                                <select name="account" id="account">
                                    <?php foreach ($accounts as $acc) : ?>
                                        <option value="<?= $acc['id'] ?>">Hesap_<?= $acc['id'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="payment_options">
                                <div class="card_informations">
                                    <label for="description"><strong>Ödenecek Taraf:</strong></label>
                                    <a href="Add_Iban.php">+ Iban Ekle</a>
                                </div>
                                <select name="Payee">
                                    <?php foreach ($accounts as $acc) : ?>
                                        <option value="<?= $acc['id'] ?>">Hesap_<?= $acc['id'] ?></option>
                                    <?php endforeach; ?>
                                    <?php while ($iban = pg_fetch_assoc($Iban_Result)) : ?>
                                        <option value="<?= $iban['iban'] ?>"><?= $iban['iban'] ?></option>
                                    <?php endwhile ?>
                                </select>
                            </div>

                        </div>
                        <!-- bununla descriptionu birleştirip descriptiona yazarız  -->
                        <div class="payment_info">
                            <div class="payment_options">
                                <label for="action"><strong>Transfer Türü</strong></label>
                                <select name="Transfer_type" id="account">
                                    <option value="Faturalar">Eğitim</option>
                                    <option value="Kredi borcu">Fatura</option>
                                    <option value="Kurumsal borç">Kira</option>
                                    <option value="Abonelik">Aidat</option>
                                    <option value="Bağış">Bağış</option>
                                    <option value="Yemek">Yemek</option>
                                    <option value="Ulaşım">Ulaşım</option>
                                </select>
                            </div>
                            <div class="payment_options">
                                <label for="Cash"><strong>Ödeme Miktar</strong></label>
                                <input name="amount" type="number" min="0">
                            </div>
                        </div>

                        <label for="description"><strong>Açıklama:</strong></label>
                        <textarea name="description" rows="3" placeholder="Opsiyonel açıklama..."></textarea>
                    </div>

                    <div class="DetailButtons">
                        <button type="submit" class="ClientBtnUpdate">Gönder</button>
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