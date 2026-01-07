<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class InvalidateApiKey extends Command
{
    protected $signature = 'apikey:invalidate {key}';

    protected $description = 'Invalidate an API key';

    public function handle()
    {
        $plainKey = $this->argument('key');

        $apiKeys = ApiKey::whereNull('invalidated_at')->with('user')->get();

        $foundKey = null;
        foreach ($apiKeys as $apiKey) {
            if (Hash::check($plainKey, $apiKey->key_hash)) {
                $foundKey = $apiKey;
                break;
            }
        }

        if (!$foundKey) {
            $this->error("No valid API key found matching the provided key.");
            return 1;
        }

        $foundKey->invalidated_at = now();
        $foundKey->save();

        $this->info("API key invalidated successfully.");
        $this->info("User: {$foundKey->user->email} ({$foundKey->user->name})");
        $this->info("Invalidated at: {$foundKey->invalidated_at}");

        return 0;
    }
}
