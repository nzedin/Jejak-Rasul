# Jejak Sirah

Kembara sirah interaktif. Pemain menjadi watak rekaan yang menyaksikan peristiwa sirah, dan setiap babak ditanda dengan label kesahihan serta dipautkan kepada sumber rujukan yang disemak.

Projek sumber terbuka (open source).

## Prinsip kandungan

- **Nabi ﷺ dan para sahabat tidak dipaparkan secara visual.** Kehadiran mereka disampaikan melalui naratif dan teks riwayat bersama sumber.
- **Setiap babak ada label:** 🟢 Sahih · 🟡 Riwayat sirah · ⚪ Rekaan (dialog watak rekaan / suasana sahaja).
- **Tiada kandungan sampai kepada pemain tanpa semakan.** Setiap perubahan pada tajuk bab atau isi babak menjadi versi baru yang perlu diluluskan oleh penyemak.

## Stack

- Laravel 13, Livewire 4, Filament 5
- MySQL (Laragon) untuk pembangunan; SQLite untuk ujian

## Pasang (Laragon)

```bash
git clone https://github.com/nzedin/Jejak-Rasul.git jejak-sirah
cd jejak-sirah
composer install
cp .env.example .env
php artisan key:generate
```

Cipta pangkalan data `jejak_sirah` dalam Laragon (HeidiSQL), kemudian:

```bash
php artisan migrate --seed
```

| Paparan | Alamat |
|---|---|
| **Pemain** | http://jejak-sirah.test/ |
| **Panel kandungan** (penulis/penyemak) | http://jejak-sirah.test/panel |
| **Pratonton draf** (selepas log masuk panel) | http://jejak-sirah.test/?pratonton=1 |

Atau `php artisan serve` → http://127.0.0.1:8000.

Paparan pemain hanya menunjukkan kandungan yang **diluluskan** dalam arka yang ditanda *Papar kepada pemain*. Kalau belum ada apa-apa yang diluluskan, gunakan mod pratonton untuk lihat rupa draf.

Akaun contoh daripada seeder (kata laluan: `password`, **tukar sebelum dipasang di pelayan sebenar**):

| Peranan  | E-mel                     |
|----------|---------------------------|
| Admin    | admin@jejaksirah.test     |
| Penulis  | penulis@jejaksirah.test   |
| Penyemak | penyemak@jejaksirah.test  |

Seeder juga mencipta rangka arka **Hijrah** (10 bab, semuanya draf) dan senarai sumber utama tanpa nombor rujukan. Butiran rujukan perlu diisi daripada naskhah sebenar.

## Tema

Panel kandungan menggunakan tema yang sama dengan paparan pemain: mod gelap, langit malam beranimasi, warna emas, lambang bulan sabit & jejak, dan tajuk berfont serif. Tema dimuatkan daripada `public/css/panel.css` melalui render hook Filament, jadi tiada langkah build diperlukan.

## Media dalam bab

Dalam **Kandungan → Bab → (pilih bab) → Media**, penulis boleh tambah:

- **Gambar**: muat naik JPG/PNG/WebP (maks. 5MB)
- **Video**: tampal pautan **YouTube/Vimeo** (dibenamkan), atau muat naik MP4/WebM kecil (maks. 12MB)
- **Pautan**: kad pautan ke laman luar (dibuka dalam tab baru)

Setiap media boleh diletak **selepas babak tertentu**, ada keterangan, kredit dan sumber, dan mesti disahkan **tidak memaparkan Nabi ﷺ, para nabi, malaikat atau para sahabat**. Media melalui semakan yang sama (Hantar → Lulus); pemain hanya nampak media yang diluluskan.

Fail yang dimuat naik disimpan dalam `storage/app/public/bab-media`. Jalankan sekali:

```bash
php artisan storage:link
```

## Latar babak

Setiap babak ada **latar visual** (Kota Makkah waktu malam, padang pasir malam/siang, dalam gua, pesisir pantai, oasis waktu fajar) yang dipilih dalam panel. Latar hanya landskap & objek — Nabi ﷺ dan sahabat tidak dipaparkan. Latar ialah hiasan, jadi ia tidak melalui aliran semakan.

Selepas `git pull`, jalankan `php artisan migrate` untuk menambah jadual/lajur baru.

## Aliran kandungan

```
draf ──hantar──▶ dihantar ──lulus──▶ diluluskan (pemain nampak versi ini)
                    │
                    └──minta pindaan──▶ perlu_pindaan ──edit & hantar──▶ dihantar
```

| Situasi | Apa berlaku |
|---|---|
| Edit versi yang masih draf / perlu pindaan | Versi yang sama dikemas kini |
| Edit versi yang sedang dihantar | Ditarik balik menjadi draf, kemudian dikemas kini |
| Edit versi yang sudah diluluskan | **Versi baru** dicipta. Pemain terus nampak versi lama sehingga versi baru diluluskan |
| Penyemak luluskan | Versi tersebut terus menjadi versi yang dibaca pemain |
| Kembalikan versi lama | Penyemak boleh terbitkan semula mana-mana versi yang pernah diluluskan |

Peraturan:
- Hanya **penulis/admin** boleh menulis; hanya **penyemak/admin** boleh lulus.
- Sesiapa yang mencipta atau menyunting sesuatu versi **tidak boleh menyemak versi itu sendiri** (termasuk admin).
- Minta pindaan **wajib** disertakan komen.
- Sumber dipautkan pada setiap **versi** babak, jadi sejarah rujukan kekal.
- Pilihan Yusuf & soalan kuiz: jika diubah selepas diluluskan, ia kembali menjadi draf secara automatik.

## Struktur kod

```
app/
├── Enums/                 Role, RevisionStatus, SceneLabel, SourceType, QuizAnswer
├── Models/
│   ├── Chapter, ChapterRevision     bab + versi tajuk/ringkasan
│   ├── Scene, SceneRevision         babak + versi isi/label/sumber
│   ├── Source, Review               rujukan & rekod semakan
│   ├── Choice, QuizQuestion         pilihan Yusuf & "Fakta atau Mitos?"
│   └── PlayerProgress, PlayerCard, JournalEntry
├── Services/
│   ├── ChapterEditor, SceneEditor   cipta/edit (melalui RevisionWriter)
│   ├── RevisionWriter               peraturan "versi sama atau versi baru"
│   ├── ContentWorkflow              hantar / lulus / minta pindaan / kembalikan
│   └── TextDiff                     perbandingan baris untuk penyemak
└── Filament/
    ├── Actions/WorkflowActions      butang aliran semakan (dikongsi)
    └── Resources/
        ├── Arcs, Chapters (+ Babak, Pilihan, Kuiz, Sejarah versi)
        ├── SceneRevisions, ChapterRevisions   barisan semakan
        └── Sources
```

## Ujian

```bash
php artisan test
```

`tests/Feature/ContentWorkflowTest.php` menguji aliran versi dan semakan.

## Langkah seterusnya

- [x] Paparan pemain: mod cerita beranimasi (satu babak satu skrin, teks muncul perkataan demi perkataan, latar beranimasi ikut lokasi, bunyi angin pilihan), peta laluan, label & sumber, pilihan Yusuf, kuiz, Kad Sumber, Jurnal Saksi
- [x] Buka bab ikut hari (`unlock_day_offset`, dikira dalam peranti pemain)
- [ ] Akaun pemain (kemajuan disimpan di pelayan; kini dalam `localStorage` peranti)
- [ ] PWA (boleh dipasang & dibaca luar talian)
- [ ] Susunan semula babak memerlukan pengesahan penyemak
