<?php 
include '../config/database.php';

if(isset($_POST['submit'])){
    $iban = filter_var($_POST['iban'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_var($_POST['description'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $user_id = $_SESSION['user_id'];

    if(!str_starts_with($iban,'TR') || !validateIBAN($iban)){
        $_SESSION['Iban_record_fail'] = "İşlem başarısız lütfen geçerli bir iban adresi giriniz";
        header('location: ' .ROOT_URL . 'Systems/Client/Add_Iban.php');
        die();
    }

    if($_POST['description']){
        $Iban_Query = "INSERT INTO iban_address (user_id,iban,description) VALUES ('$user_id','$iban','$description') ";
    }else{
        $Iban_Query = "INSERT INTO iban_address (user_id,iban) VALUES ('$user_id','$iban') ";
    }

    $Result = pg_query($connection,$Iban_Query);
}



header('location: ' .ROOT_URL . 'Systems/Client/Add_Iban.php');
die(); 

function validateIBAN($iban) {
    // Boşlukları kaldır ve büyük harf yap
    $iban = strtoupper(str_replace(' ', '', $iban));

    // Uzunluk kontrolü (Türkiye için 26 karakter)
    if (strlen($iban) != 26) return false;

    // Sadece A-Z ve 0-9 olmalı
    if (!preg_match('/^[A-Z0-9]+$/', $iban)) return false;

    // İlk 4 karakteri sona at
    $ibanRearranged = substr($iban, 4) . substr($iban, 0, 4);

    // Harfleri A=10 ... Z=35 çevir
    $ibanNumeric = '';
    foreach (str_split($ibanRearranged) as $c) {
        if (ctype_alpha($c)) {
            $ibanNumeric .= (ord($c) - 55);
        } else {
            $ibanNumeric .= $c;
        }
    }

    // Mod97 işlemi uzun string için
    $remainder = intval(substr($ibanNumeric, 0, 1));
    for ($i = 1; $i < strlen($ibanNumeric); $i++) {
        $remainder = ($remainder * 10 + intval($ibanNumeric[$i])) % 97;
    }

    return $remainder === 1;
}
?>