# Unit Tests

Dokumentasi penggunaan unit test pada project DISC Personality Test.

## Prasyarat

```bash
composer install
```

## Menjalankan Test

### PHPUnit Tests

```bash
# Jalankan semua test
composer test

# Atau langsung dengan PHPUnit
./vendor/bin/phpunit
```

### Standalone Tests

Test standalone dapat dijalankan langsung dengan PHP:

```bash
php tests/test_db.php
php tests/test_db_pass.php
php tests/test_missing_post.php
php tests/test_xss.php
php tests/test_xss_fix.php
php tests/test_array_count_values_security.php
php tests/test_db_connection_failure.php
php tests/test_exception_leak.php
php tests/test_sqli_fix.php
php tests/test_lazy_db.php
php tests/test_index_query_failure.php
php tests/test_caching_performance.php
php tests/test_security_headers.php
php tests/test_result_fallback.php
php tests/test_result_query_failure.php
php tests/test_html_cache_write_failure.php
php tests/test_unreadable_cache.php
```

## Daftar Test

| File | Deskripsi |
|------|-----------|
| `DiscTest.php` | PHPUnit test untuk kalkulasi skor DISC dan XSS escaping |
| `test_db.php` | Test konfigurasi database via environment variables |
| `test_db_pass.php` | Test validasi `DB_PASS` wajib diset |
| `test_missing_post.php` | Test handling POST data yang tidak lengkap |
| `test_xss.php` | Test simulasi serangan XSS pada input |
| `test_xss_fix.php` | Verifikasi semua output di-escape dengan `htmlspecialchars()` |
| `test_array_count_values_security.php` | Test filtering array untuk mencegah warning |
| `test_db_connection_failure.php` | Test bahwa koneksi database yang gagal menghasilkan exception atau connection error |
| `test_exception_leak.php` | Verifikasi bahwa exception tidak bocor ke output; error ditangani secara graceful |
| `test_sqli_fix.php` | Verifikasi `result.php` menggunakan prepared statements (`bind_param`) bukan string concatenation |
| `test_lazy_db.php` | Test bahwa koneksi database tidak dilakukan saat cache HTML tersedia |
| `test_index_query_failure.php` | Test bahwa `index.php` menampilkan pesan error ketika query database gagal |
| `test_caching_performance.php` | Benchmark perbandingan performa antara query DB (mock) dan file cache |
| `test_security_headers.php` | Verifikasi `index.php` dan `result.php` mengirim security headers (`X-Frame-Options`, `X-Content-Type-Options`) |
| `test_result_fallback.php` | Test fallback pada `result.php` ketika hasil query pertama kosong (null); verifikasi nilai default (15, 14, 15, 14) |
| `test_result_query_failure.php` | Test bahwa `result.php` menangani kegagalan `get_result()` tanpa warning/error, lalu fallback ke nilai default |
| `test_html_cache_write_failure.php` | Verifikasi bahwa `error_log` dipanggil ketika gagal menulis ke file HTML cache |
| `test_unreadable_cache.php` | Verifikasi bahwa file cache yang tidak bisa dibaca (permission 0000) di-bypass dan HTML baru di-generate |

## Environment Variables

Beberapa test memerlukan environment variables:

```bash
export DB_HOST=localhost
export DB_USER=root
export DB_PASS=password
export DB_NAME=test
```

## Catatan

- Test `test_html_cache_write_failure.php` dan `test_unreadable_cache.php` menggunakan `chmod()` untuk mensimulasikan kondisi file system. Test ini mungkin tidak berjalan dengan benar di Windows.
- Test `test_caching_performance.php` hanya mengukur performa relatif dengan mock DB (2ms latency per query) dan tidak memerlukan koneksi database sungguhan.
- Test `test_result_fallback.php` dan `test_result_query_failure.php` menggunakan mock class untuk menggantikan koneksi database.
