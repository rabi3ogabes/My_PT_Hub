<?php

namespace App\Http\Web\Account;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportDownloadController extends Controller
{
    public function __invoke(string $user, string $file): StreamedResponse
    {
        abort_unless(preg_match('/^aurum-export-[0-9-]+\.zip$/', $file) === 1, 404);
        $path = 'exports/'.$user.'/'.$file;
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }
}
