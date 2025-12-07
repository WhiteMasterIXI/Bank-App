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


if(isset($_POST['card_id'])){
$_SESSION['card_id'] = $_POST['card_id'];
}
$card_id = $_SESSION['card_id'];
$Select_User_Card_Query = "SELECT c.id,c.account_id,c.card_limit,c.card_available_limit,c.card_type_id,c.card_number, u.name,u.surname FROM cards c JOIN users u ON u.id = c.user_id WHERE c.id = $card_id";
$Card_Result = pg_query($connection, $Select_User_Card_Query);
$card = pg_fetch_assoc($Card_Result);

$debt =  $card['card_limit'] - $card['card_available_limit'];



$Select_User_Account = "SELECT id FROM accounts WHERE user_id = $user_id";
$Account_Result = pg_query($connection, $Select_User_Account);

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
            <?php if (isset($_SESSION['Debit_Payment_fail'])) : ?>
                <p><?= $_SESSION['Debit_Payment_fail'];
                    unset($_SESSION['Debit_Payment_fail']); ?></p>
            <?php elseif (isset($_SESSION['Debit_Payment_success'])) : ?>
                <p><?= $_SESSION['Debit_Payment_success'];
                    unset($_SESSION['Debit_Payment_success']); ?></p>
            <?php endif ?>
            <h5>Kartlarım</h5>
            <div class="Card_Sections">
                <?php
                $color;
                if ($card['card_type_id'] == 1) {
                    $color = 'rgb(56, 154, 245)';
                } elseif ($card['card_type_id'] == 2) {
                    $color = 'rgba(68, 221, 81, 1)';
                } elseif ($card['card_type_id'] == 3) {
                    $color = 'rgba(169, 74, 207, 1)';
                } else {
                    $color = 'rgba(238, 221, 70, 1)';
                }
                ?>
                <div>
                <div style="--card_background:<?= $color ?>" class="Credit_Card">
                    <div class="bank_nameside">
                        <p>Bank</p>
                        <p><?= $card['account_id'] ?></p>
                    </div>
                    <div class="bank_infoside">
                        <div class="top">
                            <div class="account_owner"><?= $card['name'] . ' ' . $card['surname'] ?></div>
                            <label>03/29</label>
                        </div>
                        <div class="bottom">
                            <p><?= '●●●● ●●●● ●●●● ' . substr($card['card_number'], 12, 16)  ?></p>
                            <div class="card_type"><img src="../../images/card.png" alt=""></div>
                        </div>
                    </div>
                </div>
                                        <?php 
                        $percentages;
                            if($card['card_type_id'] == 1)
                                $percentages = 100;
                            else{
                               $percentages = ($card['card_available_limit'] / $card['card_limit']) * 100;
                            }
                                 
                        ?>
                        <div class="progress_bar"><div class="progress"><div style="--progress: <?= $percentages?>%; --progress_color: <?= $color ?> " class="inside"><?= $card['card_available_limit'] ?></div></div></div>
            </div>
                <form class="DetailContent" action="PayCardDebit_Logic.php" method="POST">
                    <input type="hidden" name="card_id" value="<?= $card_id ?>">
                    <div class="CardActions">
                        <div class="payment_info">
                            <div class="payment_options">
                                <label for="action"><strong>Hesaplarım</strong></label>
                                <select name="account" id="account">
                                    <?php while ($acc = pg_fetch_assoc($Account_Result)) : ?>
                                        <option value="<?= $acc['id'] ?>">Hesap_<?= $acc['id'] ?></option>
                                    <?php endwhile ?>
                                </select>
                            </div>
                            <div style="--debt_color:<?= $color ?>" class="Debt_amount">
                                <label><strong>Borcunuz:</strong></label>
                                <p><?= $debt ?></p>
                            </div>
                        </div>
                        <input type="hidden" name="debt" value="<?= $debt ?>">
                        <!-- bununla descriptionu birleştirip descriptiona yazarız  -->
                        <div class="payment_options">
                            <label for="Cash"><strong>Ödeme Miktar</strong></label>
                            <input name="amount" type="number" min="0" max="<?= $debt ?>">
                        </div>
                    </div>

                    <div class="DetailButtons">
                        <button name="submit" type="submit" class="ClientBtnUpdate">Yatır</button>
                        <button type="reset" class="ClientBtnDelete">Temizle</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>


<?php include '../partials/footer.php'; ?>