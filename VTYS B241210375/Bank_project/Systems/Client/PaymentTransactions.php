<?php include '../partials/header.php';

// CREATE TABLE debts (
//     id SERIAL PRIMARY KEY,
//     user_id INT REFERENCES users(id) ON DELETE CASCADE,
//     card_id INT REFERENCES cards(id) ON DELETE CASCADE,
//     amount NUMERIC(15,2) NOT NULL,
//     description TEXT,
//     created_at TIMESTAMP DEFAULT NOW(),
//     status SMALLINT DEFAULT 0
// );

// CREATE TABLE log_records (
//     id SERIAL PRIMARY KEY,
// 	branch_id INT REFERENCES branches(id) ON UPDATE CASCADE ON DELETE SET NULL,
//     transaction_id INT REFERENCES transactions(id) NULL,
//     user_id INT REFERENCES users(id) ON UPDATE CASCADE,
// 	personel_id INT REFERENCES personels(user_id) NULL,
// 	status VARCHAR(20) DEFAULT NULL,
//     log_type VARCHAR(50), -- TRANSACTION, USER_DATA, CARD_CREATE,ACCOUNT_CREATE
//     created_at TIMESTAMP DEFAULT now(),
//     description TEXT DEFAULT NULL
// );

$Select_Logs = "SELECT log_type,description,created_at FROM log_records WHERE user_id = $user_id";
$logs_result = pg_query($connection, $Select_Logs);

$Select_Debts = "SELECT amount,description,created_at FROM debts WHERE user_id = $user_id";
$debts_result = pg_query($connection, $Select_Debts);
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
                <h5>İşlem Kayıtları</h5>
            </div>
            <div class="DetailContent">
                <!-- HTML -->

                <div class="DetailContent">
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Tip</th>
                                <th>Açıklama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($log_record = pg_fetch_assoc($logs_result)) : ?>
                            <tr>
                                <td><?= $log_record['created_at'] ?></td>
                                <td><?= $log_record['log_type'] ?></td>
                                <td><?= $log_record['description'] ?></td>
                            </tr>
                            <?php endwhile ?>
                            <?php while($debt = pg_fetch_assoc($debts_result)) : ?>
                            <tr>
                                <td><?= $debt['created_at'] ?></td>
                                <td><?= $debt['description'] ?></td>
                                <td><?= $debt['amount'] ?> TL miktarında kredi harcaması yapılmıştır</td>
                            </tr>
                            <?php endwhile ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>


<?php include '../partials/footer.php'; ?>