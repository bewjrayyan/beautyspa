<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_imports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('batch_code', 32)->unique();
            $table->string('method', 16)->index(); // paste|excel|csv|manual
            $table->string('file_name')->nullable();
            $table->unsignedInteger('uploaded_by')->nullable()->index();
            $table->unsignedInteger('spa_branch_id')->nullable()->index();
            $table->unsignedInteger('beautician_id')->nullable()->index();
            $table->string('source', 64)->default('import')->index();
            $table->unsignedInteger('raw_count')->default(0);
            $table->unsignedInteger('ready_count')->default(0);
            $table->unsignedInteger('unique_count')->default(0);
            $table->unsignedInteger('duplicate_count')->default(0);
            $table->unsignedInteger('existing_count')->default(0);
            $table->unsignedInteger('invalid_count')->default(0);
            $table->unsignedInteger('imported_count')->default(0);
            $table->string('status', 24)->default('completed')->index();
            $table->timestamps();

            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('spa_branch_id')->references('id')->on('spa_branches')->nullOnDelete();
            $table->foreign('beautician_id')->references('id')->on('beauticians')->nullOnDelete();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedInteger('lead_import_id')->nullable()->after('customer_id')->index();
            $table->foreign('lead_import_id')->references('id')->on('lead_imports')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['lead_import_id']);
            $table->dropColumn('lead_import_id');
        });

        Schema::dropIfExists('lead_imports');
    }
};
