<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreaksTable extends Migration
{
    public function up(): void
    {
        Schema::create('breaks', function (Blueprint $table) {
            $table->id();

            // 勤怠（出退勤）と紐づけ
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');

            // 休憩開始・終了
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();

            // この休憩の時間（例：0秒）
            $table->unsignedInteger('break_seconds')->nullable();

            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breaks');
    }
};