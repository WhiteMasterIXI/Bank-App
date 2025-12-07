<?php include '../partials/header.php';
require 'Setting_Permissions.php';
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

$Select_Logs = "SELECT lr.log_type,lr.description,lr.created_at,u.name,u.surname FROM log_records lr JOIN users u ON u.id = lr.user_id";
$logs_result = pg_query($connection, $Select_Logs);

$Select_Debts = "SELECT d.amount,d.description,d.created_at,u.name,u.surname FROM debts d JOIN users u ON u.id = d.user_id";
$debts_result = pg_query($connection, $Select_Debts);
?>

<div class="ManagementPanel">
    <!-- Orta Kısım: İşlem Listesi -->
    <div class="Transaction_Grid_Client">
        <aside class="Sidebar">
            <div class="card_informations center">
                <h5>Personel İşlemleri</h5>
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
                                <th>İsim</th>
                                <th>Soyad</th>
                                <th>Tarih</th>
                                <th>Tip</th>
                                <th>Açıklama</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($log_record = pg_fetch_assoc($logs_result)) : ?>
                                <tr>
                                    <td><?= $log_record['name'] ?></td>
                                    <td><?= $log_record['surname'] ?></td>
                                    <td><?= $log_record['created_at'] ?></td>
                                    <td><?= $log_record['log_type'] ?></td>
                                    <td><?= $log_record['description'] ?></td>
                                </tr>
                            <?php endwhile ?>
                            <?php while ($debt = pg_fetch_assoc($debts_result)) : ?>
                                <tr>
                                    <td><?= $debt['name'] ?></td>
                                    <td><?= $debt['surname'] ?></td>
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