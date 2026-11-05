<?php

use App\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Route;

// Paparan pemain. Panel kandungan (penulis/penyemak) ada di /panel.
// Tambah ?pratonton=1 (selepas log masuk panel) untuk lihat draf.
Route::get('/', [PlayerController::class, 'home'])->name('player.home');
Route::get('/arka/{arc:slug}', [PlayerController::class, 'arc'])->name('player.arc');
Route::get('/arka/{arc:slug}/bab/{chapter}', [PlayerController::class, 'chapter'])->name('player.chapter');
