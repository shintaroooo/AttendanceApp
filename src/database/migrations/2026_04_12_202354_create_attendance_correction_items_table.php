<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class CreateAttendanceCorrectionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_correction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_correction_id')->constrained()->cascadeOnDelete();
            $table->string('field');
            $table->text('before_value')->nullable();
            $table->text('after_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_correction_items');
    }
}
