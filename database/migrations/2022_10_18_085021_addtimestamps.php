<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('buildings', 'created_at')) {
            Schema::table('buildings', function (Blueprint $table) {
                $table->timestamps();
                $table->softDeletes();
            });
        }
        if (!Schema::hasColumn('rooms', 'created_at')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->timestamps();
                $table->softDeletes();
            });
        }
        if (!Schema::hasColumn('bookshelves', 'created_at')) {
            Schema::table('bookshelves', function (Blueprint $table) {
                $table->timestamps();
                $table->softDeletes();
            });
        }
        if (!Schema::hasColumn('shelves', 'created_at')) {
            Schema::table('shelves', function (Blueprint $table) {
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
