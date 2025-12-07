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

// 1 bağlantı yetiyor kişiye bağladık zaten ayarladım önceden mantığını
$user_id = $_SESSION['user_id'];
$Select_Cards_Query = "SELECT id,card_type_id FROM cards WHERE user_id = $user_id";
$Select_Cards_Result = pg_query($connection, $Select_Cards_Query);
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
            <?php if (isset($_SESSION['Card_Transaction_fail'])) : ?>
                <p><?= $_SESSION['Card_Transaction_fail'];
                    unset($_SESSION['Card_Transaction_fail']); ?></p>
            <?php elseif (isset($_SESSION['Card_Transaction_success'])) : ?>
                <p><?= $_SESSION['Card_Transaction_success'];
                    unset($_SESSION['Card_Transaction_success']); ?></p>
            <?php endif ?>
            <div class="card_informations">
                <h5>Kart İşlemleri</h5>
                <a href="Add_Card.php">+ Kart Ekle</a>
            </div>
            <form class="DetailContent" action="Card_Transactions_Logic.php" method="POST">
                <?php if (pg_num_rows($Select_Cards_Result) > 0) : ?>
                    <div class="CardActions">
                        <label for="action"><strong>Kartlarım</strong></label>
                        <select name="card_id">
                            <?php while ($card = pg_fetch_assoc($Select_Cards_Result)) : ?>
                                <?php
                                // 1	"banka"
                                // 2	"kredi"
                                // 3	"hibrit"
                                // 4	"sanalkart"
                                $type = "Banka";
                                if ($card['card_type_id'] == 1) {
                                    $type = "Banka";
                                } else if ($card['card_type_id'] == 2) {
                                    $type = "Kredi";
                                } else if ($card['card_type_id'] == 3) {
                                    $type = "Hibrit";
                                } else if ($card['card_type_id'] == 4) {
                                    $type = "Sanal";
                                } else {
                                    $type = "Bilinmeyen Kart";
                                }
                                ?>
                                <option value="<?= $card['id'] ?>">Kart_<?= $card['id'] . ' ' . $type ?></option>
                            <?php endwhile ?>
                        </select>
                        <label for="action"><strong>Yapılacak İşlem:</strong></label>
                        <select name="action">
                            <option value="CARD_FREEZE">Dondur</option>
                            <option value="CARD_ACTIVATE">Aktif Et</option>
                            <option value="CARD_UPDATE">Limit Değiştir</option>
                            <option value="CARD_DELETE">Kapat</option>
                        </select>

                        <div class="payment_options">
                            <label for="description"><strong>Kart Limiti:</strong></label>
                            <select name="limit" id="">
                                <option value="20000">20.000</option>
                                <option value="30000">30.000</option>
                                <option value="40000">40.000</option>
                                <option value="50000">50.000</option>
                            </select>
                        </div>

                        <label for="description"><strong>Açıklama:</strong></label>
                        <textarea name="description" rows="3" placeholder="Opsiyonel açıklama..."></textarea>
                    </div>

                    <div class="DetailButtons">
                        <button name="submit" type="submit" class="ClientBtnUpdate">Uygula</button>
                        <button type="reset" class="ClientBtnDelete">Temizle</button>
                    </div>
                <?php else : ?>
                    <p>Hiç bir kartınız bulunamamıştır işlem yapmak için kart talebinde bulununuz.</p>
                <?php endif ?>
            </form>
        </div>
    </div>
</div>


<?php include '../partials/footer.php'; ?>