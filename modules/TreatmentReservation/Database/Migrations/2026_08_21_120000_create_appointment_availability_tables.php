<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dynamic Treatment + Branch appointment availability.
 * All business schedules live in these tables — never hardcode open days/times in PHP.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('spa_branch_weekly_availabilities')) {
            Schema::create('spa_branch_weekly_availabilities', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('spa_branch_id');
                $table->unsignedTinyInteger('day_of_week'); // 0=Sun … 6=Sat (Carbon)
                $table->boolean('is_open')->default(false);
                $table->timestamps();

                $table->unique(['spa_branch_id', 'day_of_week'], 'spa_branch_weekly_day_unique');
                $table->index(['spa_branch_id', 'is_open'], 'spa_branch_weekly_open_idx');
            });
        }

        if (! Schema::hasTable('spa_branch_weekly_availability_slots')) {
            Schema::create('spa_branch_weekly_availability_slots', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('spa_branch_weekly_availability_id');
                $table->time('start_time');
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();

                $table->unique(
                    ['spa_branch_weekly_availability_id', 'start_time'],
                    'spa_branch_weekly_slot_unique'
                );
                $table->foreign('spa_branch_weekly_availability_id', 'spa_branch_weekly_slot_fk')
                    ->references('id')
                    ->on('spa_branch_weekly_availabilities')
                    ->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('treatment_branch_availabilities')) {
            Schema::create('treatment_branch_availabilities', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('product_id');
                $table->unsignedInteger('spa_branch_id');
                $table->unsignedSmallInteger('duration_minutes')->nullable();
                $table->unsignedSmallInteger('capacity_per_slot')->default(1);
                $table->boolean('allow_tba')->default(true);
                $table->boolean('is_bookable')->default(true);
                $table->timestamps();

                $table->unique(['product_id', 'spa_branch_id'], 'treatment_branch_avail_unique');
                $table->index(['spa_branch_id', 'is_bookable'], 'treatment_branch_bookable_idx');
            });
        }

        if (! Schema::hasTable('treatment_branch_weekly_availabilities')) {
            Schema::create('treatment_branch_weekly_availabilities', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('treatment_branch_availability_id');
                $table->unsignedTinyInteger('day_of_week');
                $table->boolean('is_open')->default(false);
                $table->timestamps();

                $table->unique(
                    ['treatment_branch_availability_id', 'day_of_week'],
                    'treatment_branch_weekly_day_unique'
                );
                $table->foreign('treatment_branch_availability_id', 'treatment_branch_weekly_fk')
                    ->references('id')
                    ->on('treatment_branch_availabilities')
                    ->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('treatment_branch_weekly_availability_slots')) {
            Schema::create('treatment_branch_weekly_availability_slots', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('treatment_branch_weekly_availability_id');
                $table->time('start_time');
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();

                $table->unique(
                    ['treatment_branch_weekly_availability_id', 'start_time'],
                    'treatment_branch_weekly_slot_unique'
                );
                $table->foreign('treatment_branch_weekly_availability_id', 'treatment_branch_weekly_slot_fk')
                    ->references('id')
                    ->on('treatment_branch_weekly_availabilities')
                    ->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('appointment_date_overrides')) {
            Schema::create('appointment_date_overrides', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('spa_branch_id');
                // 0 = branch-wide (all treatments). Product-specific overrides use real product_id.
                $table->unsignedInteger('product_id')->default(0);
                $table->date('override_date');
                $table->string('status', 16); // open | closed | custom
                $table->string('reason', 255)->nullable();
                $table->unsignedSmallInteger('capacity_per_slot')->nullable();
                $table->unsignedSmallInteger('duration_minutes')->nullable();
                $table->timestamps();

                $table->unique(
                    ['spa_branch_id', 'product_id', 'override_date'],
                    'appointment_date_override_unique'
                );
                $table->index(['override_date', 'spa_branch_id'], 'appointment_date_override_date_idx');
            });
        }

        if (! Schema::hasTable('appointment_date_override_slots')) {
            Schema::create('appointment_date_override_slots', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('appointment_date_override_id');
                $table->time('start_time');
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();

                $table->unique(
                    ['appointment_date_override_id', 'start_time'],
                    'appointment_date_override_slot_unique'
                );
                $table->foreign('appointment_date_override_id', 'appointment_date_override_slot_fk')
                    ->references('id')
                    ->on('appointment_date_overrides')
                    ->onDelete('cascade');
            });
        }

        if (Schema::hasTable('treatment_bookings') && ! Schema::hasColumn('treatment_bookings', 'spa_branch_id')) {
            Schema::table('treatment_bookings', function (Blueprint $table) {
                $table->unsignedInteger('spa_branch_id')->nullable()->after('beautician_id');
                $table->index(['spa_branch_id', 'appointment_date', 'appointment_time'], 'tb_branch_slot_idx');
            });
        }
    }


    public function down(): void
    {
        Schema::dropIfExists('appointment_date_override_slots');
        Schema::dropIfExists('appointment_date_overrides');
        Schema::dropIfExists('treatment_branch_weekly_availability_slots');
        Schema::dropIfExists('treatment_branch_weekly_availabilities');
        Schema::dropIfExists('treatment_branch_availabilities');
        Schema::dropIfExists('spa_branch_weekly_availability_slots');
        Schema::dropIfExists('spa_branch_weekly_availabilities');

        if (Schema::hasTable('treatment_bookings') && Schema::hasColumn('treatment_bookings', 'spa_branch_id')) {
            Schema::table('treatment_bookings', function (Blueprint $table) {
                $table->dropIndex('tb_branch_slot_idx');
                $table->dropColumn('spa_branch_id');
            });
        }
    }
};
