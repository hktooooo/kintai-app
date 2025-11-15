<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // ユーザーID
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // 勤務日
            $table->date('work_date');

            // 出勤・退勤
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();

            // 実働時間（休憩を除いた勤務時間）（例：00:00:00時間）
            $table->time('working_hours')->nullable();

            // この日の休憩の合計（例：00:00:00時間）
            $table->time('total_break')->nullable();

            // 状態など
            $table->string('status')->default('absent'); // absent，working, completed, break

            // 修正時の理由
            $table->text('reason')->nullable();

            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
}
