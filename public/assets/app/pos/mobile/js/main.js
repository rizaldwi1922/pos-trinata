// make a swicth menu on bottom navigations
const switch_menu = (key) => {
    if(key === 'home') {
         hidden_all_page();
         deactive_all_menu();
 
         $('#home').removeClass('hidden');
         $('#home-menu').addClass('active');
     } else if(key === 'presensi') {
         hidden_all_page();
         deactive_all_menu();
 
         $('#presensi').removeClass('hidden');
         $('#presensi-menu').addClass('active');
 
     } else if(key === 'others') {
         hidden_all_page()
         deactive_all_menu();
         
         $('#others').removeClass('hidden');
         $('#others-menu').addClass('active');
     }
 }
 
 const hidden_all_page = () => {
     $('#home').addClass('hidden');
     $('#presensi').addClass('hidden');
     $('#others').addClass('hidden');
 }
 
 const deactive_all_menu = () => {
     $('#home-menu').removeClass('active');
     $('#presensi-menu').removeClass('active');
     $('#others-menu').removeClass('active');
 }
 
 $('#home-menu').on('click', () => {
     switch_menu('home');
 })
 
 $('#presensi-menu').on('click', () => {
     switch_menu('presensi');
 })
 
 $('#others-menu').on('click', () => {
     switch_menu('others');
 })
 
 
 // make input type date auto value with this today
 let today = new Date();
 const dd = String(today.getDate()).padStart(2, '0');
 const mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
 const yyyy = today.getFullYear();
 today = yyyy + '-' + mm + '-' + dd;
 $('#input-date-presensi').val(today);
 
 
 // make a input type files to show image preview
 function readURL(input) {
     if (input.files && input.files[0]) {
         var reader = new FileReader();
 
         reader.onload = function (e) {
             $('.presensi #image-preview').attr('src', e.target.result);
             $('.presensi #image-preview').attr('style', "width:100% !important;height:100% !important;margin:0px;");
             $('.presensi .thumbnail-image').attr('stye', "padding:0px !important")
         }
 
         reader.readAsDataURL(input.files[0]);
     }
 }
 
 $("#input-image-presensi").change(function(){
     readURL(this);
 });
 
 
 // hide alasan-presensi when input type presensi change
 $('#input-type-presensi').on('change', function() {
     if(this.value == 'masuk') {
         $('#alasan-presensi').addClass('hidden');
         $('#name-label-dokumen').text('Foto Kehadiran')
     } else {
         $('#alasan-presensi').removeClass('hidden');
         
         $('#name-label-dokumen').text('Dokumen Pendukung')
     }
 });
 
 // make a tab menu on presensi page swictch
 const switch_tab_menu = (key) => {
     if(key === 'presensi') {
         $('#tab-menu-presensi').addClass('active');
         $('#tab-menu-perizinan').removeClass('active');
 
         hide_all_tab_presensi_page();
         $('.content-presensi').removeClass('hidden');
     } else if(key === 'perizinan') {
         $('#tab-menu-presensi').removeClass('active');
         $('#tab-menu-perizinan').addClass('active');
 
         hide_all_tab_presensi_page();
         $('.content-perizinan').removeClass('hidden');
     }
 }
 
 const hide_all_tab_presensi_page = () => {
     $('.content-presensi').addClass('hidden');
     $('.content-perizinan').addClass('hidden');
 }
 
 $('#tab-menu-presensi').on('click', () => {
     switch_tab_menu('presensi');
 });
 
 $('#tab-menu-perizinan').on('click', () => {
     switch_tab_menu('perizinan');
 });
 
 
 // modal listener
 
 const openModal = (id) => {
     $(id).removeClass('hidden');
     $(`${id} .content-modal`).addClass('modal-up');
 }
 
 const closeModal = () => {
     $('.modal .content-modal').removeClass('modal-up');
     $('.modal .content-modal').addClass('modal-down');
 
     setTimeout(() => { $('.modal').addClass('hidden')}, 500)
 }
 
 $('.modal .close-modal').on('click', () => {
     closeModal();
 });
 
 $('.modal').on('click',function(e){
     if (!$(e.target).closest('.content-modal').length && !$(e.target).is('.content-modal')) {
        closeModal();
     }
 })
 
 
 $('#others-menu-profile').on('click', () => {
     openModal('#modal-profile');
 });
 
 $('#others-menu-riwayat-transaksi').on('click', () => {
     openModal('#modal-riwayat-transaksi')
 })
 
 $('#others-menu-riwayat-shift').on('click', () => {
     openModal('#modal-riwayat-shift')
 })




 