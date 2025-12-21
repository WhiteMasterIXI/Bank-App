/* Kişinin istediği bilgileri filtrelemesi. */
const AramaCubugu = document.querySelector('.Search_input');
let Kullanicilar = document.querySelectorAll('.Personel_Table .personel');


AramaCubugu.addEventListener('input', () => {
    const aranan = AramaCubugu.value.toLowerCase();
    Kullanicilar.forEach(tr => {
        
        const ad = tr.querySelector('.personel_name').textContent.toLowerCase();
        if(!ad.includes(aranan))tr.style.display = 'none';
        else tr.style.display = '';
    });
});