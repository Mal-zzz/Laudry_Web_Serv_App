<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePesananLaundryTable extends Migration
{
    public function up()
    {
        Schema::create('pesanan_laundry', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('pelanggan_id');
            $table->string('jenis_layanan', 50);
            $table->decimal('berat_kg', 8, 2);
            $table->decimal('total_harga', 12, 2);
            $table->string('status_pesanan', 30)->default('menunggu');
            $table->dateTime('tanggal_masuk');
            $table->dateTime('tanggal_selesai')->nullable();

            $table->timestamps();

            $table->foreign('pelanggan_id')
                ->references('id')
                ->on('pelanggan')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pesanan_laundry');
    }
}
