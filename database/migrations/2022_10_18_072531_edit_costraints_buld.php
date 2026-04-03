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
            Schema::table('shelves', function (Blueprint $table) {
                $table->dropForeign('shelves_bookshelf_id_foreign');

                $table->foreign('bookshelf_id')
                    ->references('id')->on('bookshelves')
                    ->onDelete('cascade')
                    ->onUpdate('cascade')
                    ->change();

            });
            Schema::table('bookshelves', function (Blueprint $table) {
                $table->dropForeign('bookshelves_room_id_foreign');

                $table->foreign('room_id')
                    ->references('id')->on('rooms')
                    ->onDelete('cascade')
                    ->onUpdate('cascade')
                    ->change();

            });
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropForeign('rooms_building_id_foreign');

                $table->foreign('building_id')
                    ->references('id')->on('buildings')
                    ->onDelete('cascade')
                    ->onUpdate('cascade')
                    ->change();

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
