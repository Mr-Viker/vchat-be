<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('phone', 20)->index()->comment('手机号');
            $table->string('code')->comment('验证码');
            $table->text('result')->comment('第三方返回结果')->nullable();
            $table->tinyInteger('type')->index()->default(0)->comment('类型 0注册 1忘记密码');
            $table->tinyInteger('status')->index()->default(0)->comment('状态 0未使用 1已使用');
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
        Schema::dropIfExists('sms');
    }
}
