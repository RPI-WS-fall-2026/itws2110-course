<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * PUT /api/items/{item}. authorize() runs before rules(), so a resident editing someone
 * else's item gets 403 even if the input is also wrong -- the order the design promised.
 */
class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('item'));
    }

    public function rules(): array
    {
        $item = $this->route('item');
        // `sometimes`: a field that is left out keeps its value; a field that is sent is checked.
        $rules = array_map(fn (array $r) => ['sometimes', ...$r], ItemRules::fields());
        $rules['name'][] = Rule::unique('items')->where('user_id', $item->user_id)->ignore($item->id);
        return $rules;
    }

    public function messages(): array
    {
        return ItemRules::messages();
    }
}
