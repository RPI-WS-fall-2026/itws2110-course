<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** POST /api/items. A Form Request runs before the controller: a 422 never reaches it. */
class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Item::class);
    }

    public function rules(): array
    {
        $rules = ItemRules::fields();
        // No two items with the same name for one owner (MySQL compares names ignoring case).
        $rules['name'][] = Rule::unique('items')->where('user_id', $this->user()->id);
        return $rules;
    }

    public function messages(): array
    {
        return ItemRules::messages();
    }
}
