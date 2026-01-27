<?php

namespace App\Http\Controllers\Invitation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invitation\AttachInvitationMediaRequest;
use App\Services\InvitationService;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationMediaController extends Controller
{
    public function __construct(
        protected InvitationService $invitationService,
        protected MediaService $mediaService
    ) {}

    /**
     * Vincula media a una invitation
     */
    public function store(AttachInvitationMediaRequest $request, int $invitationId): JsonResponse
    {
        try {
            $this->invitationService->attachMediaToInvitation(
                $invitationId,
                $request->media_id,
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Media vinculado a la invitation exitosamente.',
            ], 201);
        } catch (\Exception $e) {
            if ($e->getCode() === 403) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 403);
            }

            if ($e->getCode() === 422) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al vincular media: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Desvincula media de una invitation
     */
    public function destroy(Request $request, int $invitationId, int $mediaId): JsonResponse
    {
        try {
            $this->invitationService->detachMediaFromInvitation(
                $invitationId,
                $mediaId,
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Media desvinculado de la invitation exitosamente.',
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
                'message' => 'Error al desvincular media: '.$e->getMessage(),
            ], 500);
        }
    }
}
