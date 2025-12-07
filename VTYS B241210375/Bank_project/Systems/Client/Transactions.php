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
$Select_User_Card_Query = "SELECT c.id,c.account_id,c.card_type_id,c.card_number, u.name,u.surname,c.card_limit,c.card_available_limit FROM cards c JOIN users u ON u.id = c.user_id WHERE c.user_id = $user_id";
$Card_Result = pg_query($connection, $Select_User_Card_Query);

$Account_Select_Query = "SELECT id,balance,currency,status,created_at,account_type_id FROM accounts WHERE user_id = $user_id";
$Account_Result = pg_query($connection, $Account_Select_Query);

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
            <h5>Kartlarım ve Hesaplarım</h5>
            <?php if (pg_num_rows($Card_Result) > 0) : ?>
                <div class="Card_Sections">

                    <?php while ($card = pg_fetch_assoc($Card_Result)) : ?>
                        <form name="card_form" action="User_Cards_Informations.php" method="POST">
                            <input type="hidden" name="card_id" value="<?= $card['id'] ?>">
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
                            <button style="--card_background:<?= $color ?>" class="Credit_Card">
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
                            </button>
                            <?php
                            $percentages;
                            if ($card['card_type_id'] == 1)
                                $percentages = 100;
                            else {
                                $percentages = ($card['card_available_limit'] / $card['card_limit']) * 100;
                            }

                            ?>
                            <div class="progress_bar">
                                <div class="progress">
                                    <div style="--progress: <?= $percentages ?>%; --progress_color: <?= $color ?> " class="inside"><?= $card['card_available_limit'] ?></div>
                                </div>
                            </div>
                        </form>
                    <?php endwhile ?>

                </div>
            <?php else : ?>
                <div class="DetailContent">
                    <div class="CardActions">
                        <p>Henüz bir kart oluşturmamışsınız.</p>
                    </div>
                </div>
            <?php endif ?>
            <?php if (pg_num_rows($Account_Result) > 0) : ?>
                <div class="Card_Sections">
                    <?php while ($account = pg_fetch_assoc($Account_Result)) : ?>
                        <div name="card_form" action="User_Cards_Informations.php" method="POST">
                            <?php
                            $color;
                            if ($account['account_type_id'] == 1) {
                                $color = 'rgb(56, 154, 245)';
                            } elseif ($account['account_type_id'] == 2) {
                                $color = 'rgba(68, 221, 81, 1)';
                            } elseif ($account['account_type_id'] == 3) {
                                $color = 'rgba(169, 74, 207, 1)';
                            } else {
                                $color = 'rgba(247, 171, 72, 1)';
                            }
                            ?>
                            <div style="--card_background:<?= $color ?>" class="Credit_Card">
                                <div class="bank_nameside">
                                    <p>Account_<?= $account['id'] ?></p>
                                </div>
                                <div class="bank_infoside">
                                    <div class="top">
                                        <div class="account_owner"><?= $account['balance'] . ' ' . $account['currency'] ?></div>
                                        <label><?= date('d.m.Y', strtotime($account['created_at'])) ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile ?>
                </div>
            <?php else : ?>
                <div class="DetailContent">
                    <div class="CardActions">
                        <p>Henüz bir Hesap oluşturmamışsınız.</p>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>


<?php include '../partials/footer.php'; ?>