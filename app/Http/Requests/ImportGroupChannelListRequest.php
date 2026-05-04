<?php

namespace App\Http\Requests;

use App\Models\ChannelGroup;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class ImportGroupChannelListRequest extends FormRequest
{
    public function authorize(): bool
    {
        $group = $this->route('group');

        if (! $group instanceof ChannelGroup) {
            return false;
        }

        return $this->user()?->can('update', $group) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value instanceof UploadedFile) {
                        return;
                    }
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (! in_array($ext, ['json', 'txt'], true)) {
                        $fail(__('The file must be a .json or .txt file containing JSON.'));
                    }
                },
            ],
        ];
    }
}
