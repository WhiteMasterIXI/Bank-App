<?php include '../partials/header.php';
require 'Setting_Permissions.php';
// Transfer tablosundan genel bilgileri çekme

// CREATE TABLE transfer_transactions (
// id SERIAL PRIMARY KEY,
// transaction_id INT UNIQUE REFERENCES transactions(id) ON DELETE CASCADE,
// sender_account_id INT REFERENCES accounts(id) ON UPDATE CASCADE ON DELETE RESTRICT,
// receiver_account_id INT REFERENCES accounts(id) ON UPDATE CASCADE ON DELETE RESTRICT,
// external_bank_name VARCHAR(255),   -- başka bankaya EFT ise
// external_iban VARCHAR(34),
// description TEXT

// get back form data if there was a registration error 
$firstname = $_SESSION['signup-data']['name'] ?? null;
$lastname = $_SESSION['signup-data']['surname'] ?? null;
$email = $_SESSION['signup-data']['email'] ?? null;
$password = $_SESSION['signup-data']['password'] ?? null;
unset($_SESSION['signup-data']);



// yetkileri seçme
$Select_Personel_authority = "SELECT id,name FROM roles WHERE name != 'Müdür'";
$Personel_Authority_Result = pg_query($connection, $Select_Personel_authority);

// şubeleri seçme
$Branch_Query = "SELECT * FROM branches";
$Branch_Result = pg_query($connection, $Branch_Query);

// personelleri seçme 
$Select_Personels = "SELECT u.id,u.name,u.surname,r.name AS role,p.role_id FROM personels p JOIN users u ON u.id = p.user_id JOIN roles r ON r.id = p.role_id ";
$Personel_Result = pg_query($connection, $Select_Personels);
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
            <div class="personel-grid">

                <!-- SOL: Personel Ekleme Alanı -->
                <div class="personel-card">
                    <h4>Yeni Personel Ekle</h4>
                    <?php if (isset($_SESSION['Add_Personel_Fail'])) : ?>
                        <p><?= $_SESSION['Add_Personel_Fail'];
                            unset($_SESSION['Add_Personel_Fail']); ?></p>
                    <?php elseif (isset($_SESSION['Add_Personel_Success'])) : ?>
                        <p><?= $_SESSION['Add_Personel_Success'];
                            unset($_SESSION['Add_Personel_Success']); ?></p>
                    <?php endif ?>
                    <form action="Add_Personel_Logic.php" method="POST" class="personel-form">
                        <div class="payment_info">
                            <div class="payment_options">
                                <label>Ad</label>
                                <input type="text" value="<?= $firstname ?>" name="name" placeholder="Personel adı">
                            </div>
                            <div class="payment_options">
                                <label>Soyad</label>
                                <input type="text" value="<?= $lastname ?>" name="surname" placeholder="Personel soyadı">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" value="<?= $email ?>" name="email" placeholder="E-mail adresi">
                        </div>

                        <div class="form-group">
                            <label>Şifre</label>
                            <input type="password" value="<?= $password ?>" name="password" placeholder="Şifresi">
                        </div>


                        <div class="payment_info">
                            <div class="payment_options">
                                <label>Rol / Yetki</label>
                                <select name="role_id">
                                    <?php while ($authority = pg_fetch_assoc($Personel_Authority_Result)) : ?>
                                        <option value="<?= $authority['id'] ?>"><?= $authority['name'] ?></option>
                                    <?php endwhile ?>
                                </select>
                            </div>
                            <div class="payment_options">
                                <label>Branch</label>
                                <select name="branch_id">
                                    <?php while ($branch = pg_fetch_assoc($Branch_Result)) : ?>
                                        <option value="<?= $branch['id'] ?>"><?= $branch['name'] ?></option>
                                    <?php endwhile ?>
                                </select>
                            </div>
                        </div>
                        <div class="form_submit_container">
                            <button name="submit" class="ClientBtnUpdate">Personel Oluştur</button>
                        </div>
                    </form>
                </div>

                <!-- SAĞ: Personel Listesi -->
                <div class="personel-card">
                    <h4>Personel Yönetimi</h4>
                    <?php if (isset($_SESSION['update_fail'])) : ?>
                        <p><?= $_SESSION['update_fail'];
                            unset($_SESSION['update_fail']); ?></p>
                    <?php elseif (isset($_SESSION['update_success'])) : ?>
                        <p><?= $_SESSION['update_success'];
                            unset($_SESSION['update_success']); ?></p>
                    <?php endif ?>

                    <table class="personel-table">
                        <thead>
                            <tr>
                                <th>Ad Soyad</th>
                                <th>Yetki</th>
                                <th>Düzenle</th>
                                <th>Sil</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($personel = pg_fetch_assoc($Personel_Result)) : ?>
                                <?php if ($personel['role_id'] != '1') : ?>
                                    <form action="Personel_Manage_Logic.php" method="POST">
                                        <input type="hidden" name="user_id" value="<?= $personel['id'] ?>">
                                        <tr>
                                            <td><?= $personel['name'] . ' ' . $personel['surname'] ?></td>
                                            <td><?= $personel['role'] ?></td>
                                            <td><button type="submit" name="updated" class="ClientBtnUpdate">Düzenle</button></td>
                                            <td><button type="submit" name="remove" class="ClientBtnDelete">Sil</button></td>
                                        </tr>
                                    </form>
                                <?php endif ?>
                            <?php endwhile ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>


<?php include '../partials/footer.php'; ?>