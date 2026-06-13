<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::landing')->name('landing');

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'pages::auth.login')->name('login');
    Route::livewire('/register', 'pages::auth.register')->name('register');
});

Route::middleware('auth')->group(function () {
    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');
    Route::livewire('/reservasi', 'pages::reservasi.index')->name('reservasi.index');
    Route::livewire('/reservasi/buat', 'pages::reservasi.create')->name('reservasi.create');
    Route::livewire('/jadwal', 'pages::jadwal.harian')->name('jadwal.harian');
    Route::livewire('/jadwal/mingguan', 'pages::jadwal.mingguan')->name('jadwal.mingguan');

    Route::post('/logout', function () {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('landing');
    })->name('logout');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/dashboard', 'pages::admin.dashboard')->name('dashboard');
    Route::livewire('/ruangan', 'pages::admin.ruangan.index')->name('ruangan.index');
    Route::livewire('/ruangan/tambah', 'pages::admin.ruangan.form')->name('ruangan.create');
    Route::livewire('/ruangan/{id}/edit', 'pages::admin.ruangan.form')->name('ruangan.edit');
    Route::livewire('/pemohon', 'pages::admin.pemohon.index')->name('pemohon.index');
    Route::livewire('/persetujuan', 'pages::admin.persetujuan.index')->name('persetujuan.index');
});
