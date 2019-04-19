<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->comment('用户名');
            $table->string('vchat_id', 50)->unique()->comment('唯一标识ID');
            $table->string('phone', 20)->unique()->comment('手机号');
            $table->string('password')->comment('密码');
            $table->string('avatar')->comment('头像')->nullable();
            $table->tinyInteger('sex')->default(0)->comment('性别 0男 1女');
            $table->string('area')->comment('地区')->nullable();
            $table->string('signature')->comment('个性签名')->nullable();
            $table->string('moment_bgi')->comment('朋友圈背景图')->nullable();
            $table->tinyInteger('is_test')->index()->default(0)->comment('测试用户 0否 1是');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 0冻结 1激活');
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
        Schema::dropIfExists('user');
    }
}
