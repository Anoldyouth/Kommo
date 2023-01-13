<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Phpmig\Migration\Migration;
use Sync\Models\UnisenderToken;

class UnisenderTokens extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        Capsule::schema()->create('unisender_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->timestamps();
        });
        Capsule::schema()->table('accounts', function (Blueprint $table) {
            $table->foreignIdFor(UnisenderToken::class)->nullable();
            $table->json('access_token')->nullable()->change();
        });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('unisender_tokens');
        Capsule::schema()->table('accounts', function (Blueprint $table) {
            $table->json('access_token')->nullable(false)->change();
            $table->dropColumn('unisender_token_id');
        });
    }
}
