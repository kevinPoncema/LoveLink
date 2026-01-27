<?php

namespace App\Http\Controllers\Themes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Themes\StoreThemeRequest;
use App\Http\Requests\Themes\UpdateThemeRequest;
use App\Services\ThemeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function __construct(
        protected ThemeService $themeService
    ) {}

    /**
     * Lista temas disponibles para el usuario (sistema + propios)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $themes = $this->themeService->getAvailableThemes($request->user());

        return response()->json([
            'message' => 'Temas obtenidos exitosamente.',
            'themes' => $themes,
        ], 200);
    }

    /**
     * Crea un nuevo tema personalizado para el usuario
     * 
     * @param StoreThemeRequest $request
     * @return JsonResponse
     */
    public function store(StoreThemeRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Extraer archivo de imagen si existe
            $backgroundImage = $request->hasFile('bg_image_file') ? $request->file('bg_image_file') : null;
            
            // Remover el archivo de los datos ya que se maneja por separado
            unset($data['bg_image_file']);

            $theme = $this->themeService->createUserTheme($request->user(), $data, $backgroundImage);

            return response()->json([
                'success' => true,
                'message' => 'Tema creado exitosamente.',
                'data' => $theme->load('backgroundImage'),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el tema: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra detalles de un tema específico si es accesible
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $theme = $this->themeService->findAccessibleTheme($id, $request->user());

        if (!$theme) {
            return response()->json([
                'message' => 'Tema no encontrado o no accesible.',
            ], 404);
        }

        return response()->json([
            'theme' => $theme,
        ], 200);
    }

    /**
     * Actualiza un tema del usuario
     * 
     * @param UpdateThemeRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateThemeRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Extraer archivo de imagen si existe
            $backgroundImage = $request->hasFile('bg_image_file') ? $request->file('bg_image_file') : null;
            
            // Remover el archivo de los datos ya que se maneja por separado
            unset($data['bg_image_file']);

            $theme = $this->themeService->updateTheme($id, $data, $request->user(), $backgroundImage);

            return response()->json([
                'success' => true,
                'message' => 'Tema actualizado exitosamente.',
                'data' => $theme->load('backgroundImage'),
            ], 200);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            
            if ($statusCode === 404) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tema no encontrado.',
                ], 404);
            }

            if ($statusCode === 403) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para modificar este tema.',
                ], 403);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el tema: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un tema del usuario
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $success = $this->themeService->deleteTheme($id, $request->user());

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tema eliminado exitosamente.',
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el tema.',
            ], 500);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            
            if ($statusCode === 404) {
                return response()->json([
                    'message' => 'Tema no encontrado.',
                ], 404);
            }

            if ($statusCode === 403) {
                return response()->json([
                    'message' => 'No tienes permisos para eliminar este tema.',
                ], 403);
            }

            return response()->json([
                'message' => 'Error interno del servidor.',
            ], 500);
        }
    }
}