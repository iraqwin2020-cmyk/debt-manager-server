<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['debtors', 'guarantors'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->json('id_document_images')->nullable()->after('id_document_image');
            });

            DB::table($table)->whereNotNull('id_document_image')->orderBy('id')->each(function ($row) use ($table) {
                DB::table($table)->where('id', $row->id)->update([
                    'id_document_images' => json_encode([$row->id_document_image]),
                ]);
            });

            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('id_document_image');
            });
        }
    }

    public function down(): void
    {
        foreach (['debtors', 'guarantors'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('id_document_image')->nullable()->after('address');
            });

            DB::table($table)->whereNotNull('id_document_images')->orderBy('id')->each(function ($row) use ($table) {
                $images = json_decode($row->id_document_images, true) ?? [];
                DB::table($table)->where('id', $row->id)->update([
                    'id_document_image' => $images[0] ?? null,
                ]);
            });

            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('id_document_images');
            });
        }
    }
};
