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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('english_message');
            $table->string('arabic_message');
            $table->timestamps();
        });

        DB::table('messages')->insert([
            [
                'key' => 'unauthenticated', 
                'english_message' => 'You are not authorized to make this request', 
                'arabic_message' => 'أنت غير مخول لتقديم هذا الطلب'
            ],
            [
                'key' => 'credentials', 
                'english_message' => 'Credentials do not match', 
                'arabic_message' => 'البريد الإلكتروني أو كلمة السر خاطئة'
            ],
            [
                'key' => 'token', 
                'english_message' => 'API Token of ', 
                'arabic_message' => 'رمز دخول المستخدم '
            ],
            [
                'key' => 'logout', 
                'english_message' => 'You have successfully logged out and your token has been deleted', 
                'arabic_message' => 'لقد قمت بتسجيل الخروج بنجاح وتم حذف رمزك دخولك'
            ],
            [
                'key' => 'success', 
                'english_message' => 'The operation was done successfully', 
                'arabic_message' => 'تمت العملية بنجاح'
            ],
            [
                'key' => 'fail', 
                'english_message' => 'Something went wrong', 
                'arabic_message' => 'فشلت العملية'
            ],
            [
                'key' => 'not_found', 
                'english_message' => 'Not Fount', 
                'arabic_message' => 'غير متوفر حاليا'
            ],
            [
                'key' => 'amount_not_available', 
                'english_message' => 'The requested amount is not available', 
                'arabic_message' => 'الكمية المطلوبة غير متوفرة'
            ],
            [
                'key' => 'invalid_stock_number', 
                'english_message' => 'You entered an invalid stock number', 
                'arabic_message' => 'لقد قمت بادخال رقم مخزن غير صحيح'
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
        Schema::dropIfExists('messages');
    }
};
