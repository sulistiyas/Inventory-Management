<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StockOutRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:1', 'max:999999'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists'   => 'Produk tidak ditemukan.',
            'quantity.required'   => 'Jumlah wajib diisi.',
            'quantity.min'        => 'Jumlah minimal 1.',
        ];
    }

    /**
     * After-validation hook: check actual stock availability.
     * This runs after all rules pass, giving us a clean product_id to work with.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('product_id') ||
                    $validator->errors()->has('quantity')) {
                    return; // don't bother if basic rules already failed
                }

                $product = Product::find($this->product_id);
                if ($product && $product->stock < (int) $this->quantity) {
                    $validator->errors()->add(
                        'quantity',
                        "Stok tidak cukup. Tersedia: {$product->stock} unit."
                    );
                }
            },
        ];
    }
}