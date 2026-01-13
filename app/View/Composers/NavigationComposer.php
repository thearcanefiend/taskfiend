<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class NavigationComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $otherLinksFiles = collect();

        try {
            $files = Storage::disk('other-links')->files();
            $otherLinksFiles = collect($files)->mapWithKeys(function ($value) {
                $name = str_replace(['-', '_'], ' ', pathinfo($value, PATHINFO_FILENAME));
                return [$value => $name];
            });
        } catch (\Exception $e) {
            // If directory doesn't exist, just leave collection empty
        }

        $view->with('otherLinksFiles', $otherLinksFiles);
    }
}
