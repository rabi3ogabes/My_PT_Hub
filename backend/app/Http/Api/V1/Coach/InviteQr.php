<?php

namespace App\Http\Api\V1\Coach;

use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

final class InviteQr
{
    public static function svg(string $code): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::MARKUP_SVG,
            'outputBase64' => false,
            'eccLevel' => \chillerlan\QRCode\Common\EccLevel::M,
            'addQuietzone' => true,
            'svgUseFillAttributes' => true,
            'drawLightModules' => false,
        ]);

        return (new QRCode($options))->render(url('/invite/'.$code));
    }
}
