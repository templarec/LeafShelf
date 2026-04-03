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
            Schema::table('buildings', function (Blueprint $table) {
                $table->string('key');
            });
            Schema::table('rooms', function (Blueprint $table) {
                $table->string('key');
            });
            Schema::table('bookshelves', function (Blueprint $table) {
                $table->string('key');
            });
            Schema::table('shelves', function (Blueprint $table) {
                $table->string('key');
            });
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
