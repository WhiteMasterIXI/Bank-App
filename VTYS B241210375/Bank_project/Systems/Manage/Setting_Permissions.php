<?php 
// 1	"Log_Islemleri"
// 2	"Kullanıcı_Islemleri"
// 3	"Transfer_Islemleri"
// 4	"Personel_Islemleri"
// 5	"Hesap_Islemleri"
// 6	"Kart_Islemleri"

$user_id = $_SESSION['user_id'];
$User_Role_Query = "SELECT role_id FROM personels WHERE user_id = $user_id";

$User_Role_Result = pg_query($connection, $User_Role_Query);
$user = pg_fetch_assoc($User_Role_Result);

$user_role_id = -1;
if (isset($user['role_id']))
    $user_role_id = $user['role_id'];

$is_Admin = $_SESSION['is_admin'];
$user_permissions = [];
if ($is_Admin == 1) { // 1 = Admin / Müdür
    // Admin tüm yetkilere sahip, tüm permission_id’leri atıyoruz
    $user_permissions = [1, 2, 3, 4, 5, 6]; // tüm permission id’leri
} else {
    // Normal kullanıcılar için tablodan çek
    $permissions_query = "SELECT permission_id FROM role_permissions WHERE role_id = $user_role_id";
    $permissions_result = pg_query($connection, $permissions_query);

    while ($row = pg_fetch_assoc($permissions_result)) {
        $user_permissions[] = $row['permission_id'];
    }
}
?>