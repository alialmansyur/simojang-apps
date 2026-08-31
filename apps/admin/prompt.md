Bertindak sebagai **Senior Fullstack Developer CodeIgniter 4** pada project ini.

Lakukan **trace, audit, bug hunting, bug fixing, refactoring, standardisasi, dan optimalisasi** pada:
`/timkerja`
`/timkerja-layanan/*`

### 1. TRACE EXISTING

Sebelum coding, trace seluruh flow:
**Route → Controller → Model → View → JS/AJAX → Endpoint → Database**

Pahami implementasi existing, termasuk route, parameter, controller, model, query, relasi parent-detail, CRUD, form, modal, Select2, DataTable, AJAX, validasi, upload, dan response.

**Jangan berasumsi. Jangan rewrite besar-besaran. Pertahankan business logic yang sudah benar.**

### 2. CLEAN ARCHITECTURE — WAJIB

Terapkan separation of concerns:

* **Controller:** request → validasi → panggil Model → response.
* **Model:** seluruh query, database, join, filter, pagination, relasi, CRUD.
* **View:** HTML/UI/Form/Modal/DataTable.
* **JS/AJAX:** event, request, loading, response handling, refresh UI, lifecycle komponen.

**DILARANG ada query/database access di Controller**, termasuk Query Builder atau SQL seperti:
`$db->query()`, `$db->table()`, `select()`, `join()`, `where()`, `find()`, `findAll()`, `first()`, `insert()`, `update()`, `delete()`, dan sejenisnya.

Jika ditemukan query/business logic di Controller, **pindahkan ke Model** tanpa mengubah business logic.

Target:
**View → AJAX → Controller → Model → Database → Model → Controller → AJAX → View**

Tidak boleh:
**Controller → Database**

### 3. BUG HUNTING

Cari root cause dan perbaiki seluruh bug, terutama:

* Create menimpa/meng-update data existing.
* ID/primary key Create vs Update salah.
* Parent-detail relationship salah.
* Data tidak tersimpan/tidak muncul.
* DataTable tidak refresh.
* AJAX request/response atau endpoint bermasalah.
* Parameter/validasi salah.
* Duplicate submit/request.
* Modal/loading/error handling bermasalah.
* Query tidak efisien.
* Data UI tidak konsisten dengan database.
* Event handler ter-register berulang.
* Komponen frontend mengalami duplicate initialization.
* State/form lama terbawa saat modal dibuka kembali.

**Khusus BUG MODAL + SELECT2 — WAJIB DIAUDIT**

Pada modal tambah/edit, pastikan setiap komponen **Select2 hanya diinisialisasi satu kali untuk setiap instance modal**.

Cari dan perbaiki kasus seperti:

**Buka Modal → Init Select2 → Tutup Modal → Buka Lagi → Init Select2 Lagi → terjadi 2 Select2 / duplicate DOM / duplicate event / AJAX request ganda.**

Pastikan lifecycle Select2 benar:

* Sebelum melakukan `.select2()`, cek apakah element sudah memiliki instance Select2.
* Jangan melakukan init berulang setiap kali modal dibuka tanpa destroy/reuse instance.
* Jika menggunakan event `shown.bs.modal`, pastikan handler tidak ter-register berkali-kali.
* Jika menggunakan AJAX Select2, pastikan konfigurasi tidak menghasilkan request ganda.
* Saat modal ditutup/reset, bersihkan state sesuai kebutuhan tanpa merusak konfigurasi Select2.
* Jika memang perlu reinitialize, lakukan **destroy terlebih dahulu**, kemudian init kembali.
* Pastikan container `.select2-container` lama tidak tertinggal di DOM.
* Pastikan `dropdownParent` mengarah ke modal yang benar agar dropdown tidak bermasalah dengan backdrop/z-index.
* Pastikan event `change`, `select`, dan callback Select2 tidak terduplikasi.
* Pastikan membuka modal berkali-kali menghasilkan **tepat 1 Select2**, bukan 2, 3, atau lebih.
* Audit semua Select2 pada `/timkerja` dan `/timkerja-layanan/*`, bukan hanya field yang terlihat bermasalah.

**Target behavior:**

`Open Modal → 1 Select2`

`Close Modal → cleanup/reset yang benar`

`Open Modal kembali → tetap 1 Select2`

`Open/Close 10x → tetap 1 Select2 + tidak ada duplicate event/request`

Jangan hanya menghilangkan tampilan Select2 kedua. **Perbaiki root cause lifecycle/init JavaScript-nya.**

### 4. CREATE / DETAIL

Jika terdapat input detail, gunakan **struktur tabel, field, relasi, endpoint, dan business logic existing** sebagai dasar.

Jangan membuat field berdasarkan asumsi.

Pastikan detail otomatis terhubung ke parent yang sedang dibuka.

Flow:
**Detail → Input → Validation → AJAX → Controller → Model → INSERT → Response → Refresh → Data Baru Tampil**

Jika ada Update, pisahkan jelas:
**Create Detail ≠ Update Detail**

Pastikan:
**Create = INSERT record baru**
dan tidak menggunakan ID existing secara tidak sengaja.

### 5. UPLOAD

Jika terdapat upload file, ikuti pola Drag & Drop existing pada `/apps-ikpa`.

Pertahankan pola existing untuk UI, validasi, endpoint, response, loading, success/error, dan preview jika tersedia.

Jika `"Unduh format file disini"` berada di dalam dropzone, pindahkan ke luar/bawah dropzone tanpa mengubah link/template.

### 6. MODAL, SELECT2, LOADING & DATATABLE

Audit lifecycle seluruh komponen modal:

**Open → Initialize → Interaction → Submit → Success/Error → Close → Cleanup/Reset → Reopen**

Pastikan:

* Submit → Loading → AJAX → Response → Success/Error.
* Cegah double submit.
* Loader tidak tertutup modal/backdrop.
* Success → modal close → refresh data.
* Error → modal tetap terbuka.
* Form reset sesuai kebutuhan.
* State lama tidak terbawa ke modal berikutnya.
* Event listener tidak ter-register berulang.
* AJAX handler tidak terduplikasi.
* Select2 tidak terduplikasi.
* DataTable tidak terinisialisasi berulang.
* Tidak ada duplicate DOM element akibat proses open/close.
* Tidak ada duplicate AJAX request akibat event handler yang ter-register berkali-kali.
* DataTable/list menampilkan data terbaru.
* Search/filter/sorting/pagination tetap berjalan.
* Hindari stale data dan duplicate initialization/request.

Untuk DataTable dan Select2, audit apakah terdapat pola seperti:

`$(document).on(...)`
`$('#modal').on('shown.bs.modal', ...)`
`$('#modal').on('hidden.bs.modal', ...)`
`.select2(...)`
`.DataTable(...)`

yang menyebabkan handler atau instance terbuat berulang.

Gunakan pola lifecycle yang aman, misalnya:
**guard → reuse**, atau **destroy → reinitialize**, sesuai kebutuhan implementasi existing.

### 7. STANDARDISASI

Rapikan sesuai pola existing project:

* Naming.
* Controller/Model structure.
* CRUD.
* Validation.
* JSON response.
* AJAX.
* Modal lifecycle.
* Select2 lifecycle.
* DataTable lifecycle.
* Parent-detail relation.
* Query.

Jangan membuat Service/Repository/Helper baru tanpa kebutuhan.

**Jangan mengubah schema, endpoint, business logic, atau UI global jika tidak diperlukan.**

### 8. TEST & REGRESSION

Test minimal:

**Load → Search/Filter → Detail → Open Modal → Select2 → Close Modal → Open Modal Kembali → Select2 → Validation → Upload → Submit → Insert → Refresh → Edit → Update → Delete → Pagination**

Khusus modal dan Select2, lakukan regression test minimal:

**Open → Close → Open → Close → Open** beberapa kali.

Pastikan:

* Tetap hanya **1 Select2 instance**.
* Tidak ada duplicate `.select2-container`.
* Tidak ada duplicate event handler.
* Tidak ada duplicate AJAX request.
* Tidak ada JS/Console error.
* Tidak ada modal/backdrop error.
* Tidak ada state lama yang bocor.
* Tidak ada data existing yang tertimpa.
* Create tidak berubah menjadi Update.
* Data baru tampil setelah refresh.
* Query database tidak berada di Controller.

### OUTPUT

Berikan ringkasan:

1. **Temuan & Root Cause**
2. **Bug yang diperbaiki**
3. **Khusus Modal & Select2: penyebab duplicate initialization dan solusi lifecycle yang diterapkan**
4. **Perubahan Controller/Model/View/JS-AJAX**
5. **Endpoint & query yang terdampak**
6. **File yang berubah**
7. **Hasil testing & regression**
8. **Rekomendasi jika ada**

**Prinsip utama:**

> **TRACE → ROOT CAUSE → FIX → CLEAN ARCHITECTURE → STANDARDIZE → TEST → REGRESSION**

Setiap perubahan harus berdasarkan hasil trace dan business logic existing, **bukan asumsi**.

Prioritaskan **perbaikan root cause**, bukan sekadar menutupi gejala. Jangan melakukan rewrite atau perubahan besar jika tidak diperlukan.
