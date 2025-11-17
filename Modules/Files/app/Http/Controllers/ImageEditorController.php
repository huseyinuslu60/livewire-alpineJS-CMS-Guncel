<?php

namespace Modules\Files\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Files\Services\ImageEditorService;

class ImageEditorController
{
    public function __construct(
        protected ImageEditorService $imageEditorService
    ) {
    }

    /**
     * Save edited image from image editor
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function editImage(Request $request): JsonResponse
    {
        Gate::authorize('view files');

        try {
            // Increase timeout and memory limit for large image processing
            set_time_limit(120); // 2 minutes
            ini_set('memory_limit', '256M');

            // Validate request
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
                'file_id' => 'nullable|string',
                'index' => 'nullable|integer',
            ], [
                'image.required' => 'Resim dosyası gereklidir',
                'image.image' => 'Yüklenen dosya geçerli bir resim dosyası olmalıdır',
                'image.mimes' => 'Resim dosyası şu formatlardan biri olmalıdır: jpeg, png, jpg, gif, webp',
                'image.max' => 'Resim dosyası maksimum 10MB olabilir',
            ]);

            $image = $request->file('image');
            $fileId = $request->input('file_id');
            $index = $request->input('index') ? (int) $request->input('index') : null;

            if (! $image || ! $image->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geçersiz dosya yüklendi',
                ], 400);
            }

            $result = $this->imageEditorService->saveEditedImage(
                $image,
                $fileId,
                $index
            );

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Resim başarıyla kaydedildi',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \App\Helpers\LogHelper::warning('Image edit validation error', [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Doğrulama hatası',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            \App\Helpers\LogHelper::warning('Image edit error - File too large', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Dosya boyutu çok büyük (maksimum 10MB)',
            ], 413);
        } catch (\Exception $e) {
            \App\Helpers\LogHelper::error('Image edit error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'file_id' => $request->input('file_id'),
                'index' => $request->input('index'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Resim kaydedilirken bir hata oluştu: '.$e->getMessage(),
            ], 500);
        }
    }
}

