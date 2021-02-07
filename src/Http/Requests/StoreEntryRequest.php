<?php

namespace Kalizi\LaravelSpyhole\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->wantsJson();
    }

    public function rules(): array
    {
        return [
            // recording frames
            'frames' => 'required|array',
            // previous recording id (encrypted)
            'recording' => 'sometimes|string',
            // recorded path
            'path' => 'required|string',
        ];
    }
}
