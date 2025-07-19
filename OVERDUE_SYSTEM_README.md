# Sistem Otomatis Task Overdue

## Deskripsi
Sistem ini secara otomatis mengidentifikasi dan memproses task-task yang telah melewati deadline (overdue) dengan mengubah statusnya menjadi "Non Achieved" dan mencatat achievement record di database.

## Cara Kerja

### 1. Pemrosesan Otomatis (Auto-trigger)
- **Kapan**: Setiap kali karyawan mengakses halaman "My Tasks" (mytasks.php)
- **Proses**:
  1. Sistem memeriksa semua task milik user dengan status "In Progress" yang deadline-nya sudah terlewat
  2. Untuk setiap task overdue:
     - Cek apakah sudah ada record achievement dengan status "Non Achieved"
     - Jika belum ada, buat record baru di table `task_achievements` dengan:
       - `progress_int` = 0 (0% progress)
       - `status` = "Non Achieved"
       - `notes` = "Task otomatis ditandai Non Achieved karena melewati deadline"
  3. Update status task di table `user_tasks` menjadi "Non Achieved"

### 2. Pemrosesan Batch (Cron Job)
- **File**: `auto_overdue_update.php`
- **Fungsi**: Memproses semua task overdue di seluruh sistem untuk semua user
- **Penggunaan**: 
  ```bash
  php auto_overdue_update.php
  ```
- **Rekomendasi**: Set sebagai cron job yang berjalan setiap hari
  ```bash
  # Contoh cron job - jalankan setiap hari jam 23:59
  59 23 * * * /usr/bin/php /path/to/tama_kp/auto_overdue_update.php
  ```

## Database Tables

### task_achievements
Table ini menyimpan record achievement untuk setiap laporan task:
- `user_task_id`: ID task yang dilaporkan
- `user_id`: ID karyawan
- `progress_int`: Persentase progress (0-100)
- `notes`: Catatan dari karyawan atau sistem
- `status`: Status achievement ("In Progress", "Achieved", "Non Achieved")
- `submitted_at`: Waktu laporan dibuat

### user_tasks
Table ini menyimpan data task assignment:
- `status`: Status task ("In Progress", "Achieved", "Non Achieved")
- `deadline`: Tanggal deadline task
- `updated_at`: Waktu terakhir diupdate

## Fitur Keamanan
1. **Duplikasi Prevention**: Sistem mencegah pembuatan multiple achievement records untuk task overdue yang sama
2. **User Authentication**: Semua proses memerlukan login yang valid
3. **Data Integrity**: Menggunakan prepared statements untuk keamanan database

## Proses Otomatis
- **Tidak memerlukan intervensi admin**: Sistem berjalan otomatis tanpa perlu interface tambahan
- **Seamless processing**: Terintegrasi dengan workflow normal karyawan
- **Real-time updates**: Task overdue langsung diproses saat user mengakses My Tasks

## Log dan Monitoring
- Achievement records memiliki catatan otomatis yang menjelaskan bahwa task ditandai overdue oleh sistem
- Timestamp lengkap untuk audit trail
- Output logging pada script batch untuk monitoring cron job

## Pengaturan
- **Auto-run**: Aktif secara default setiap kali user mengakses My Tasks
- **Batch processing**: Script standalone untuk cron job pemrosesan massal
- **No manual intervention needed**: Sistem bekerja sepenuhnya otomatis

## Troubleshooting

### Task tidak terdeteksi overdue
- Periksa format tanggal deadline (YYYY-MM-DD)
- Pastikan timezone server sesuai

### Achievement record tidak terbuat
- Periksa struktur table `task_achievements`
- Pastikan foreign key constraints benar

### Performance
- Untuk database besar, gunakan batch processing dengan cron job
- Monitor execution time pada auto-run jika diperlukan

## Keunggulan Pendekatan Ini
1. **Sederhana**: Tidak ada interface admin tambahan yang perlu dikelola
2. **Otomatis**: Berjalan tanpa intervensi manual
3. **Efisien**: Proses langsung saat user mengakses sistem
4. **Akurat**: Data selalu up-to-date dengan aktivitas user
5. **Minimal overhead**: Tidak memerlukan resource tambahan untuk UI
