<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeImageColumnTypeInSkillItemsTable extends Migration
{
    public function up()
    {
        Schema::table('skill_items', function (Blueprint $table) {
            $table->text('image')->change();
        });
    }

    public function down()
    {
        Schema::table('skill_items', function (Blueprint $table) {
            $table->string('image', 255)->change();
        });
    }
}