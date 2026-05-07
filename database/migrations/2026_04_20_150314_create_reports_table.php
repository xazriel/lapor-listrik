<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('reports', function (Blueprint $table) {
        $table->id();
        // Info Pelapor
        $table->string('nama_pelapor');
        $table->string('nomor_hp');
        $table->text('alamat_lengkap');

        // Atribut Klasifikasi (Input untuk C4.5)
        $table->string('jenis_gangguan'); // Misal: Kabel Putus, Trafo Meledak, Padam Total
        $table->string('dampak_wilayah'); // Misal: Perumahan, Fasilitas Umum, Jalan Raya
        $table->integer('durasi_padam');  // Misal: dalam hitungan jam

        // Hasil Output Algoritma
        $table->string('urgensi')->nullable(); // Akan terisi otomatis: Tinggi, Sedang, Rendah
        
        // Status Penanganan
        $table->enum('status', ['pending', 'proses', 'selesai'])->default('pending');
        
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
