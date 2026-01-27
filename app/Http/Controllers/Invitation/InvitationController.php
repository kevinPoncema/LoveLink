<?php

namespace App\Http\Controllers\Invitation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invitation\StoreInvitationRequest;
use App\Http\Requests\Invitation\UpdateInvitationRequest;
use App\Services\InvitationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(
        protected InvitationService $invitationService
    ) {}

    /**
     * Lista las invitations del usuario autenticado
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $invitations = $this->invitationService->getUserInvitations($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => $invitations,
                'message' => 'Invitations obtenidas exitosamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las invitations: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crea una nueva invitation
     */
    public function store(StoreInvitationRequest $request): JsonResponse
    {
        try {
            $invitation = $this->invitationService->createInvitation(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'data' => $invitation->load(['media']),
                'message' => 'Invitation creada exitosamente.',
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la invitation: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Muestra los detalles de una invitation específica
     * Puede ser accedida por ID o slug
     * Para el propietario: ID autenticado
     * Para público: slug sin autenticación
     */
    public function show(Request $request, $identifier): JsonResponse
    {
        try {
            if (is_numeric($identifier)) {
                if (! $request->user()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Acceso no autorizado.',
                    ], 401);
                }

                $invitation = $this->invitationService->findInvitationById(
                    (int) $identifier,
                    $request->user()
                );
            } else {
                $invitation = $this->invitationService->findPublicInvitationBySlug($identifier);
            }

            if (! $invitation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invitation no encontrada.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $invitation->load(['media', 'user:id,name']),
                'message' => 'Invitation obtenida exitosamente.',
            ]);
        } catch (\Exception $e) {
            if ($e->getCode() === 403) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 403);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la invitation: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza una invitation existente
     */
    public function update(UpdateInvitationRequest $request, $id): JsonResponse
    {
        try {
            $invitation = $this->invitationService->updateInvitation(
                (int) $id,
                $request->validated(),
                $request->user()
            );

            return response()->json([
                'success' => true,
                'data' => $invitation->load(['media']),
                'message' => 'Invitation actualizada exitosamente.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invitation no encontrada.',
            ], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            if ($e->getCode() === 403) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 403);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la invitation: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Elimina una invitation (soft delete)
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $deleted = $this->invitationService->deleteInvitation(
                (int) $id,
                $request->user()
            );

            if (! $deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo eliminar la invitation.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Invitation eliminada exitosamente.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invitation no encontrada.',
            ], 404);
        } catch (\Exception $e) {
            if ($e->getCode() === 403) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 403);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la invitation: '.$e->getMessage(),
            ], 500);
        }
    }
}
