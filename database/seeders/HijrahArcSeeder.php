<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\SourceType;
use App\Models\Arc;
use App\Models\Source;
use App\Models\User;
use App\Services\ChapterEditor;
use Illuminate\Database\Seeder;

/**
 * Rangka arka Hijrah: 10 bab bertaraf DRAF sahaja (tiada isi babak).
 * Tajuk ini cadangan awal — penulis ubah melalui panel, dan penyemak
 * yang muktamadkan sebelum apa-apa diterbitkan.
 */
class HijrahArcSeeder extends Seeder
{
    public function run(ChapterEditor $editor): void
    {
        $writer = User::where('role', Role::Writer)->orderBy('id')->firstOrFail();

        $arc = Arc::firstOrCreate(
            ['slug' => 'hijrah'],
            ['title' => 'Hijrah', 'description' => 'Perjalanan dari Makkah ke Madinah.', 'position' => 1],
        );

        if ($arc->chapters()->exists()) {
            return;
        }

        $titles = [
            'Tekanan di Makkah & Baiah Aqabah',
            'Para sahabat berhijrah secara berperingkat',
            'Pakatan Quraisy di Darun Nadwah',
            'Malam keluar dari Makkah',
            'Persiapan di rumah Abu Bakar RA',
            'Tiga malam di Gua Thur',
            'Penunjuk jalan & laluan pesisir',
            'Suraqah bin Malik mengejar',
            'Singgah di khemah Ummu Ma\'bad',
            'Tiba di Quba\' dan Madinah',
        ];

        foreach ($titles as $i => $title) {
            $editor->create($arc, [
                'title' => $title,
                'position' => $i + 1,
                'unlock_day_offset' => $i,
            ], $writer, 'Rangka awal (perlu semakan)');
        }

        // Senarai rujukan utama. Butiran (nombor hadis, bab) diisi oleh
        // penulis/penyemak daripada naskhah sebenar — sengaja dibiarkan kosong.
        $sources = [
            ['type' => SourceType::Quran, 'title' => 'Al-Quran', 'reference' => 'Surah At-Taubah: 40'],
            ['type' => SourceType::Hadith, 'title' => 'Sahih al-Bukhari', 'reference' => null],
            ['type' => SourceType::Hadith, 'title' => 'Sahih Muslim', 'reference' => null],
            ['type' => SourceType::SirahBook, 'title' => 'Sirah Ibn Hisham', 'reference' => null],
            ['type' => SourceType::SirahBook, 'title' => 'Ar-Raheeq al-Makhtum (Safiyurrahman al-Mubarakfuri)', 'reference' => null],
        ];

        foreach ($sources as $source) {
            Source::firstOrCreate(
                ['title' => $source['title'], 'reference' => $source['reference']],
                [...$source, 'created_by' => $writer->id],
            );
        }
    }
}
