<?php

namespace Modules\Authors\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Authors\Models\Author;

class AuthorService
{
    /**
     * Create a new author
     */
    public function create(array $data, ?UploadedFile $image = null): Author
    {
        return DB::transaction(function () use ($data, $image) {
            // Handle image upload
            if ($image) {
                $imageName = time().'_'.$image->getClientOriginalName();
                $image->storeAs('authors', $imageName, 'public');
                $data['image'] = 'authors/'.$imageName;
            }

            $author = Author::create($data);

            Log::info('Author created', [
                'author_id' => $author->author_id,
                'title' => $author->title,
            ]);

            return $author;
        });
    }

    /**
     * Update an existing author
     */
    public function update(Author $author, array $data, ?UploadedFile $image = null): Author
    {
        try {
            return DB::transaction(function () use ($author, $data, $image) {
                // Handle image upload
                if ($image) {
                    // Delete old image if exists
                    if ($author->image && Storage::disk('public')->exists($author->image)) {
                        Storage::disk('public')->delete($author->image);
                    }

                    $imageName = time().'_'.$image->getClientOriginalName();
                    $image->storeAs('authors', $imageName, 'public');
                    $data['image'] = 'authors/'.$imageName;
                }

                $author->update($data);

                Log::info('Author updated', [
                    'author_id' => $author->author_id,
                    'title' => $author->title,
                ]);

                return $author;
            });
        } catch (\Exception $e) {
            Log::error('AuthorService update error:', [
                'author_id' => $author->author_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete an author
     */
    public function delete(Author $author): void
    {
        try {
            DB::transaction(function () use ($author) {
                // Delete image if exists
                if ($author->image && Storage::disk('public')->exists($author->image)) {
                    Storage::disk('public')->delete($author->image);
                }

                $author->delete();

                Log::info('Author deleted', [
                    'author_id' => $author->author_id,
                    'title' => $author->title,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('AuthorService delete error:', [
                'author_id' => $author->author_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Toggle author mainpage visibility
     */
    public function toggleMainpage(Author $author): Author
    {
        try {
            return DB::transaction(function () use ($author) {
                $author->update(['show_on_mainpage' => !$author->show_on_mainpage]);

                Log::info('Author mainpage toggled', [
                    'author_id' => $author->author_id,
                    'show_on_mainpage' => $author->show_on_mainpage,
                ]);

                return $author;
            });
        } catch (\Exception $e) {
            Log::error('AuthorService toggleMainpage error:', [
                'author_id' => $author->author_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Toggle author status
     */
    public function toggleStatus(Author $author): Author
    {
        try {
            return DB::transaction(function () use ($author) {
                $author->update(['status' => !$author->status]);

                Log::info('Author status toggled', [
                    'author_id' => $author->author_id,
                    'status' => $author->status,
                ]);

                return $author;
            });
        } catch (\Exception $e) {
            Log::error('AuthorService toggleStatus error:', [
                'author_id' => $author->author_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

