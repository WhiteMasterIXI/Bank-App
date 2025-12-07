<?php include '../partials/header.php';

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
$Query = "SELECT * FROM account_types";
$Query_result = pg_query($connection, $Query);



$Currency_Query = "SELECT currency_code FROM currency_rates";
$Currency_Result = pg_query($connection, $Currency_Query);
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
                <h5>Hesap Ekle</h5>
            </div>
            <?php if (isset($_SESSION['Account_Record_fail'])) : ?>
                <p><?= $_SESSION['Account_Record_fail'];
                    unset($_SESSION['Account_Record_fail']); ?></p>
            <?php elseif (isset($_SESSION['Account_Record_success'])) : ?>
                <p><?= $_SESSION['Account_Record_success'];
                    unset($_SESSION['Account_Record_success']); ?></p>
            <?php endif ?>
            <form action="Add_Account_Logic.php" class="DetailContent" method="POST">
                <div class="CardActions">
                    <div class="LimitInput">
                        <label for="new_limit"><strong>Hesap Türü</strong></label>
                        <select name="account_type">
                            <?php while ($account_type = pg_fetch_assoc($Query_result)) : ?>
                                <option value="<?= $account_type['id'] ?>"><?= $account_type['type']  ?></option>
                            <?php endwhile ?>
                        </select>
                    </div>
                    <div class="LimitInput">
                        <label for="new_limit"><strong>Kur Seçimi</strong></label>
                        <select name="currency" id="">
                            <?php while ($currency = pg_fetch_assoc($Currency_Result)) : ?>
                                <option value="<?= $currency['currency_code'] ?>"><?= $currency['currency_code'] ?></option>
                            <?php endwhile ?>
                        </select>
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