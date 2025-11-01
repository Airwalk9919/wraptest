# WrapStation – Inspection Report (CodeIgniter 3)
Aplikasi Inspection Report berbasis CodeIgniter 3 untuk bengkel/auto detailing & wrapping:

Form New Report (data kendaraan & pelanggan)

Checklist G/F/P (Paint, Glass, Tires & Wheels) + Notes + Upload media

Vehicle Angles: Front / Rear / Left / Right

Final & Export: persetujuan, tanda tangan digital (canvas), Export PDF (mPDF)

Tanpa DB – data disimpan sebagai JSON + media file

Dibuat untuk keperluan tes: fokus pada arsitektur bersih, UX cepat, dan PDF yang mirip contoh video.
By : Rivaldy Ramdhany — Full Stack (CI3, PDF, mPDF, Bootstrap, SweetAlert2)

jalankan di localhost karena menggunakan Codeigniter 3 dan json

🧭 Alur Penggunaan

Create Report (Form New): isi Location, Customer, Phone, Brand, Model, Color, Year, Plate (auto uppercase, no space), Inspector, Date, Mileage.

Tab 1 – Checklist: pilih G/F/P per item → area Note + Upload muncul. Simpan (file opsional setelah pernah upload).

Tab 2 – Angles: upload foto Front/Rear/Left/Right (min. 1 per sisi).

Tab 3 – Final: centang persetujuan → tanda tangan → Submit (akan upload signature & otomatis buka PDF).

Export PDF juga tersedia via tombol di header ketika semua syarat terpenuhi.

🚀 Quick Start (XAMPP – Windows):

Clone / Extract ke:

C:\xampp\htdocs\wrapstation


Composer install (pastikan PHP XAMPP dipakai):

cd C:\xampp\htdocs\wrapstation
composer install
# atau
composer require mpdf/mpdf


Aktifkan ekstensi PHP di C:\xampp\php\php.ini:

extension=gd
extension=mbstring


Restart Apache.

Folder writable:

storage\reports\
uploads\
application\cache\mpdf_tmp\   (dibuat otomatis)


Logo (opsional):

public\img\logo.png


Akses aplikasi:

http://localhost/wrapstation/
