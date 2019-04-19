<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMomentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('moment', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('uid')->index()->comment('用户ID');
            $table->text('content')->comment('朋友圈内容');
            $table->text('imgs')->comment('图片数组')->nullable();
            $table->string('address')->comment('定位地址')->nullable();
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
        Schema::dropIfExists('moment');
    }
}
