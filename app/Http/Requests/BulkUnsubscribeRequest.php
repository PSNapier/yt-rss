<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BulkUnsubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userGroupIds = $this->user()->channelGroups()->pluck('id')->all();

        $subscribedChannelIds = DB::table('channel_group_channel')
            ->whereIn('channel_group_id', $userGroupIds)
            ->distinct()
            ->pluck('channel_id')
            ->all();

        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.channel_id' => ['required', 'integer', 'distinct', Rule::in($subscribedChannelIds)],
            'items.*.scope' => ['required', Rule::in(['everywhere', 'group'])],
            'items.*.group_id' => ['required_if:items.*.scope,group', 'nullable', 'integer', Rule::in($userGroupIds)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.*.channel_id.in' => 'You are not subscribed to this channel.',
            'items.*.group_id.in' => 'This group does not belong to you.',
        ];
    }
}
