<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Overtrue\LaravelPinyin\Facades\Pinyin;

use App\Models\Article;
use App\Http\Traits\ApiResponse;

class ArticleController extends Controller
{
    use ApiResponse;
    use SoftDeletes;

    public function index()
    {
        try {
            $articles = Article::ordered()->get();
            return $this->successResponse('Success', $articles);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function trashed()
    {
        try {
            $articles = Article::onlyTrashed()->get();
            return $this->successResponse('Success', $articles);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            if (!$request->isJson()) {
                return $this->errorResponse('Content-Type must be application/json or multipart/form-data', 415);
            }

            $validated = $request->validate([
                'title' => 'required|max:255',
                'content' => 'required',
                'is_active' => 'boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($request->hasFile('image')) {
                if (!$request->file('image')->isValid()) {
                    return $this->errorResponse('Invalid image file', 422);
                }
                $imagePath = $request->file('image')->store('articles', 'public');
                $validated['image'] = $imagePath;
            }

            $baseSlug = Pinyin::permalink($validated['title']);
            $slug = $baseSlug;
            $count = 1;
            
            while (Article::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $count;
                $count++;
            }
            $validated['slug'] = $slug;

            $validated['order'] = Article::withTrashed()->max('order') + 1;

            $article = Article::create($validated);

            return $this->successResponse('Article created successfully', $article , 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create article', 500, $e->getMessage());
        }
    }

    public function show($id)
    {
        $article = Article::where('id', $id)
                    ->orWhere('slug', $id)
                    ->firstOrFail();
        return $article;
    }

    public function update(Request $request, Article $article)
    {
        try {
            if (!$request->isJson()) {
                return $this->errorResponse('Content-Type must be application/json or multipart/form-data', 415);
            }

            $validated = $request->validate([
                'title' => 'sometimes|required|max:255',
                'content' => 'sometimes|required',
                'is_active' => 'boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($request->hasFile('image')) {
                if (!$request->file('image')->isValid()) {
                    return $this->errorResponse('Invalid image file', 422);
                }

                if ($article->image) {
                    Storage::disk('public')->delete($article->image);
                }
                
                $imagePath = $request->file('image')->store('articles', 'public');
                $validated['image'] = $imagePath;
            }

            if (isset($validated['title'])) {
                $baseSlug = Pinyin::permalink($validated['title']);
                $slug = $baseSlug;
                $count = 1;

                while (Article::withTrashed()->where('slug', $slug)->where('id', '!=', $article->id)->exists()) {
                    $slug = $baseSlug . '-' . $count;
                    $count++;
                }

                $validated['slug'] = $slug;
            }

            $article->update($validated);

            return $this->successResponse('Article created successfully', $article);
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update article', 500, $e->getMessage());
        }
    }

    public function destroy(Article $article)
    {
        try {
            $article->delete();
            return $this->successResponse('Article deleted successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to deleted article', 500, $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $article = Article::onlyTrashed()->findOrFail($id);
            $article->restore();
            return $this->successResponse('Article updated successfully', $article);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update article', 500, $e->getMessage());
        }
    }

    public function forceDelete($id)
    {
        try {
            $article = Article::withTrashed()->findOrFail($id);
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $article->forceDelete();

            return $this->successResponse('Article force deleted successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to force delete article', 500, $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        try {
            $validated = $request->validate([
                'search' => 'nullable|string',
                'with_trashed' => 'in:true,false'
            ]);
    
            $query = Article::query();

            if ($search = $request->input('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            }

            $withTrashed = filter_var($validated['with_trashed'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($withTrashed) {
                $query->withTrashed();
            }

            return $this->successResponse('Success', $query->paginate(10));

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());

        } catch (\Exception $e) {
            return $this->errorResponse('Search failed', 500, $e->getMessage());
        }
    }

    public function updateOrder(Request $request)
    {
        try {
            $validated = $request->validate([
                'articles' => 'required|array',
                'articles.*.id' => 'required|exists:articles,id',
                'articles.*.order' => 'required|integer'
            ]);

            foreach ($validated['articles'] as $articleData) {
                Article::where('id', $articleData['id'])
                    ->update(['order' => $articleData['order']]);
            }

            return $this->successResponse('Orders updated successfully');
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());

        } catch (\Exception $e) {
            return $this->errorResponse('Search failed', 500, $e->getMessage());
        }
    }

    public function toggleActive(Article $article)
    {
        try {
            $article->update([
                'is_active' => !$article->is_active
            ]);

            return $this->successResponse('Article status toggled successfully', $article);
        
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to toggle article status', 500, $e->getMessage());
        }
    }
}