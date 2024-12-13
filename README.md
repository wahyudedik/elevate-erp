<p>Download the project (or clone using GIT)</p>
<p>Copy .env.example into .env and configure your database credentials</p>
<p>Go to the project's root directory using terminal window/command prompt</p>
<p>Run composer install</p>
<p>Set the application key by running php artisan key:generate --ansi</p>
<p>Run migrations php artisan migrate</p>
<p>Start local server by executing php artisan serve</p>
<p>Visit here http://127.0.0.1:8000/products to test the application</p>
<p> php artisan shield:generate --all </p>
<p>  php artisan shield:super-admin </p>
<p>  apakah perlu menjalankan php artisan reverb:start </p>
<p>  php artisan composer run dev </p>

Perbaikan :

-   [x] bug dan bahasa indonesia => branch/cabang
-   [x] bug dan bahasa indonesia => Departemen
-   [x] bug dan bahasa indonesia => Jabatan
-   [x] bug dan bahasa indonesia => Akuntansi
-   [x] bug dan bahasa indonesia => Jurnal
-   [x] bug dan bahasa indonesia => Buku Besar
-   [x] bug dan bahasa indonesia => laporan keuangan
-   [x] bug => role permission
-   [x] Email Verification port .env dan .env.example

Fitur Admin :

-   [x] fitur login = User untuk melakukan login yang berstatus active
-   [x] fitur register = User untuk melakukan register untuk menjadi user aktif dan masuk ke dalam sistem
-   [x] fitur forgot password =  User untuk melakukan reset password
-   [x] fitur reset password = User untuk melakukan reset password
-   [x] fitur profile
-   [x] fitur logout
-   [x] fitur dashboard
-   [x] fitur company/tenant
-   [x] fitur global pencarian
-   [x] fitur cabang
-   [x] fitur departemen
-   [x] fitur jabatan
-   [x] fitur Akun Transfer ke akun lain
-   [x] fitur akutansi
-   [x] fitur jurnal
-   [x] fitur bukuu besar dan transaksi
-   [x] fitur laporan keuangan -> laporan laba rugi, laporan neraca, laporan arus kas
-   [x] fitur role permission

bug :

-   [] Deployment error fitur permission super-admin dan register/login
-   [] Actifyti log error belum setting per company
-   [] laporan keuangan belum otomatis
