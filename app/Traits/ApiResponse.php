<?php


namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    protected function ok(
        mixed  $data = null,
        string $message = 'Success.',
    ): JsonResponse {
        return $this->success($data, $message, Response::HTTP_OK);
    }

    protected function created(
        mixed  $data = null,
        string $message = 'Resource berhasil dibuat.',
    ): JsonResponse {
        return $this->success($data, $message, Response::HTTP_CREATED);
    }

    protected function noContent(
        string $message = 'Operasi berhasil.',
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], Response::HTTP_OK);
    }

    protected function paginated(
        ResourceCollection $collection,
        string             $message = 'Success.',
    ): JsonResponse {
        $paginator = $collection->resource;

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $collection,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
        ], Response::HTTP_OK);
    }

    protected function badRequest(
        string $message = 'Permintaan tidak valid.',
        array  $errors = [],
    ): JsonResponse {
        return $this->error($message, Response::HTTP_BAD_REQUEST, $errors);
    }

    protected function unauthorized(
        string $message = 'Autentikasi diperlukan.',
    ): JsonResponse {
        return $this->error($message, Response::HTTP_UNAUTHORIZED);
    }

    protected function forbidden(
        string $message = 'Akses ditolak.',
    ): JsonResponse {
        return $this->error($message, Response::HTTP_FORBIDDEN);
    }

    protected function notFound(
        string $message = 'Resource tidak ditemukan.',
    ): JsonResponse {
        return $this->error($message, Response::HTTP_NOT_FOUND);
    }

    protected function conflict(
        string $message = 'Konflik data.',
    ): JsonResponse {
        return $this->error($message, Response::HTTP_CONFLICT);
    }

    protected function unprocessable(
        string $message = 'Data tidak dapat diproses.',
        array  $errors = [],
    ): JsonResponse {
        return $this->error($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    protected function serverError(
        string $message = 'Terjadi kesalahan pada server.',
    ): JsonResponse {
        return $this->error($message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function success(
        mixed  $data,
        string $message,
        int    $statusCode,
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if (! is_null($data)) {
            $payload['data'] = $data instanceof JsonResource
                ? $data->resolve()
                : $data;
        }

        return response()->json($payload, $statusCode);
    }

    private function error(
        string $message,
        int    $statusCode,
        array  $errors = [],
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $statusCode);
    }
}
