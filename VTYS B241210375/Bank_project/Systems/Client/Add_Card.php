<?php include '../partials/header.php';

// CREATE TABLE cards(
// 	id SERIAL PRIMARY KEY,
// 	branch_id INT REFERENCES branches(id) ON UPDATE CASCADE ON DELETE SET NULL,
// 	user_id INT REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
// 	account_id INT REFERENCES accounts(id) ON UPDATE CASCADE ON DELETE RESTRICT,
// 	card_type_id INT REFERENCES card_types(id) ON UPDATE CASCADE ON DELETE RESTRICT,
// 	card_limit NUMERIC(15,2) DEFAULT 0.00,
// 	status VARCHAR(20) DEFAULT 'active',
// 	card_available_limit NUMERIC(15,2) DEFAULT 0.00,
// 	card_number CHAR(16) UNIQUE NOT NULL
// );

$user_id = $_SESSION['user_id'];
$Query = "SELECT * FROM accounts WHERE user_id = $user_id";
$Query_result = pg_query($connection, $Query);



$Card_Query = "SELECT id,type FROM card_types";
$Card_Result = pg_query($connection, $Card_Query);

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
                <h5>Kart Ekle</h5>
            </div>
            <?php if (isset($_SESSION['Card_Record_fail'])) : ?>
                <p><?= $_SESSION['Card_Record_fail'];
                    unset($_SESSION['Card_Record_fail']); ?></p>
            <?php elseif (isset($_SESSION['Card_Record_success'])) : ?>
                <p><?= $_SESSION['Card_Record_success'];
                    unset($_SESSION['Card_Record_success']); ?></p>
            <?php endif ?>
            <form action="Add_Card_Logic.php" class="DetailContent" method="POST">
                <div class="CardActions">
                    <div class="LimitInput">
                        <label for="new_limit"><strong>Hesap</strong></label>
                        <select name="account_id">
                            <?php while ($Account = pg_fetch_assoc($Query_result)) :
                                $acc_id = $Account['account_type_id'];
                                $Acc_type = "SELECT * FROM account_types WHERE id = $acc_id";
                                $Acc_type_result = pg_query($connection, $Acc_type);
                                $Acc_type_fetch = pg_fetch_assoc($Acc_type_result);
                                $Acc_type_name = $Acc_type_fetch['type']
                            ?>
                                <option value="<?= $Account['id'] ?>">Account_<?= $Account['id'] . ' ' . $Acc_type_name  ?></option>
                            <?php endwhile ?>
                        </select>
                    </div>
                    <div class="payment_info">
                        <div class="payment_options">
                            <label for="new_limit"><strong>Kart Seçimi</strong></label>
                            <select name="card_type">
                                <?php while ($card = pg_fetch_assoc($Card_Result)) : ?>
                                    <option value="<?= $card['id'] ?>"><?= $card['type'] ?></option>
                                <?php endwhile ?>
                            </select>
                        </div>
                        <div class="payment_options">
                            <div class="information">
                                <label for="description"><strong>Kart Limiti</strong></label>
                                <label for="description">Kredi için</label>
                            </div>
                            <select name="Limit" id="">
                                <option value="20000">20.000</option>
                                <option value="30000">30.000</option>
                                <option value="40000">40.000</option>
                                <option value="50000">50.000</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="DetailButtons">
                    <button name="submit" type="submit" class="ClientBtnUpdate">Oluştur</button>
                    <button type="reset" class="ClientBtnDelete">Temizle</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php include '../partials/footer.php'; ?>