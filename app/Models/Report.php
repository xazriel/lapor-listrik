<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Report extends Model
{
    protected $fillable = [
        'nama_pelapor',
        'nomor_hp',
        'alamat_lengkap',
        'jenis_gangguan',
        'dampak_wilayah',
        'durasi_padam',
        'urgensi',
        'status',
    ];
}