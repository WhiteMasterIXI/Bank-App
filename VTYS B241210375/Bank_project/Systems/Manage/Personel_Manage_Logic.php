<?php
require '../config/database.php';

$Personel_Transaction;

if (isset($_POST['remove']))
    $Personel_Transaction = "Remove";
elseif (isset($_POST['updated']))
    $Personel_Transaction = "Update";
else {
    header('location: ' . ROOT_URL . 'index.php');
    die();
}


$User_From_Post = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);



if (!$User_From_Post) {
    $_SESSION['Personel_Update_Fail'] = "Islem başarısız kullanıcının id si alınamadı.";
}

if (isset($_SESSION['Personel_Update_Fail'])) {
    header('location: ' . ROOT_URL . 'Systems/Manage/Personel_Update_Logic.php');
    die();
}

?>

<?php if ($Personel_Transaction == "Remove") : ?>
    <?php
    $User_Delete_Query = "DELETE FROM users WHERE id = $User_From_Post";
    $user_Delete_Result = pg_query($connection, $User_Delete_Query);
    header('location: ' . ROOT_URL . 'Systems/Manage/Personel_Management.php');
    die();
    ?>
<?php elseif ($Personel_Transaction == "Update") : ?>
    <?php include '../partials/header.php';
    require 'Setting_Permissions.php';


    $Select_User_Query = "SELECT name,surname,email FROM users WHERE id = $User_From_Post";
    $User_Result = pg_query($connection, $Select_User_Query);
    $User = pg_fetch_assoc($User_Result);

    $firstname = $User['name'];
    $lastname = $User['surname'];
    $email = $User['email'];

    // yetkileri seçme
    $Select_Personel_authority = "SELECT id,name FROM roles WHERE name != 'Müdür'";
    $Personel_Authority_Result = pg_query($connection, $Select_Personel_authority);

    // şubeleri seçme
    $Branch_Query = "SELECT * FROM branches";
    $Branch_Result = pg_query($connection, $Branch_Query);
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

                    <?php if (in_array(5, $user_permissions)) : // Kart yetkisi 
                    ?>
                        <li><a href="Accounts.php"><i class="uil uil-file-edit-alt"></i> Hesap işlemleri</a></li>
                    <?php endif ?>

                    <?php if (in_array(2, $user_permissions)) : // Müşteri işlemleri yetkisi 
                    ?>
                        <li><a href="Payment.php"><i class="uil uil-bill"></i> Ödeme işlemleri</a></li>
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
                <h5>Banka İşlemleri</h5>
                <div class="DetailContent">
                    <form action="Personel_Update_Logic.php" method="POST" class="personel-form">
                        <input type="hidden" name="user_id" value="<?= $User_From_Post ?>">
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
                            <input type="password" name="password" placeholder="Şifresi">
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
                            <button name="submit" class="ClientBtnUpdate">Düzenle</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <?php include '../partials/footer.php'; ?>

<?php endif ?>