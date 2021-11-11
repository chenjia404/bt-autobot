<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address_group', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('name',42)->unique();
            $table->string('collection_address',42)->comment("归集地址");
            $table->integer('address_nonce')->comment("矿工费地址nonce");
            $table->string('private_key',128)->comment("矿工费私钥");
            $table->integer('master_address_nonce')->comment("主地址nonce");
            $table->string('master_private_key',128)->comment("主私钥");
            $table->timestamps();
        });

        Schema::create('address', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('address',42)->unique();
            $table->string('private_key',64)->unique();
            $table->unsignedInteger('nonce')->default(0);
            $table->unsignedInteger('group_id')->default(0)->comment("地址组id");
            $table->tinyInteger('is_export')->default(0)->comment('是否导出 1没有导出 2导出');
            $table->integer('last_check_time')->default(0)->unsigned()->comment("上次检查时间");
            $table->timestamps();
        });

        Schema::create('assets_type', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('net_type',16)->default('qki')->comment('主网类型');
            $table->string('contract_address',66)->default('')->comment('合约地址');
            $table->integer('decimals')->comment('精度');
            $table->string('assets_name')->comment('资产名称');
            $table->unique('contract_address');
            $table->timestamps();
        });

        Schema::create('setting', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key',128)->default('')->unique();
            $table->text('value');
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
        Schema::dropIfExists('address_group');
        Schema::dropIfExists('address');
        Schema::dropIfExists('assets_type');
        Schema::dropIfExists('setting');
    }
}
