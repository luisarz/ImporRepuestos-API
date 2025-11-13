<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Contingency;
use App\Models\Correlative;
use App\Models\HistoryDte;
use App\Models\SalesHeader;
use DateTime;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Options;

use SimpleSoftwareIO\QrCode\Facades\QrCode;


class DTEController extends Controller
{
    public function generarDTE($idVenta): array|JsonResponse
    {
        $configuracion = $this->getConfiguracion();
        if (!$configuracion) {
            return response()->json(['message' => 'No se ha configurado la empresa']);
        }

        $venta = SalesHeader::with('documentType')->find($idVenta);
        if (!$venta) {
            return $this->respuestaFallo('Venta no encontrada');
        }

        if ($venta->is_dte) {
            return $this->respuestaFallo('DTE ya enviado');
        }

        $documentTypes = [
            '01' => 'facturaJson',
            '03' => 'CCFJson',
            '05' => 'CreditNotesJSON',
            '11' => 'ExportacionJson',
            '14' => 'sujetoExcluidoJson',
        ];

        $method = $documentTypes[$venta->documenttype->code] ?? null;

        $response = ($method && method_exists($this, $method))
            ? $this->$method($idVenta)
            : $this->respuestaFallo('Tipo de documento no soportado');
        return response()->json($response);
    }

    private function respuestaFallo($mensaje): array
    {
        return [
            'estado' => 'FALLO',
            'mensaje' => $mensaje,
        ];
    }


    public function getConfiguracion()
    {
        $configuracion = Company::find(1);
        if ($configuracion) {
            return $configuracion;
        } else {
            return null;
        }
    }

    /**
     * Obtiene el DTE completo desde history_dtes usando el código de generación
     *
     * @param string $codigoGeneracion Código de generación del DTE
     * @return array|null Array con los datos del DTE o null si no existe
     */
    public function getDTEFromHistory(string $codigoGeneracion): ?array
    {
        \Log::info('getDTEFromHistory called with: ' . $codigoGeneracion);

        $historyDte = HistoryDte::where('codigoGeneracion', $codigoGeneracion)->first();

        if (!$historyDte) {
            \Log::warning('No history_dte found for codigo: ' . $codigoGeneracion);
            return null;
        }

        \Log::info('Found history_dte ID: ' . $historyDte->id . ', has dte: ' . (!empty($historyDte->dte) ? 'YES' : 'NO'));

        // El campo 'dte' ya está casteado como array en el modelo
        return $historyDte->dte;
    }


    function facturaJson($idVenta): array|jsonResponse
    {
        $factura = SalesHeader::with([
            'warehouse.stablishmentType',
            'warehouse.cashRegisters',
            'documentType',
            'seller',
            'customer',
            'customer.economicActivity',
            'customer.departamento',
            'customer.documentTypeCustomer',
            'saleCondition',
            'paymentMethod',
            'saleDetails',
            'saleDetails.inventory.product'])->find($idVenta);

        // Si document_internal_number es 0, asignar el próximo correlativo
        $error = $this->assignControlNumberIfNeeded($factura);
        if ($error) {
            return $error; // Retornar error si hubo problema al asignar correlativo
        }

        $establishmentType = trim($factura->warehouse->stablishmentType->code);
        $conditionCode = trim($factura->saleCondition->code??'');

        $receptor = [
            "documentType" => $factura->customer->documentType->code ?? null,
            "documentNum" => $factura->customer->document_number ?? null,
            "nit" => null,
            "nrc" => null,
            "name" => isset($factura->customer) ? trim($factura->customer->name ?? '') . " " . trim($factura->customer->last_name ?? '') : null,
            "phoneNumber" => ($number = preg_replace('/\D/', '', $factura->customer?->phone ?? '')) && strlen($number) >= 8 ? $number : null,

            "email" => isset($factura->customer) ? trim($factura->customer->email ?? null) : null,
            "businessName" => isset($factura->customer) ? trim($factura->customer->name ?? '') . " " . trim($factura->customer->last_name ?? '') : null,
            "economicAtivity" => isset($factura->customer->economicactivity) ? trim($factura->customer->economicactivity->code ?? null) : null,
            "address" => isset($factura->customer) ? trim($factura->customer->address ?? null) : null,
            "codeCity" => isset($factura->customer->departamento) ? trim($factura->customer->departamento->code ?? null) : null,
            "codeMunicipality" => isset($factura->customer->municipio) ? trim($factura->customer->municipio->code ?? null) : null,
        ];
        $extencion = [
            "deliveryName" => isset($factura->seller) ? trim($factura->seller->name ?? '') . " " . trim($factura->seller->last_name ?? '') : null,
            "deliveryDoc" => isset($factura->seller) ? str_replace("-", "", $factura->seller->dui ?? '') : null,
        ];
        $items = [];
        $i = 1;
        foreach ($factura->saleDetails as $detalle) {
            $codeProduc = str_pad($detalle->inventory_id, 10, '0', STR_PAD_LEFT);
            $exent= !$detalle->inventory->product->is_taxed;
            $items[] = [
                "itemNum" => $i,
                "itemType" => 1,
                "docNum" => null,
                "code" => $codeProduc,
                "tributeCode" => null,
                "description" => $detalle->inventory->product->description,
                "quantity" => doubleval($detalle->quantity),
                "unit" => 1,
                "except" => $exent,
                "unitPrice" => doubleval(number_format($detalle->price, 8, '.', '')),
                "discountPercentage" => doubleval(number_format($detalle->discount, 8, '.', '')),
                "discountAmount" => doubleval(number_format(0, 8, '.', '')),
                "exemptSale" => doubleval(number_format(0, 8, '.', '')),
                "tributes" => null,
                "psv" => doubleval(number_format($detalle->price, 8, '.', '')),
                "untaxed" => doubleval(number_format(0, 8, '.', '')),
            ];
            $i++;
        }
//        $branchId = auth()->user()->employee->warehouse_id ?? null;
        $branchId = $factura->warehouse_id ?? 1;

        if (!$factura->warehouse_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se ha configurado la empresa',
            ], 400);
        }
        $exiteContingencia = Contingency::where('warehouse_id', $branchId)
            ->where('is_close', 0)->first();
        $uuidContingencia = null;
        $transmissionType = 1;
        if ($exiteContingencia) {
            $uuidContingencia = $exiteContingencia->uuid_hacienda;
            $transmissionType = 2;
        }
        $dte = [
            "documentType" => "01",
            "invoiceId" => intval($factura->document_internal_number),
            "establishmentType" => $establishmentType,
            "conditionCode" => $conditionCode,
            "transmissionType" => $transmissionType,
            "contingency" => $uuidContingencia,
            "receptor" => $receptor,
            "extencion" => $extencion,
            "items" => $items
        ];

//        $dteJSON = json_encode($dte, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
//        return response()->json($dte);

        return $this->processDTE($dte, $idVenta);
    }

    function CCFJson($idVenta): array|JsonResponse
    {
        $factura = SalesHeader::with('warehouse.stablishmenttype', 'warehouse.cashRegisters', 'documentType', 'seller', 'customer', 'customer.economicactivity', 'customer.departamento', 'customer.documenttypecustomer', 'saleCondition', 'paymentMethod', 'saleDetails', 'saleDetails.inventory.product')->find($idVenta);

        // Si document_internal_number es 0, asignar el próximo correlativo
        $error = $this->assignControlNumberIfNeeded($factura);
        if ($error) {
            return $error; // Retornar error si hubo problema al asignar correlativo
        }

        $establishmentType = trim($factura->warehouse->stablishmenttype->code);
        $conditionCode = trim($factura->salescondition->code);
        $receptor = [
            "documentType" => trim($factura->customer->documentType->code) ?? null,
            "documentNum" => trim($factura->customer->nit),
            "nit" => trim(str_replace("-", '', $factura->customer->nit)) ?? null,
            "nrc" => trim(str_replace("-", "", $factura->customer->nrc)) ?? null,
            "name" => trim($factura->customer->name) . " " . trim($factura->customer->last_name) ?? null,
//            "phoneNumber" => isset($factura->customer) ? str_replace(["(", ")", "-", " "], "", $factura->customer->phone ?? '') : null,
            "phoneNumber" => ($number = preg_replace('/\D/', '', $factura->customer?->phone ?? '')) && strlen($number) >= 8 ? $number : null,

            "email" => trim($factura->customer->email) ?? null,
            "address" => trim($factura->customer->address) ?? null,
            "businessName" => null,
            "codeCity" => trim($factura->customer->departamento->code) ?? null,
            "codeMunicipality" => trim($factura->customer->distrito->code) ?? null,
            "economicAtivity" => trim($factura->customer->economicactivity->code ?? null),
        ];
        $extencion = [
            "deliveryName" => trim($factura->seller->name) . " " . trim($factura->seller->last_name) ?? null,
            "deliveryDoc" => trim(str_replace("-", "", $factura->seller->dui)),
        ];
        $items = [];
        $i = 1;
        foreach ($factura->saleDetails as $detalle) {
            $codeProduc = str_pad($detalle->inventory_id, 10, '0', STR_PAD_LEFT);
            $tributes = ["20"];
            $exent= !$detalle->inventory->product->is_taxed;
            $items[] = [
                "itemNum" => intval($i),
                "itemType" => 1,
                "docNum" => null,
                "code" => $codeProduc,
                "tributeCode" => null,
                "description" => trim($detalle->inventory->product->description),
                "quantity" => doubleval($detalle->quantity),
                "unit" => 1,
                "except" => $exent,
                "unitPrice" => doubleval(number_format($detalle->price, 8, '.', '')),
                "discountAmount" => doubleval(number_format(0, 8, '.', '')),
                "discountPercentage" => doubleval(number_format($detalle->discount, 2, '.', '')),
                "exemptSale" => doubleval(number_format(0, 8, '.', '')),
                "tributes" => $tributes,
                "psv" => doubleval(number_format($detalle->price, 8, '.', '')),
                "untaxed" => doubleval(number_format(0, 8, '.', '')),
            ];
            $i++;
        }
        $branchId = auth()->user()->employee->branch_id ?? null;
        if (!$branchId) {
            return [
                'estado' => 'FALLO',
                'mensaje' => 'No se ha configurado la empresa'
            ];
        }
        $exiteContingencia = Contingency::where('warehouse_id', $branchId)
            ->where('is_close', 0)->first();
        $uuidContingencia = null;
        $transmissionType = 1;
        if ($exiteContingencia) {
            $uuidContingencia = $exiteContingencia->uuid_hacienda;
            $transmissionType = 2;
        }
        $dte = [
            "documentType" => "03",
            "invoiceId" => intval($factura->document_internal_number),
            "transmissionType" => $transmissionType,
            "contingency" => $uuidContingencia,
            "establishmentType" => trim($establishmentType),
            "conditionCode" => trim($conditionCode),
            "receptor" => $receptor,
            "extencion" => $extencion,
            "items" => $items
        ];

//        return response()->json($dte);


        return $this->processDTE($dte, $idVenta);
    }

    function CreditNotesJSON($idVenta): array|JsonResponse
    {
        $factura = SalesHeader::with('saleRelated', 'warehouse.stablishmenttype', 'documentType', 'seller', 'customer', 'customer.economicactivity', 'customer.departamento', 'customer.documenttypecustomer', 'saleCondition', 'paymentMethod', 'saleDetails', 'saleDetails.inventory.product')->find($idVenta);

        $establishmentType = trim($factura->warehouse->stablishmenttype->code);
        $conditionCode = 1;//trim($factura->salescondition->code);
        $receptor = [
            "documentType" => $factura->customer->documentType->code ?? null,
            "documentNum" => $factura->customer->document_number,
//            "nit" => $factura->customer->nit,
            "nit" => trim(str_replace("-", '', $factura->customer->nit)) ?? null,
            "nrc" => trim(str_replace("-", '', $factura->customer->nrc)) ?? null,
//            "nrc" => $factura->customer->nrc,
            "name" => isset($factura->customer) ? trim($factura->customer->name ?? '') . " " . trim($factura->customer->last_name ?? '') : null,
            "phoneNumber" => ($number = preg_replace('/\D/', '', $factura->customer?->phone ?? '')) && strlen($number) >= 8 ? $number : null,
            "email" => isset($factura->customer) ? trim($factura->customer->email ?? '') : null,
            "businessName" => isset($factura->customer) ? trim($factura->customer->name ?? '') . " " . trim($factura->customer->last_name ?? '') : null,
            "economicAtivity" => isset($factura->customer->economicactivity) ? trim($factura->customer->economicactivity->code ?? '') : null,
            "address" => isset($factura->customer) ? trim($factura->customer->address ?? '') : null,
            "codeCity" => isset($factura->customer->departamento) ? trim($factura->customer->departamento->code ?? '') : null,
            "codeMunicipality" => isset($factura->customer->distrito) ? trim($factura->customer->distrito->code ?? '') : null,
        ];
        $extencion = [
            "deliveryName" => isset($factura->seller) ? trim($factura->seller->name ?? '') . " " . trim($factura->seller->last_name ?? '') : null,
            "deliveryDoc" => isset($factura->seller) ? str_replace("-", "", $factura->seller->dui ?? '') : null,
        ];
        $items = [];
        $i = 1;
        $tributes = ["20"];

        foreach ($factura->saleDetails as $detalle) {
            $codeProduc = str_pad($detalle->inventory_id, 10, '0', STR_PAD_LEFT);
            $exent= !$detalle->inventory->product->is_taxed;
            $items[] = [
                "itemNum" => $i,
                "itemType" => 1,
                "docNum" => $factura->saleRelated->document_internal_number,
                "code" => $codeProduc,
                "tributeCode" => null,
                "description" => $detalle->inventory->product->description,
                "quantity" => doubleval($detalle->quantity),
                "unit" => 1,
                "except" =>$exent,
                "unitPrice" => doubleval(number_format($detalle->price, 8, '.', '')),
                "discountPercentage" => doubleval(number_format($detalle->discount, 8, '.', '')),
                "discountAmount" => doubleval(number_format(0, 8, '.', '')),
                "exemptSale" => doubleval(number_format(0, 8, '.', '')),
                "tributes" => $tributes,
                "psv" => doubleval(number_format($detalle->price, 8, '.', '')),
                "untaxed" => doubleval(number_format(0, 8, '.', '')),
            ];
            $i++;
        }
        $branchId = auth()->user()->employee->branch_id ?? null;
        if (!$branchId) {
            return false;
        }
        $exiteContingencia = Contingency::where('warehouse_id', $branchId)
            ->where('is_close', 0)->first();
        $uuidContingencia = null;
        $transmissionType = 1;
        if ($exiteContingencia) {
            $uuidContingencia = $exiteContingencia->uuid_hacienda;
            $transmissionType = 2;
        }
        $relatedDocuments[] = [
            "typeDocument" => "03",//$Nota de Credito
            "typeGeneration" => "1",//$factura->saleRelated->document_internal_number,
            "numDocument" => $factura->saleRelated->document_internal_number,
            "dateEmision" => $factura->saleRelated->operation_date,
        ];
        $dte = [
            "documentType" => "05",
            "invoiceId" => intval($factura->document_internal_number),
            "establishmentType" => "02",//$establishmentType,
            "conditionCode" => $conditionCode,
            "transmissionType" => $transmissionType,
            "contingency" => $uuidContingencia,
            "relatedDocuments" => $relatedDocuments,
            "receptor" => $receptor,
            "extencion" => $extencion,
            "items" => $items
        ];

//        $dteJSON = json_encode($dte, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
//        return response()->json($dte);

        return $this->processDTE($dte, $idVenta);
    }

    function ExportacionJson($idVenta): array|jsonResponse
    {
        $factura = SalesHeader::with('warehouse.stablishmenttype', 'warehouse.cashRegisters', 'documentType', 'seller', 'customer', 'customer.economicactivity', 'customer.departamento', 'customer.documenttypecustomer', 'saleCondition', 'paymentMethod', 'saleDetails', 'saleDetails.inventory.product')->find($idVenta);

        // Si document_internal_number es 0, asignar el próximo correlativo
        $error = $this->assignControlNumberIfNeeded($factura);
        if ($error) {
            return $error; // Retornar error si hubo problema al asignar correlativo
        }

        $establishmentType = trim($factura->warehouse->stablishmenttype->code);
        $conditionCode = 1;//trim($factura->salescondition->code);
        $receptor = [
//            "documentType" => $factura->customer->documentType->code ?? null,
//            "documentNum" => $factura->customer->document_number ?? $factura->customer->nit,
//            "name" => isset($factura->customer) ? trim($factura->customer->name ?? '') . " " . trim($factura->customer->last_name ?? '') : null,
//            "nit" => null,
//            "nrc" => null,
//            "phoneNumber" => isset($factura->customer) ? str_replace(["(", ")", "-", " "], "", $factura->customer->phone ?? '') : null,
//            "email" => isset($factura->customer) ? trim($factura->customer->email ?? '') : null,
//            "businessName" => isset($factura->customer) ? trim($factura->customer->name ?? '') . " " . trim($factura->customer->last_name ?? '') : null,
//            "address" => isset($factura->customer) ? trim($factura->customer->address ?? '') : null,
//            "codeMunicipality" => null,// isset($factura->customer->distrito) ? trim($factura->customer->distrito->code ?? '') : null,
//            "codCountry" => "9450",
//            "personType" => 1
            "documentType" => $factura->customer->documentType->code ?? 37,
            "documentNum" => $factura->customer->document_number,
            "nit" => null,
            "nrc" => null,
            "name" => isset($factura->customer) ? trim($factura->customer->name ?? '') . " " . trim($factura->customer->last_name ?? '') : null,
//            "phoneNumber" => isset($factura->customer) ? str_replace(["(", ")", "-", " "], "", $factura->customer->phone ?? '') : null,
            "phoneNumber" => ($number = preg_replace('/\D/', '', $factura->customer?->phone ?? '')) && strlen($number) >= 8 ? $number : null,

            "email" => isset($factura->customer) ? trim($factura->customer->email ?? '') : null,
            "businessName" => isset($factura->customer) ? trim($factura->customer->name ?? '') . " " . trim($factura->customer->last_name ?? '') : null,
            "address" => isset($factura->customer) ? trim($factura->customer->address ?? '') : null,
            "economicAtivity" => null,
            "codeCity" => null,
            "codeMunicipality" => null,
            "codCountry" => $factura->customer->country->code ?? null,
            "personType" => 1
//  },
        ];
        $extencion = ["deliveryName" => isset($factura->seller) ? trim($factura->seller->name ?? '') . " " . trim($factura->seller->last_name ?? '') : null,
            "deliveryDoc" => isset($factura->seller) ? str_replace("-", "", $factura->seller->dui ?? '') : null,];
        $items = [];
        $i = 1;

        foreach ($factura->saleDetails as $detalle) {
            $codeProduc = str_pad($detalle->inventory_id, 10, '0', STR_PAD_LEFT);
            $exent= !$detalle->inventory->product->is_taxed;
            $items[] = ["itemNum" => $i,
                "itemType" => 0,
                "docNum" => "",
                "code" => $codeProduc,
//                "tributeCode" => null,
                "description" => $detalle->inventory->product->description,
                "quantity" => doubleval($detalle->quantity),
                "unit" => 1,
                "except" => $exent,
                "unitPrice" => doubleval(number_format($detalle->price, 8, '.', '')),
                "discount" => 0,
                "discountPercentage" => doubleval(number_format($detalle->discount, 8, '.', '')),
                "discountAmount" => doubleval(number_format(0, 8, '.', '')),
                "exemptSale" => doubleval(number_format(0, 8, '.', '')),
                "tributes" => null,
                "psv" => 0,// doubleval(number_format($detalle->price, 8, '.', '')),
                "untaxed" => doubleval(number_format(0, 8, '.', '')),];
            $i++;
        }

        $branchId = auth()->user()->employee->branch_id ?? null;
        if (!$branchId) {
            return [
                'estado' => 'FALLO',
                'mensaje' => 'No se ha configurado la empresa'
            ];
        }
        $exiteContingencia = Contingency::where('warehouse_id', $branchId)
            ->where('is_close', 0)->first();
        $uuidContingencia = null;
        $transmissionType = 1;
        if ($exiteContingencia) {
            $uuidContingencia = $exiteContingencia->uuid_hacienda;
            $transmissionType = 2;
        }
        $foot = [
            "fiscalPrecinct" => null,
            "regimen" => null,
            "itemExportype" => 2,
            "incoterms" => null,
            "description" => "string",
            "freight" => null,
            "insurance" => null,
            "relatedDocuments" => null,
            "transmissionType" => 1
        ];
        $dte = [
            "documentType" => "11",//Factura de exportacion
            "invoiceId" => intval($factura->document_internal_number),
            "establishmentType" => "02",//;$establishmentType,
            "conditionCode" => $conditionCode,
            "transmissionType" => $transmissionType,
            "contingency" => $uuidContingencia,
            "receptor" => $receptor,
            "extencion" => $extencion,
            "items" => $items,
            "fiscalPrecinct" => null,
            "regimen" => null,
            "itemExportype" => 2,
            "incoterms" => null,
            "description" => "string",
            "freight" => 0,
            "insurance" => 0,
            "relatedDocuments" => null,
            "transmissionType" => 1
        ];

//        $dteJSON = json_encode($dte, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
//        return response()->json($dte);

        return $this->processDTE($dte, $idVenta);
    }

    function sujetoExcluidoJson($idVenta): array|jsonResponse
    {
        $factura = SalesHeader::with('warehouse.stablishmenttype', 'warehouse.cashRegisters', 'documentType', 'seller', 'customer', 'customer.economicactivity', 'customer.departamento', 'customer.documenttypecustomer', 'saleCondition', 'paymentMethod', 'saleDetails', 'saleDetails.inventory.product')->find($idVenta);

        // Si document_internal_number es 0, asignar el próximo correlativo
        $error = $this->assignControlNumberIfNeeded($factura);
        if ($error) {
            return $error; // Retornar error si hubo problema al asignar correlativo
        }

        $establishmentType = trim($factura->warehouse->stablishmenttype->code);
        $conditionCode = (int)trim($factura->salescondition->code ?? 1);

        $receptor = [
            "documentType" => $factura->customer->documentType->code ?? null,
            "documentNum" => isset($factura->customer) ? str_replace(["(", ")", "-", " "], "", $factura->customer->nit ?? '') : null,
            "nit" => $factura->customer->nit??null,
            "nrc" => $factura->customer->nrc ?? null,
            "name" => isset($factura->customer) ? trim($factura->customer->name ?? '') . " " . trim($factura->customer->last_name ?? '') : null,
//            "phoneNumber" => isset($factura->customer) ? str_replace(["(", ")", "-", " "], "", $factura->customer->phone ?? '') : null,
            "phoneNumber" => ($number = preg_replace('/\D/', '', $factura->customer?->phone ?? '')) && strlen($number) >= 8 ? $number : null,

            "email" => isset($factura->customer) ? trim($factura->customer->email ?? '') : null,
            "businessName" => isset($factura->customer) ? trim($factura->customer->name ?? '') . " " . trim($factura->customer->last_name ?? '') : null,
            "economicAtivity" => isset($factura->customer->economicactivity) ? trim($factura->customer->economicactivity->code ?? '') : null,
            "address" => isset($factura->customer) ? trim($factura->customer->address ?? '') : null,
            "codeCity" => isset($factura->customer->departamento) ? trim($factura->customer->departamento->code ?? '') : null,
            "codeMunicipality" => isset($factura->customer->distrito) ? trim($factura->customer->distrito->code ?? '') : null,
        ];
        $extencion = [
            "deliveryName" => isset($factura->seller) ? trim($factura->seller->name ?? '') . " " . trim($factura->seller->last_name ?? '') : null,
            "deliveryDoc" => isset($factura->seller) ? str_replace("-", "", $factura->seller->dui ?? '') : null,
        ];
        $items = [];
        $i = 1;
        foreach ($factura->saleDetails as $detalle) {
            $codeProduc = str_pad($detalle->inventory_id, 10, '0', STR_PAD_LEFT);
            $exent= !$detalle->inventory->product->is_taxed;
            $items[] = [
                "itemNum" => $i,
                "itemType" => 1,
                "docNum" => null,
                "code" => $codeProduc,
                "tributeCode" => null,
                "description" => $detalle->inventory->product->description,
                "quantity" => doubleval($detalle->quantity),
                "unit" => 1,
                "except" => $exent,
                "unitPrice" => doubleval(number_format($detalle->price, 8, '.', '')),
                "discountPercentage" => doubleval(number_format($detalle->discount, 8, '.', '')),
                "discountAmount" => doubleval(number_format(0, 8, '.', '')),
                "exemptSale" => doubleval(number_format(0, 8, '.', '')),
                "tributes" => null,
                "psv" => doubleval(number_format($detalle->price, 8, '.', '')),
                "untaxed" => doubleval(number_format(0, 8, '.', '')),
            ];
            $i++;
        }
        $branchId = auth()->user()->employee->branch_id ?? null;
        if (!$branchId) {
            return [
                'estado' => 'FALLO',
                'mensaje' => 'No se ha configurado la empresa'
            ];
        }
        $exiteContingencia = Contingency::where('warehouse_id', $branchId)
            ->where('is_close', 0)->first();
        $uuidContingencia = null;
        $transmissionType = 1;
        if ($exiteContingencia) {
            $uuidContingencia = $exiteContingencia->uuid_hacienda;
            $transmissionType = 2;
        }
        $dte = [
            "documentType" => "14",
            "invoiceId" => intval($factura->document_internal_number),
            "establishmentType" => "02",//$establishmentType,
            "conditionCode" => $conditionCode,
//            "transmissionType" => $transmissionType,
            "contingency" => $uuidContingencia,
            "receptor" => $receptor,
            "extencion" => $extencion,
            "items" => $items,
            "fiscalPrecinct" => null,
            "regimen" => null,
            "ItemExportype" => 3,
            "incoterms" => null,
            "description" => null,
            "freight" => null,
            "insurance" => null,
            "relatedDocuments" => null,
            "transmissionType" => 1
        ];

//        $dteJSON = json_encode($dte, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
//return response()->json($dte);

        return $this->processDTE($dte, $idVenta);
    }

    function SendDTE($dteData, $idVenta, $documentInfo = []): array|JsonResponse // Assuming $dteData is the data you need to send
    {
        set_time_limit(0);
        try {
//            echo env(DTE_TEST);
            $urlAPI = env('DTE_URL') .'/api/DTE/generateDTE'; // Set the correct API URL
            $apiKey = trim($this->getConfiguracion()->api_key_mh); // Assuming you retrieve the API key from your config
            // Convert data to JSON format
            $dteJSON = json_encode($dteData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $urlAPI,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $dteJSON,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'apiKey: ' . $apiKey
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            // Check for cURL errors
            if ($response === false) {
                return [
                    'estado' => 'RECHAZADO',
                    'response' => false,
                    'code' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
                    'mensaje' => "Ocurrio un eror" . curl_error($curl)
                ];
            }

            //fecha contingencia
            //estado contingencia
            //

//            dd($response);
//            return response()->json($response);

            $responseData = json_decode($response, true);


            //validar si respuesta hacienda es null pero tiene firma, si es asi es contingencia


            $responseHacienda = (isset($responseData["estado"]) == "RECHAZADO") ? $responseData : $responseData["respuestaHacienda"];
//            $responseHacienda = isset($responseData["estado"]) && $responseData["estado"] === "RECHAZADO"
//                ? $responseData
//                : ($responseData["respuestaHacienda"] ?? $responseData["identificacion"] ?? null);
//            $responseData["estado"] = "procesado"; // Reemplaza con el valor deseado
//            if (isset($responseHacienda["valueKind"]) && $responseHacienda["valueKind"] == 1) {
//                $responseData["estado"] = "EXITO";
//            }


            $falloDTE = new HistoryDte;
            $ventaID = intval($idVenta);
            $falloDTE->sales_invoice_id = $ventaID;
            $falloDTE->document_type = $documentInfo['document_type'] ?? null;
            $falloDTE->document_number = $documentInfo['document_number'] ?? null;
            $falloDTE->version = $responseHacienda["version"] ?? 0;
            $falloDTE->ambiente = $responseHacienda["ambiente"] ?? 0;
            $falloDTE->versionApp = $responseHacienda["versionApp"] ?? 0;
            $falloDTE->estado = $responseHacienda["estado"] ?? null;
            $falloDTE->codigoGeneracion = $responseHacienda["codigoGeneracion"] ?? null;
            $falloDTE->contingencia = $responseHacienda["tipoContingencia"] ?? null;
            $falloDTE->motivo_contingencia = $responseHacienda["motivoContin"] ?? null;
            $falloDTE->selloRecibido = $responseHacienda["selloRecibido"] ?? null;
            $falloDTE->num_control = $responseData["identificacion"]['numeroControl'] ?? null;
            if (isset($responseHacienda["fhProcesamiento"])) {
                $fhProcesamiento = DateTime::createFromFormat('d/m/Y H:i:s', $responseHacienda["fhProcesamiento"]);
                $falloDTE->fhProcesamiento = $fhProcesamiento ? $fhProcesamiento->format('Y-m-d H:i:s') : null;
            } else {
                $falloDTE->fhProcesamiento = null;
            }

            $falloDTE->clasificaMsg = $responseHacienda["clasificaMsg"] ?? null;
            $falloDTE->codigoMsg = $responseHacienda["codigoMsg"] ?? null;
            $falloDTE->descripcionMsg = $responseHacienda["descripcionMsg"] ?? null;

            // Usar el método formatObservaciones para normalizar el formato
            $observacionesRaw = $responseHacienda["observaciones"] ?? $responseHacienda["descripcion"] ?? null;
            $falloDTE->observaciones = $this->formatObservaciones($observacionesRaw);

            $falloDTE->dte = $responseData ?? null;
            $falloDTE->save();

//            $responseData['json_send']= $dteData;

            return $responseData;

        } catch (Exception $e) {
            $data = [
                'estado' => 'RECHAZADO',
                'mensaje' => "Ocurrio un eror " . $e
            ];
            return $data;
        }
    }


    public
    function anularDTE($idVenta): array|JsonResponse
    {


        if ($this->getConfiguracion() == null) {
            return response()->json(['message' => 'No se ha configurado la empresa']);
        }
        $venta = SalesHeader::with([
            'warehouse.stablishmentType',
            'seller',
            'documentType',
            'saleCondition',
            'paymentMethod',
            'dteProcesado' => function ($query) {
                $query->where('estado', 'PROCESADO');
            }
        ])->find($idVenta);

//        return response()->json($venta);

        if (!$venta) {
            return [
                'estado' => 'FALLO', // o 'ERROR'
                'mensaje' => 'Venta no encontrada',
            ];
        }
        if (!$venta->is_dte) {
            return [
                'estado' => 'FALLO', // o 'ERROR'
                'mensaje' => 'DTE no generado aun',
            ];
        }

        if ($venta->status == "Anulado") {
            return [
                'estado' => 'FALLO', // o 'ERROR'
                'mensaje' => 'DTE Ya fue anulado ',
            ];
        }

        $codigoGeneracion = $venta->dteProcesado->codigoGeneracion;
        $establishmentType = trim($venta->warehouse->stablishmenttype->code);
        $user = \Auth::user()->employee;
        $dte = [
            "codeGeneration" => $codigoGeneracion,
            "codeGenerationR" => null,
            "description" => "Anulación de la operación",
            "establishmentType" => $establishmentType,
            "type" => 2,
            "responsibleName" => $venta->seller->name . " " . $venta->seller->lastname,
            "responsibleDocType" => "13",
            "responsibleDocNumber" => trim(str_replace("-", "", $venta->seller->dui)),
            "requesterName" => $user->name . " " . $user->lastname,
            "requesterDocType" => "13",
            "requesterDocNumber" => trim(str_replace("-", "", $user->dui)),
        ];


//        return response()->json($dte);
        $responseData = $this->SendAnularDTE($dte, $idVenta);
//        return response()->json($responseData);
        $reponse_anular = $responseData['response_anular'] ?? null;
        if (isset($reponse_anular['estado']) == "RECHAZADO") {
            return [
                'estado' => 'FALLO', // o 'ERROR'
                'mensaje' => 'DTE falló al enviarse: ' . implode(', ', $responseData['observaciones'] ?? []), // Concatenar observaciones
                'descripcionMsg' => $reponse_anular['descripcionMsg'] ?? null,
                'codigoGeneracion' => $codigoGeneracion['codigoGeneracion'] ?? null
            ];
        } else {
            $venta = SalesHeader::find($idVenta);
            $venta->sale_status = 3;
            $venta->save();
            return [
                'estado' => 'EXITO',
                'mensaje' => 'DTE ANULADO correctamente',
            ];
        }
    }

    function SendAnularDTE($dteData, $idVenta) // Assuming $dteData is the data you need to send
    {
        try {
            $urlAPI = env('DTE_URL').'/api/DTE/cancellationDTE'; // Set the correct API URL
            $apiKey = $this->getConfiguracion()->api_key_mh; // Assuming you retrieve the API key from your config

            // Convert data to JSON format
            $dteJSON = json_encode($dteData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $urlAPI,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $dteJSON,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'apiKey: ' . $apiKey
                ),
            ));

            $response = curl_exec($curl);
//            dd($response);

            // Check for cURL errors
            if ($response === false) {
                return [
                    'estado' => 'RECHAZADO ',
                    'mensaje' => "Ocurrio un eror" . curl_error($curl)
                ];
            }

            curl_close($curl);

            $responseData = json_decode($response, true);
//            dd  ($responseData);
            $responseData['response_anular'] = json_decode($responseData['descripcion'], true) ?? [];
//            dd($responseData);
            $response_anular = $responseData['response_anular'] ?? [];

            $observacion_fail = str_replace(['[', ']', '"'], '', $response_anular['descripcionMsg'] ?? '');

            $responseHacienda = ($responseData["estado"] == "RECHAZADO") ? $responseData : $responseData["respuestaHacienda"];
            $falloDTE = new HistoryDte;
            $falloDTE->sales_invoice_id = $idVenta;
            $falloDTE->document_type = $venta->documentType->code ?? null;
            $falloDTE->document_number = $venta->document_internal_number ?? null;
            $falloDTE->version = $responseHacienda["version"] ?? 2;
            $falloDTE->ambiente = $responseHacienda["ambiente"] ?? "00";
            $falloDTE->versionApp = $responseHacienda["versionApp"] ?? 2;
            $falloDTE->estado = $responseHacienda["estado"] ?? "RECHAZADO";
            $falloDTE->codigoGeneracion = $responseHacienda["codigoGeneracion"] ?? null;
            $falloDTE->selloRecibido = $responseHacienda["selloRecibido"] ?? null;

            $fhProcesamiento = DateTime::createFromFormat('d/m/Y H:i:s', $responseHacienda["fhProcesamiento"] ?? null);
            $falloDTE->fhProcesamiento = $fhProcesamiento ? $fhProcesamiento->format('Y-m-d H:i:s') : null;

            $falloDTE->clasificaMsg = $responseHacienda["clasificaMsg"] ?? null;
            $falloDTE->codigoMsg = $responseHacienda["codigoMsg"] ?? null;
            $falloDTE->descripcionMsg = $responseHacienda["descripcionMsg"] ?? null;

            // Usar el método formatObservaciones para normalizar el formato
            $observacionesRaw = $responseHacienda["observaciones"] ?? $observacion_fail ?? null;
            $falloDTE->observaciones = $this->formatObservaciones($observacionesRaw);

            $falloDTE->dte = json_encode($responseData, JSON_UNESCAPED_UNICODE);

            $falloDTE->save();

            return $responseData;

        } catch (Exception $e) {
            $data = [
                'estado' => 'RECHAZADO ',
                'mensaje' => "Ocurrio un eror" . $e->getMessage()
            ];
            return $data;
        }
    }

    public
    function printDTETicket($codGeneracion)
    {
        // Obtener DTE desde history_dtes usando el nuevo método
        $DTE = $this->getDTEFromHistory($codGeneracion);

        // Si no existe en history_dtes, intentar obtenerlo de Hacienda
        if (!$DTE) {
            $jsonResponse = $this->getDTE($codGeneracion);

            if (isset($jsonResponse->original) && is_array($jsonResponse->original)) {
                $this->saveRestoreJson($jsonResponse->original, $codGeneracion);
                return response()->json([
                    'estado' => 'Error',
                    'mensaje' => 'El DTE no estaba en el historial, pero fue solicitado a Hacienda. Por favor intente nuevamente.',
                ]);
            } else {
                return response()->json([
                    'estado' => 'Error',
                    'mensaje' => 'No se pudo obtener el DTE. Verifique el código de generación.',
                ]);
            }
        }

        // Obtener la venta asociada al código de generación
        $venta = SalesHeader::where('generationCode', $codGeneracion)->with('warehouse')->first();

        // Obtener configuración de la empresa
        $configuracion = $this->getConfiguracion();
        if (!$configuracion) {
            return response()->json([
                'estado' => 'Error',
                'mensaje' => 'No se ha configurado la empresa',
            ]);
        }

        // Preparar datos para la vista - tomar del DTE (fuente de verdad)
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

        // Generar QR code como SVG (compatible con Dompdf)
        $qrUrl = env('DTE_URL_REPORT') . '/consultaPublica?codGen=' . $codGeneracion;
        $qrCodeSvg = QrCode::format('svg')
            ->size(150)
            ->errorCorrection('H')
            ->generate($qrUrl);

        // Convertir SVG a data URL
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

        // Preparar logo como base64 - prioridad: logo de sucursal, sino logo de empresa
        $logo = null;
        if ($venta && $venta->warehouse && $venta->warehouse->logo) {
            $logo = $venta->warehouse->logo;
            \Log::info('Logo de sucursal encontrado: ' . (is_array($logo) ? json_encode($logo) : $logo));
        } else {
            // Usar logo de la configuración general de la empresa
            $logo = $configuracion->logo;
            \Log::info('Logo de empresa: ' . ($logo ? (is_array($logo) ? json_encode($logo) : $logo) : 'null'));
        }

        // Si el logo es un array/objeto JSON, extraer el path
        if (is_array($logo)) {
            $logo = $logo['path'] ?? $logo['filename'] ?? $logo[0] ?? null;
            \Log::info('Logo después de extraer path: ' . ($logo ?? 'null'));
        }

        // Convertir logo a base64 data URL
        $logoDataUrl = null;
        if ($logo) {
            $logoPath = public_path('storage/' . $logo);
            \Log::info('Buscando logo en: ' . $logoPath);

            if (file_exists($logoPath)) {
                \Log::info('Logo encontrado, convirtiendo a base64');
                $logoData = file_get_contents($logoPath);
                $logoBase64 = base64_encode($logoData);
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $logoPath);
                finfo_close($finfo);
                $logoDataUrl = 'data:' . $mimeType . ';base64,' . $logoBase64;
            } else {
                \Log::warning('Logo no encontrado en path: ' . $logoPath);
            }
        } else {
            \Log::warning('No hay logo configurado');
        }

        $datos = [
            'logo' => $logoDataUrl,
            'empresa' => $empresa,
            'tipoDocumento' => $tipoDocumento,
            'DTE' => $DTE
        ];

        // Generar PDF usando Dompdf (formato ticket)
        $pdf = Pdf::loadView('DTE.dte-print-ticket', [
            'datos' => $datos,
            'qr' => $qrDataUrl
        ]);

        // Configurar tamaño de página para ticket (80mm de ancho)
        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait'); // 80mm x 297mm

        // Retornar PDF como base64
        $pdfContent = $pdf->output();
        $pdfBase64 = base64_encode($pdfContent);

        return response()->json([
            'success' => true,
            'pdf' => $pdfBase64,
            'filename' => 'TICKET_' . $codGeneracion . '.pdf'
        ]);
    }

    public
    function printDTEPdf($codGeneracion)
    {
        // Obtener DTE desde history_dtes usando el nuevo método
        $DTE = $this->getDTEFromHistory($codGeneracion);

        // Si no existe en history_dtes, intentar obtenerlo de Hacienda
        if (!$DTE) {
            $jsonResponse = $this->getDTE($codGeneracion);

            if (isset($jsonResponse->original) && is_array($jsonResponse->original)) {
                $this->saveRestoreJson($jsonResponse->original, $codGeneracion);
                return response()->json([
                    'estado' => 'Error',
                    'mensaje' => 'El DTE no estaba en el historial, pero fue solicitado a Hacienda. Por favor intente nuevamente.',
                ]);
            } else {
                return response()->json([
                    'estado' => 'Error',
                    'mensaje' => 'No se pudo obtener el DTE. Verifique el código de generación.',
                ]);
            }
        }

        // Obtener la venta asociada al código de generación
        $venta = SalesHeader::where('generationCode', $codGeneracion)->with('warehouse')->first();

        // Obtener configuración de la empresa
        $configuracion = $this->getConfiguracion();
        if (!$configuracion) {
            return response()->json([
                'estado' => 'Error',
                'mensaje' => 'No se ha configurado la empresa',
            ]);
        }

        // Preparar datos para la vista - tomar del DTE (fuente de verdad)
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

        // Generar QR code como SVG (compatible con Dompdf)
        $qrUrl = env('DTE_URL_REPORT') . '/consultaPublica?codGen=' . $codGeneracion;
        $qrCodeSvg = QrCode::format('svg')
            ->size(200)
            ->errorCorrection('H')
            ->generate($qrUrl);

        // Convertir SVG a data URL
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

        // Preparar logo como base64 - prioridad: logo de sucursal, sino logo de empresa
        $logo = null;
        if ($venta && $venta->warehouse && $venta->warehouse->logo) {
            $logo = $venta->warehouse->logo;
            \Log::info('Logo de sucursal encontrado: ' . (is_array($logo) ? json_encode($logo) : $logo));
        } else {
            // Usar logo de la configuración general de la empresa
            $logo = $configuracion->logo;
            \Log::info('Logo de empresa: ' . ($logo ? (is_array($logo) ? json_encode($logo) : $logo) : 'null'));
        }

        // Si el logo es un array/objeto JSON, extraer el path
        if (is_array($logo)) {
            $logo = $logo['path'] ?? $logo['filename'] ?? $logo[0] ?? null;
            \Log::info('Logo después de extraer path: ' . ($logo ?? 'null'));
        }

        // Convertir logo a base64 data URL
        $logoDataUrl = null;
        if ($logo) {
            $logoPath = public_path('storage/' . $logo);
            \Log::info('Buscando logo en: ' . $logoPath);

            if (file_exists($logoPath)) {
                \Log::info('Logo encontrado, convirtiendo a base64');
                $logoData = file_get_contents($logoPath);
                $logoBase64 = base64_encode($logoData);
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $logoPath);
                finfo_close($finfo);
                $logoDataUrl = 'data:' . $mimeType . ';base64,' . $logoBase64;
            } else {
                \Log::warning('Logo no encontrado en path: ' . $logoPath);
            }
        } else {
            \Log::warning('No hay logo configurado');
        }

        $datos = [
            'logo' => $logoDataUrl,
            'empresa' => $empresa,
            'tipoDocumento' => $tipoDocumento,
            'DTE' => $DTE
        ];

        // Generar PDF usando Dompdf
        $pdf = Pdf::loadView('DTE.dte-print-pdf', [
            'datos' => $datos,
            'qr' => $qrDataUrl
        ]);

        // Retornar PDF como base64
        $pdfContent = $pdf->output();
        $pdfBase64 = base64_encode($pdfContent);

        return response()->json([
            'success' => true,
            'pdf' => $pdfBase64,
            'filename' => 'DTE_' . $codGeneracion . '.pdf'
        ]);
    }

    public
    function getDTE($codGeneracion)
    {
        set_time_limit(0);
        try {
//            echo env(DTE_TEST);
            $urlAPI = env('DTE_URL_REPORT') .'/api/DTE/json/'.$codGeneracion; // Set the correct API URL
            $apiKey = trim($this->getConfiguracion()->api_key_mh); // Assuming you retrieve the API key from your config
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $urlAPI,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'apiKey: ' . $apiKey
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);





            $responseData = json_decode($response, true);

            return response()->json($responseData);


        } catch (Exception $e) {
            $data = [
                'estado' => 'RECHAZADO',
                'mensaje' => "Ocurrio un eror " . $e
            ];
            return $data;
        }


    }

    /**
     * @param mixed $responseData
     * @param $idVenta
     * @return void
     */
    public
    function saveJson(mixed $responseData, $idVenta, $enviado_hacienda): void
    {
        $codGeneration = $responseData['respuestaHacienda']['codigoGeneracion'] ?? $responseData['identificacion']['codigoGeneracion'] ?? null;

        // Ya no guardamos el JSON en disco, solo en history_dtes
        // El DTE completo ya está siendo guardado en history_dtes.dte por el método SendDTE

        $venta = SalesHeader::find($idVenta);
        $venta->is_dte = true;
        $venta->is_dte_send = $enviado_hacienda;
        $venta->generationCode = $codGeneration ?? null;
        // Mantenemos jsonUrl como null ya que no guardamos archivos físicos
        $venta->jsonUrl = null;
        $venta->save();
    }

    function searchInArray($clave, $array)
    {
        if (array_key_exists($clave, $array)) {
            return $array[$clave];
        } else {
            return 'Clave no encontrada';
        }
    }

    /**
     * Normaliza y formatea las observaciones para guardar en la base de datos
     *
     * @param array|string|null $observaciones
     * @return string|null
     */
    private function formatObservaciones($observaciones): ?string
    {
        if (empty($observaciones)) {
            return null;
        }

        // Si ya es un string, retornarlo directamente
        if (is_string($observaciones)) {
            return $observaciones;
        }

        // Si es un array, procesarlo
        if (is_array($observaciones)) {
            // Si el array está vacío, retornar null
            if (count($observaciones) === 0) {
                return null;
            }

            // Si el array contiene strings simples, unirlos con saltos de línea
            $isSimpleArray = true;
            foreach ($observaciones as $item) {
                if (!is_string($item) && !is_numeric($item)) {
                    $isSimpleArray = false;
                    break;
                }
            }

            if ($isSimpleArray) {
                // Unir observaciones con saltos de línea para mejor legibilidad
                return implode("\n", array_filter($observaciones));
            }

            // Si el array contiene objetos o arrays anidados, convertir a JSON formateado
            return json_encode($observaciones, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        // Para cualquier otro tipo, convertir a string
        return (string) $observaciones;
    }

    /**
     * @param array $dte
     * @param $idVenta
     * @return array
     */
    public
    function processDTE(array $dte, $idVenta): array|jsonResponse
    {
        // Obtener la venta para asignar correlativo si es necesario
        $venta = SalesHeader::with(['cashRegister', 'documentType', 'cashOpening', 'warehouse'])->findOrFail($idVenta);

        // Validar que la venta tenga una apertura de caja asociada
        if (!$venta->cashbox_open_id) {
            return [
                'estado' => 'FALLO',
                'mensaje' => 'No se puede enviar el DTE. La venta no tiene una caja registradora asociada.',
            ];
        }

        // Verificar el estado de la apertura de caja asociada
        $cashOpening = \App\Models\CashOpening::find($venta->cashbox_open_id);
        if (!$cashOpening) {
            return [
                'estado' => 'FALLO',
                'mensaje' => 'No se puede enviar el DTE. La apertura de caja asociada no existe.',
            ];
        }

        // Si la caja está cerrada, buscar una caja abierta en la misma sucursal y actualizar
        if ($cashOpening->status !== 'open') {
            // Buscar una caja abierta en la sucursal de la venta
            $openCashRegister = \App\Models\CashRegister::where('warehouse_id', $venta->warehouse_id)
                ->where('is_active', 1)
                ->whereHas('currentOpening')
                ->with('currentOpening')
                ->first();

            if (!$openCashRegister || !$openCashRegister->currentOpening) {
                return [
                    'estado' => 'FALLO',
                    'mensaje' => 'No se puede enviar el DTE. No hay una caja registradora abierta en esta sucursal. Por favor, abra una caja para poder enviar documentos a Hacienda.',
                ];
            }

            // Actualizar el cashbox_open_id de la venta a la caja actualmente abierta
            DB::beginTransaction();
            try {
                $venta->cashbox_open_id = $openCashRegister->currentOpening->id;
                $venta->save();
                DB::commit();

                // Actualizar la referencia local
                $cashOpening = $openCashRegister->currentOpening;

                \Log::info("Venta {$idVenta}: cashbox_open_id actualizado de {$venta->getOriginal('cashbox_open_id')} a {$cashOpening->id} para el envío de DTE");
            } catch (\Exception $e) {
                DB::rollBack();
                return [
                    'estado' => 'FALLO',
                    'mensaje' => 'Error al actualizar la apertura de caja: ' . $e->getMessage(),
                ];
            }
        }

        // Si no tiene document_internal_number asignado, asignar el correlativo
        if (!$venta->document_internal_number) {
            try {
                // Obtener la caja registradora desde la apertura de caja
                $cashOpening = \App\Models\CashOpening::with('cashRegister')->findOrFail($venta->cashbox_open_id);
                $cashRegisterId = $cashOpening->cash_register_id;

                // Obtener el correlativo activo para esta caja y tipo de documento
                $correlative = \App\Models\Correlative::getActiveCorrelative($cashRegisterId, $venta->document_type_id);

                if (!$correlative) {
                    return [
                        'estado' => 'FALLO',
                        'mensaje' => 'No hay un correlativo activo configurado para este tipo de documento en la caja registradora',
                    ];
                }

                // Generar y asignar el siguiente número correlativo
                DB::beginTransaction();
                $nextNumber = $correlative->current_number + 1;
                $correlative->incrementCorrelative();
                $venta->document_internal_number = $nextNumber;
                $venta->save();
                DB::commit();

                // Actualizar el invoiceId en el DTE con el nuevo número
                $dte['invoiceId'] = intval($nextNumber);

            } catch (\Exception $e) {
                DB::rollBack();
                return [
                    'estado' => 'FALLO',
                    'mensaje' => 'Error al asignar correlativo: ' . $e->getMessage(),
                ];
            }
        } else {
            // Si ya tiene correlativo asignado (reintento), usar el existente
            $dte['invoiceId'] = intval($venta->document_internal_number);
        }

        // Preparar datos del documento para el historial
        $documentInfo = [
            'document_type' => $venta->documentType->code ?? null,
            'document_number' => $venta->document_internal_number ?? null,
        ];

        $responseData = $this->SendDTE($dte, $idVenta, $documentInfo);

//    dd($responseData['respuestaHacienda']);
//    if (isset($responseData['respuestaHacienda']['estado']) && $responseData['respuestaHacienda']["estado"] === "RECHAZADO" || $responseData["estado"] === "RECHAZADO") {
        if (
            (isset($responseData['respuestaHacienda']['estado']) && $responseData['respuestaHacienda']['estado'] === "RECHAZADO")
            || (isset($responseData["estado"]) && $responseData["estado"] === "RECHAZADO")
        ) {
            // Construir mensaje detallado del error de Hacienda
            $errorDetails = [];

            // Obtener información de la respuesta
            $haciendaResponse = $responseData['respuestaHacienda'] ?? $responseData;

            if (isset($haciendaResponse['descripcionMsg'])) {
                if (is_array($haciendaResponse['descripcionMsg'])) {
                    $errorDetails[] = implode(', ', $haciendaResponse['descripcionMsg']);
                } else {
                    $errorDetails[] = $haciendaResponse['descripcionMsg'];
                }
            }

            if (isset($haciendaResponse['observaciones'])) {
                if (is_array($haciendaResponse['observaciones'])) {
                    $errorDetails[] = implode(', ', $haciendaResponse['observaciones']);
                } else {
                    $errorDetails[] = $haciendaResponse['observaciones'];
                }
            }

            $mensaje = !empty($errorDetails)
                ? 'DTE rechazado por Hacienda: ' . implode(' | ', $errorDetails)
                : 'DTE rechazado por Hacienda';

            return [
                'estado' => 'FALLO',
                'response' => $responseData,
                'mensaje' => $mensaje,
            ];
        } else if (isset($responseData['respuestaHacienda']["estado"]) && $responseData['respuestaHacienda']["estado"] == "PENDIENTE") {
            $this->saveJson($responseData, $idVenta, false);
            return [
                'estado' => 'CONTINGENCIA',
                'mensaje' => 'DTE procesado correctamente - Pendiente envio a hacienda',
            ];
        } else if (isset($responseData['respuestaHacienda']["estado"]) && $responseData['respuestaHacienda']["estado"] == "PROCESADO") {
            $this->saveJson($responseData, $idVenta, true);
            return [
                'estado' => 'EXITO',
                'mensaje' => 'DTE enviado correctamente',
                'codGeneracion'=>$responseData['respuestaHacienda']['codigoGeneracion']??'SN',
            ];
        } else {
//            $this->saveJson($responseData, $idVenta, false);
            return [
                'estado' => 'FALLO',
                'mensaje' => 'Error desconocido' // Concatenar observaciones
            ];
        }
    }
    public function saveRestoreJson($responseData, $codGeneracion): void
    {
        // Ya no guardamos el JSON en disco
        // Intentar guardar en history_dtes si no existe
        $exists = HistoryDte::where('codigoGeneracion', $codGeneracion)->exists();

        if (!$exists) {
            // Crear registro en history_dtes para DTEs recuperados de Hacienda
            $historyDte = new HistoryDte();
            $historyDte->codigoGeneracion = $codGeneracion;
            $historyDte->dte = $responseData;
            $historyDte->estado = 'RECUPERADO_HACIENDA';
            $historyDte->save();
        }
    }
    public  function logDTE($idVenta)
    {
        try {
            $historyDTE= HistoryDte::where('sales_invoice_id', $idVenta)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            return ApiResponse::success($historyDTE, 'Historial de DTE obtenido correctamente',200);
        }catch (Exception $e) {
            ApiResponse::error(null,
                'Error al obtener el historial de DTE: ' . $e->getMessage(),
                500);
        }



    }

    /**
     * Asignar número de control interno si está en 0 (validación)
     * Obtiene el próximo correlativo del sistema e incrementa el contador
     */
    private function assignControlNumberIfNeeded(SalesHeader $factura): ?JsonResponse
    {
        // Si document_internal_number NO es 0, ya tiene un número asignado, retornar null (sin error)
        if ($factura->document_internal_number != 0) {
            return null;
        }

        // Obtener la caja registradora activa del warehouse
        $cashRegister = $factura->warehouse->cashRegisters()->where('is_active', 1)->first();

        if (!$cashRegister) {
            return response()->json([
                'status' => false,
                'message' => 'No hay una caja registradora activa en esta sucursal',
            ], 400);
        }

        // Obtener el correlativo activo para el tipo de documento
        $correlativo = Correlative::where('cash_register_id', $cashRegister->id)
            ->where('document_type_id', $factura->document_type_id)
            ->where('is_active', true)
            ->first();

        if (!$correlativo) {
            return response()->json([
                'status' => false,
                'message' => 'No hay un correlativo activo configurado para este tipo de documento',
            ], 400);
        }

        // Asignar el próximo número correlativo
        $factura->document_internal_number = $correlativo->current_number + 1;
        $factura->save();

        // Incrementar el correlativo
        $correlativo->incrementCorrelative();

        return null; // Sin error, proceso exitoso
    }

}
