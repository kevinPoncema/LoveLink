<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    /**
     * Lista media accesible por el usuario autenticado
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $media = $this->mediaService->getUserAccessibleMedia($request->user()->id);
            
            return response()->json([
                'success' => true,
                'data' => $media,
                'message' => 'Media obtenido exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el media: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sube un nuevo archivo multimedia
     */
    public function store(StoreMediaRequest $request): JsonResponse
    {
        try {
            $media = $this->mediaService->uploadMedia(
                $request->file('file'),
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'data' => $media,
                'message' => 'Archivo subido exitosamente.'
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un archivo multimedia si no está en uso
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $deleted = $this->mediaService->deleteMedia($id, $request->user()->id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el archivo. Puede que no exista, no te pertenezca, o esté siendo usado.'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Archivo eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }
}