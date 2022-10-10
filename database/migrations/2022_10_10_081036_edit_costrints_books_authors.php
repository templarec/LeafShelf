<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('books_authors', function (Blueprint $table) {
            $table->dropForeign('books_authors_book_id_foreign');

            $table->foreign('book_id')
                ->references('id')->on('books')
                ->onDelete('cascade')
                ->onUpdate('cascade')
                ->change();

            $table->dropForeign('books_authors_author_id_foreign');
            $table->foreign('author_id')
                ->references('id')->on('authors')
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
