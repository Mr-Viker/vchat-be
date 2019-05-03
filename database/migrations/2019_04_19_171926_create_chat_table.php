<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('from_id')->index()->comment('聊天发送ID');
            $table->integer('to_id')->index()->comment('聊天接收ID');
            $table->text('content')->comment('内容');
            $table->tinyInteger('content_type')->index()->default(0)->comment('内容类型 0文字 1图片');
            $table->tinyInteger('type')->index()->default(0)->comment('类型 0用户-用户 1用户-群组 2群组-用户');
            $table->tinyInteger('status')->index()->default(0)->comment('发送状态 0未成功 1成功');
            $table->tinyInteger('is_read')->index()->default(0)->comment('查看状态 0未阅 1已阅');
            $table->tinyInteger('is_del')->index()->default(0)->comment('删除状态 0双方未删 >0表示是该id的用户删了)');
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
        Schema::dropIfExists('chat');
    }
}
