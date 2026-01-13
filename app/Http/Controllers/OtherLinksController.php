<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use FastVolt\Helper\Markdown;

class OtherLinksController extends Controller
{
    public function index(Request $request, Task $task)
    {
        $files = Storage::disk('other-links')->files();

        $files = collect($files)->mapWithKeys(function ($value) {
            $name = str_replace(['-', '_'], ' ', pathinfo($value, PATHINFO_FILENAME));
            return [ $value => $name ];
        });

        return view('other.links.list', [
            'files' => $files
        ]);
    }

    public function show(Request $request, string $filename)
    {
        $fileContents = Storage::disk('other-links')->get($filename);

        $markdown = new Markdown();
        $markdown->setContent($fileContents);

        $title = str_replace(['-', '_'], ' ', pathinfo($filename, PATHINFO_FILENAME));

        return view('other.links.show', [
            'title' => $title,
            'fileContents' => $markdown->getHtml()
        ]);

    }
}
