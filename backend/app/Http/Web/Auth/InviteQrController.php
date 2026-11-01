<?php

namespace App\Http\Web\Auth;

use App\Http\Api\V1\Coach\InviteQr;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class InviteQrController extends Controller
{
    public function __invoke(string $code): Response
    {
        return response(InviteQr::svg(strtoupper($code)), 200, ['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'private, max-age=3600']);
    }
}
