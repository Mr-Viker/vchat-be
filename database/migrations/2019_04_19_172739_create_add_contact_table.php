<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('add_contact', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('from_uid')->index()->comment('添加好友发送用户ID');
            $table->integer('to_uid')->index()->comment('添加好友接收用户ID');
            $table->string('content')->comment('附加内容')->nullable();
            $table->tinyInteger('status')->index()->default(0)->comment('状态 0已发送 1已添加 2已拒绝');
            $table->tinyInteger('is_read')->index()->default(0)->comment('查看状态 0未阅 1已阅');
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
        Schema::dropIfExists('add_contact');
    }
}
