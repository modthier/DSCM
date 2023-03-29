<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status', function (Blueprint $table) {
            $table->id();
            $table->string('status_english_title');
            $table->string('status_arabic_title');
            $table->string('status_description');
            $table->timestamps();
        });

        DB::table('status')->insert([
            [
                'status_english_title' => 'Onhold', 
                'status_arabic_title' => 'قيد الانتظار', 
                'status_description' => 'The status of an order right after it is initiated.'
            ],
            [
                'status_english_title' => 'Approved', 
                'status_arabic_title' => 'طلب معتمد', 
                'status_description' => 'The status of an approved order.'
            ],
            [
                'status_english_title' => 'Performed', 
                'status_arabic_title' => 'طلب منفذ', 
                'status_description' => 'The status of an already performed order.'
            ],
            [
                'status_english_title' => 'Canceled', 
                'status_arabic_title' => 'طلب ملغي', 
                'status_description' => 'The status of a canceled order.'
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('status');
    }
};
