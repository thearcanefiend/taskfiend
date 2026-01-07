<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add new date and time columns
            $table->date('date')->nullable()->after('description');
            $table->time('time')->nullable()->after('date');
        });

        // Migrate existing datetime data to date and time columns
        DB::table('tasks')->get()->each(function ($task) {
            if ($task->datetime) {
                $datetime = new DateTime($task->datetime);
                DB::table('tasks')->where('id', $task->id)->update([
                    'date' => $datetime->format('Y-m-d'),
                    'time' => $datetime->format('H:i:s'),
                ]);
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            // Drop the index that includes datetime column
            $table->dropIndex('tasks_datetime_status_index');

            // Drop the old datetime column
            $table->dropColumn('datetime');

            // Re-create the index with date instead of datetime
            $table->index(['date', 'status'], 'tasks_date_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Re-add datetime column
            $table->dateTime('datetime')->nullable()->after('description');
        });

        // Migrate data back to datetime column
        DB::table('tasks')->get()->each(function ($task) {
            if ($task->date) {
                $datetime = $task->date;
                if ($task->time) {
                    $datetime .= ' ' . $task->time;
                }
                DB::table('tasks')->where('id', $task->id)->update([
                    'datetime' => $datetime,
                ]);
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            // Drop the new index
            $table->dropIndex('tasks_date_status_index');

            // Drop date and time columns
            $table->dropColumn(['date', 'time']);

            // Re-create the original index
            $table->index(['datetime', 'status'], 'tasks_datetime_status_index');
        });
    }
};
