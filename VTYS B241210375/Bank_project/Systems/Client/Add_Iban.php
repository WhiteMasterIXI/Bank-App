<?php include '../partials/header.php';

// Transfer tablosundan genel bilgileri çekme

// CREATE TABLE iban_address (
//  id SERIAL PRIMARY KEY,
//   iban VARCHAR(50) UNIQUE NOT NULL,
//   time TIMESTAMP DEFAULT now(),
//    description TEXT DEFAULT NULL);

$Query = "SELECT t.* FROM transactions t JOIN transfer_transactions tr ON tr.transaction_id = t.id;";
$Query_Result = pg_query($connection, $Query);
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
                <h5>Iban Ekle</h5>
            </div>
            <p><?php if(isset($_SESSION['Iban_record_fail'])){echo $_SESSION['Iban_record_fail'] ; unset($_SESSION['Iban_record_fail']);} ?></p>
            <form action="Iban_Logic.php" class="DetailContent" method="POST">
                <div class="CardActions">
                    <div class="LimitInput">
                        <label for="new_limit"><strong>Iban Adresi</strong></label>
                        <input name="iban" type="text">
                    </div>

                    <label for="description"><strong>Açıklama:</strong></label>
                    <textarea name="description" id="description" rows="3" placeholder="Opsiyonel açıklama..."></textarea>
                </div>

                <div class="DetailButtons">
                    <button name="submit" type="submit" class="ClientBtnUpdate">Ekle</button>
                    <button type="reset" class="ClientBtnDelete">Temizle</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php include '../partials/footer.php'; ?>