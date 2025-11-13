<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\SalesHeader;
use App\Models\HistoryDTE;
use App\Models\Company;
use Illuminate\Support\Facades\Mail;
use App\Mail\sendEmailDTE as sendDTEFiles;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SenEmailDTEController extends Controller
{
    public function SenEmailDTEController($idVenta): \Illuminate\Http\JsonResponse
    {
        $sale = SalesHeader::with('customer', 'warehouse', 'warehouse.company')->find($idVenta);
        if (!$sale) {
            return response()->json([
                'status' => false,
                'message' => 'Venta no encontrada',
            ]);
        }

        $generationCode = $sale->generationCode;

        if (!$generationCode) {
            return response()->json([
                'status' => false,
                'message' => 'La venta no tiene código de generación DTE',
            ]);
        }

        // Obtener DTE desde history_dtes
        $historyDTE = HistoryDTE::where('codigoGeneracion', $generationCode)->first();

        if (!$historyDTE || !$historyDTE->dte) {
            return response()->json([
                'status' => false,
                'message' => 'No se encontró el DTE en el historial',
                'body' => 'El DTE no ha sido generado o no existe en la base de datos',
            ]);
        }

        try {
            // Obtener configuración de la empresa
            $configuracion = Company::find(1);
            if (!$configuracion) {
                return response()->json([
                    'status' => false,
                    'message' => 'No se ha configurado la empresa',
                ]);
            }

            $DTE = $historyDTE->dte;

            // Preparar datos del emisor desde el DTE
            $empresa = [
                'nombre' => $DTE['emisor']['nombre'] ?? $configuracion->company_name ?? 'NOMBRE DE EMPRESA',
                'nit' => $DTE['emisor']['nit'] ?? $configuracion->nit ?? '',
                'nrc' => $DTE['emisor']['nrc'] ?? $configuracion->nrc ?? '',
                'descActividad' => $DTE['emisor']['descActividad'] ?? $configuracion->economic_activity ?? '',
                'direccion' => [
                    'complemento' => $DTE['emisor']['direccion']['complemento'] ?? $configuracion->address ?? ''
                ],
                'telefono' => $DTE['emisor']['telefono'] ?? $configuracion->phone ?? '',
                'correo' => $DTE['emisor']['correo'] ?? $configuracion->email ?? '',
                'web' => $configuracion->web ?? ''
            ];

            // Generar QR code como SVG
            $qrUrl = env('DTE_URL_REPORT') . '/consultaPublica?codGen=' . $generationCode;
            $qrCodeSvg = QrCode::format('svg')
                ->size(200)
                ->errorCorrection('H')
                ->generate($qrUrl);
            $qrDataUrl = 'data:image/svg+xml;base64,' . base64_encode($qrCodeSvg);

            // Determinar tipo de documento
            $tipoDocumento = 'FACTURA ELECTRÓNICA';
            if (isset($DTE['identificacion']['tipoDte'])) {
                $tipoDocumento = match($DTE['identificacion']['tipoDte']) {
                    '01' => 'FACTURA ELECTRÓNICA',
                    '03' => 'COMPROBANTE DE CRÉDITO FISCAL',
                    '14' => 'FACTURA DE EXPORTACIÓN',
                    default => 'DOCUMENTO TRIBUTARIO ELECTRÓNICO'
                };
            }

            // Preparar logo
            $logo = null;
            if ($sale->warehouse && $sale->warehouse->logo) {
                $logo = $sale->warehouse->logo;
            } else {
                $logo = $configuracion->logo;
            }

            // Extraer path del logo si es array
            if (is_array($logo)) {
                $logo = $logo['path'] ?? $logo['filename'] ?? $logo[0] ?? null;
            }

            // Convertir logo a base64 data URL
            $logoDataUrl = null;
            if ($logo) {
                $logoPath = public_path('storage/' . $logo);
                if (file_exists($logoPath)) {
                    $logoData = file_get_contents($logoPath);
                    $logoBase64 = base64_encode($logoData);
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $logoPath);
                    finfo_close($finfo);
                    $logoDataUrl = 'data:' . $mimeType . ';base64,' . $logoBase64;
                }
            }

            // Generar PDF en memoria
            $pdf = Pdf::loadView('DTE.dte-print-pdf', [
                'datos' => [
                    'logo' => $logoDataUrl,
                    'empresa' => $empresa,
                    'tipoDocumento' => $tipoDocumento,
                    'DTE' => $DTE
                ],
                'qr' => $qrDataUrl
            ]);

            $pdfContent = $pdf->output();

            // Crear archivo JSON en memoria
            $jsonContent = json_encode($DTE, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

            // Enviar email con archivos generados en memoria
            Mail::to($sale->customer->email)
                ->send(new sendDTEFiles($jsonContent, $pdfContent, $sale, $generationCode));

            return response()->json([
                'status' => true,
                'message' => 'Email enviado exitosamente',
                'body' => 'Correo enviado a ' . $sale->customer->email,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage(),
                'body' => 'Error al enviar el correo a ' . $sale->customer->email,
            ]);
        }
    }
}
