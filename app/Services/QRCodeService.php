<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class QRCodeService
{
    public function generateQRCode($data)
    {
        try {
            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => QRCode::ECC_L,
                'scale' => 10,
                'imageBase64' => false,
            ]);

            $qrcode = new QRCode($options);
            $qrData = $qrcode->render(json_encode($data));

            // Générer un nom de fichier unique
            $fileName = 'qrcodes/' . uniqid() . '.png';
            $fullPath = 'public/' . $fileName;

            Log::info('QR code généré avec succès', ['fileName' => $fileName]);

            // Sauvegarder le QR code dans le stockage
            $saved = Storage::put($fullPath, $qrData);

            if (!$saved) {
                Log::error('Échec de la sauvegarde du QR code', ['fileName' => $fileName]);
                throw new \Exception('Impossible de sauvegarder le QR code');
            }

            return $fileName;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du QR code', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
