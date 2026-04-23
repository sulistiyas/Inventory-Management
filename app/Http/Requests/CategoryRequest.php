<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /**
         * Get the ID of the category being updated (null on create).
         *
         * WHY THIS FIX IS NEEDED:
         * Without ignoring the current ID, the unique rule sees the
         * existing row and throws a duplicate error — even though the
         * user kept the same name and only changed the description.
         *
         * Route model binding puts the Category in the route parameter.
         * We support two calling conventions:
         *   PUT /api/categories/{category}   → $this->route('category')
         *   PUT /api/categories/{id}         → $this->route('id')
         */
        $currentId = $this->route('category')?->id   // route model binding
                  ?? $this->route('category')         // raw integer segment
                  ?? $this->route('id')               // fallback key name
                  ?? null;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'min:2',
                // ✅ FIX: ignore the current record's row in the unique check
                Rule::unique('categories', 'name')->ignore($currentId),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:110',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                // Slug must also be unique, ignoring self on update
                Rule::unique('categories', 'slug')->ignore($currentId),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.min'      => 'Nama kategori minimal 2 karakter.',
            'name.max'      => 'Nama kategori maksimal 100 karakter.',
            'name.unique'   => 'Nama kategori sudah digunakan. Pilih nama lain.',
            'slug.unique'   => 'Slug sudah digunakan.',
            'slug.regex'    => 'Slug hanya boleh mengandung huruf kecil, angka, dan tanda hubung.',
        ];
    }

    /**
     * Prepare the data for validation.
     * Auto-trim whitespace and lowercase the slug if provided.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name ?? ''),
            'slug' => $this->slug
                ? strtolower(trim($this->slug))
                : null,
        ]);
    }
}