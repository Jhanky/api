<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QuotationService;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Http\Requests\UpdateQuotationStatusRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuotationController extends Controller
{
    use ApiResponseTrait;

    protected QuotationService $quotationService;

    public function __construct(QuotationService $quotationService)
    {
        $this->quotationService = $quotationService;
    }

    /**
     * Display a listing of quotations
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $quotations = $this->quotationService->getQuotations($request->all(), $request->get('per_page', 25));
            return $this->paginationResponse($quotations, 'Cotizaciones obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener cotizaciones', [], 500, 'INTERNAL_ERROR');
        }
    }

    /**
     * Store a newly created quotation
     */
    public function store(StoreQuotationRequest $request): JsonResponse
    {
        try {
            $quotation = $this->quotationService->createQuotation($request->validated());
            return $this->successResponse($quotation, 'Cotización creada exitosamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear cotización', [], 500, 'INTERNAL_ERROR');
        }
    }

    /**
     * Display the specified quotation
     */
    public function show(int $id): JsonResponse
    {
        try {
            $quotation = $this->quotationService->getQuotationById($id);
            return $this->successResponse($quotation, 'Cotización obtenida exitosamente');
        } catch (\Exception $e) {
            return $this->notFoundResponse('Cotización no encontrada');
        }
    }

    /**
     * Update the specified quotation
     */
    public function update(UpdateQuotationRequest $request, int $id): JsonResponse
    {
        try {
            $quotation = $this->quotationService->getQuotationById($id);
            $updatedQuotation = $this->quotationService->updateQuotation($quotation, $request->validated());
            return $this->successResponse($updatedQuotation, 'Cotización actualizada exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar cotización', [], 500, 'INTERNAL_ERROR');
        }
    }

    /**
     * Remove the specified quotation
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $quotation = $this->quotationService->getQuotationById($id);
            $this->quotationService->deleteQuotation($quotation);
            return $this->successResponse(null, 'Cotización eliminada exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar cotización', [], 500, 'INTERNAL_ERROR');
        }
    }

    /**
     * Update quotation status
     */
    public function updateStatus(UpdateQuotationStatusRequest $request, int $id): JsonResponse
    {
        try {
            $quotation = $this->quotationService->getQuotationById($id);
            $result = $this->quotationService->updateQuotationStatus($quotation, $request->validated()['status_id']);

            $message = 'Estado de cotización actualizado exitosamente';
            if (isset($result['project_created'])) {
                $message .= ', proyecto y centro de costo creados automáticamente';
            }

            return $this->successResponse($result, $message);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar estado de cotización', [], 500, 'INTERNAL_ERROR');
        }
    }

    /**
     * Get quotation statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->quotationService->getQuotationStatistics();
            return $this->successResponse($statistics, 'Estadísticas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas', [], 500, 'INTERNAL_ERROR');
        }
    }

    /**
     * Get quotation data for PDF generation
     */
    public function downloadPDF(int $id): JsonResponse
    {
        try {
            $pdfData = $this->quotationService->getQuotationPdfData($id);
            return $this->successResponse($pdfData, 'Datos de cotización obtenidos para PDF');
        } catch (\Exception $e) {
            return $this->notFoundResponse('Cotización no encontrada');
        }
    }
}
