<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CatalogTaxonomyController extends Controller
{
    public function index(string $taxonomy): View
    {
        $model = $this->model($taxonomy);
        $records = $model::query()->withCount('products')->orderBy('name')->paginate(25);
        $title = $taxonomy === 'categories' ? 'Categories' : 'Brands';

        return view('admin.taxonomy.index', compact('taxonomy', 'records', 'title'));
    }

    public function create(string $taxonomy): View
    {
        $model = $this->model($taxonomy);
        $record = new $model(['is_active' => true]);
        $categories = $taxonomy === 'categories' ? Category::query()->orderBy('name')->get() : collect();

        return view('admin.taxonomy.form', compact('taxonomy', 'record', 'categories'));
    }

    public function store(Request $request, string $taxonomy): RedirectResponse
    {
        $model = $this->model($taxonomy);
        $values = $this->validatedValues($request, $taxonomy);
        $record = new $model;
        $this->applyValues($record, $values, $request, $taxonomy);
        $record->save();

        return redirect()->route('admin.taxonomy.index', ['taxonomy' => $taxonomy])->with('status', ($taxonomy === 'categories' ? 'Category' : 'Brand').' created.');
    }

    public function edit(string $taxonomy, int $record): View
    {
        $model = $this->model($taxonomy);
        $record = $model::query()->findOrFail($record);
        $categories = $taxonomy === 'categories' ? Category::query()->whereKeyNot($record->getKey())->orderBy('name')->get() : collect();

        return view('admin.taxonomy.form', compact('taxonomy', 'record', 'categories'));
    }

    public function update(Request $request, string $taxonomy, int $record): RedirectResponse
    {
        $model = $this->model($taxonomy);
        $record = $model::query()->findOrFail($record);
        $values = $this->validatedValues($request, $taxonomy, $record);
        $this->applyValues($record, $values, $request, $taxonomy);
        $record->save();

        return redirect()->route('admin.taxonomy.index', ['taxonomy' => $taxonomy])->with('status', ($taxonomy === 'categories' ? 'Category' : 'Brand').' updated.');
    }

    public function visibility(string $taxonomy, int $record): RedirectResponse
    {
        $model = $this->model($taxonomy);
        $record = $model::query()->findOrFail($record);
        $record->update(['is_active' => ! $record->is_active]);

        return back()->with('status', $record->is_active ? 'Published.' : 'Hidden from the shop.');
    }

    /** @return class-string<Category|Brand> */
    private function model(string $taxonomy): string
    {
        return match ($taxonomy) {
            'categories' => Category::class,
            'brands' => Brand::class,
            default => abort(404),
        };
    }

    /** @return array<string, mixed> */
    private function validatedValues(Request $request, string $taxonomy, ?Model $record = null): array
    {
        $table = $this->model($taxonomy)::query()->getModel()->getTable();
        $imageField = $taxonomy === 'brands' ? 'logo' : 'image';
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique($table, 'slug')->ignore($record?->getKey())],
            'is_active' => ['sometimes', 'boolean'],
            $imageField => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
        ];

        if ($taxonomy === 'categories') {
            $rules['description'] = ['nullable', 'string', 'max:5000'];
            $rules['sort_order'] = ['nullable', 'integer', 'min:0', 'max:65535'];
            $rules['parent_id'] = ['nullable', 'integer', 'exists:categories,id'];
            if ($record) {
                $rules['parent_id'][] = Rule::notIn([$record->getKey()]);
            }
        }

        $values = $request->validate($rules);
        if ($taxonomy === 'categories' && $record && ! empty($values['parent_id'])) {
            $parentId = (int) $values['parent_id'];
            while ($parentId > 0) {
                if ($parentId === (int) $record->getKey()) {
                    throw ValidationException::withMessages(['parent_id' => 'A category cannot be placed inside one of its descendants.']);
                }
                $parentId = (int) Category::query()->whereKey($parentId)->value('parent_id');
            }
        }

        return $values;
    }

    /** @param array<string, mixed> $values */
    private function applyValues(Model $record, array $values, Request $request, string $taxonomy): void
    {
        $record->name = $values['name'];
        $record->slug = Str::slug(($values['slug'] ?? null) ?: $values['name']);
        $record->is_active = $request->boolean('is_active');
        $model = $record::class;
        $slugExists = $model::query()
            ->where('slug', $record->slug)
            ->when($record->exists, fn ($query) => $query->whereKeyNot($record->getKey()))
            ->exists();
        if ($slugExists) {
            throw ValidationException::withMessages(['slug' => 'This name or URL slug is already in use.']);
        }

        if ($taxonomy === 'categories') {
            $record->description = $values['description'] ?? null;
            $record->sort_order = (int) ($values['sort_order'] ?? 0);
            $record->parent_id = $values['parent_id'] ?? null;
        }

        $imageField = $taxonomy === 'brands' ? 'logo' : 'image';
        if ($request->hasFile($imageField)) {
            $path = $request->file($imageField)->storePublicly($taxonomy, 'public');
            if (! $path) {
                throw ValidationException::withMessages([$imageField => 'The image could not be saved. Please retry.']);
            }

            $record->{$taxonomy === 'brands' ? 'logo_path' : 'image_path'} = 'storage/'.$path;
        }
    }
}
