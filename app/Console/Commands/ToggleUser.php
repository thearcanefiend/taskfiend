<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ToggleUser extends Command
{
    protected $signature = 'user:toggle {email}';

    protected $description = 'Toggle user enabled/disabled status';

    public function handle()
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("No user found with email address: {$email}");
            return 1;
        }

        if ($user->email_enabled_at === null) {
            // Disabling user
            $user->email_enabled_at = now();
            $user->save();

            // Invalidate all active sessions
            DB::table('sessions')->where('user_id', $user->id)->delete();

            // Invalidate all API keys
            $invalidatedCount = DB::table('api_keys')
                ->where('user_id', $user->id)
                ->whereNull('invalidated_at')
                ->update(['invalidated_at' => now()]);

            $this->info("User {$user->email} ({$user->name}) has been disabled.");
            $this->info("All active sessions have been invalidated.");
            $this->info("{$invalidatedCount} API key(s) have been invalidated.");
        } else {
            // Enabling user
            $user->email_enabled_at = null;
            $user->save();
            $this->info("User {$user->email} ({$user->name}) has been enabled.");
            $this->info("Note: API keys remain invalidated. Generate new keys if needed.");
        }

        return 0;
    }
}
