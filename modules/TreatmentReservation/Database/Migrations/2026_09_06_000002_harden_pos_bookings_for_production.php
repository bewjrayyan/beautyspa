<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Entities\File;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("treatment_bookings", function (Blueprint $table) {
            if (! Schema::hasColumn("treatment_bookings", "pos_request_key")) {
                $table->uuid("pos_request_key")->nullable()->after("payment_receipt_file_id");
                $table->unsignedSmallInteger("pos_line_index")->nullable()->after("pos_request_key");
                $table->char("pos_payload_hash", 64)->nullable()->after("pos_line_index");
                $table->unique(["pos_request_key", "pos_line_index"], "treatment_bookings_pos_request_line_unique");
            }
        });

        DB::table("treatment_bookings")
            ->whereNull("customer_id")
            ->whereNotNull("order_id")
            ->orderBy("id")
            ->chunkById(100, function ($bookings): void {
                foreach ($bookings as $booking) {
                    $customerId = DB::table("orders")->where("id", $booking->order_id)->value("customer_id");
                    if ($customerId) {
                        DB::table("treatment_bookings")->where("id", $booking->id)->update(["customer_id" => $customerId]);
                    }
                }
            });

        $receiptIds = DB::table("treatment_bookings")->whereNotNull("payment_receipt_file_id")
            ->distinct()->pluck("payment_receipt_file_id");

        File::query()->whereIn("id", $receiptIds)->where("disk", "!=", "private")
            ->each(function (File $file): void {
                $source = Storage::disk((string) $file->disk);
                $private = Storage::disk("private");
                $paths = array_values(array_filter(array_merge(
                    [(string) $file->getRawOriginal("path")],
                    (array) ($file->responsive_paths ?? [])
                )));
                if ($paths === [] || ! $source->exists($paths[0])) {
                    return;
                }
                $copied = [];
                foreach ($paths as $path) {
                    if ($source->exists($path)) {
                        if (! $private->put($path, $source->get($path))) {
                            $private->delete($copied);
                            throw new RuntimeException("Failed to migrate payment receipt {$file->id} to private storage.");
                        }
                        $copied[] = $path;
                    }
                }
                if (! in_array($paths[0], $copied, true) || ! $private->exists($paths[0])) {
                    $private->delete($copied);
                    throw new RuntimeException("Payment receipt {$file->id} was not verified in private storage.");
                }
                $file->forceFill(["disk" => "private"])->save();
                $source->delete($paths);
            });
    }

    public function down(): void
    {
        if (! Schema::hasColumn("treatment_bookings", "pos_request_key")) {
            return;
        }
        Schema::table("treatment_bookings", function (Blueprint $table) {
            $table->dropUnique("treatment_bookings_pos_request_line_unique");
            $table->dropColumn(["pos_request_key", "pos_line_index", "pos_payload_hash"]);
        });
    }
};
