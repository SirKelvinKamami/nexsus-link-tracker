<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormResponse;

class FormController extends BaseController
{
    /**
     * List all forms for the authenticated user.
     */
    public function index(Request $request)
    {
        $userId = $this->getUserId();
        $perPage = $request->input('per_page', 50);

        $query = Form::where('user_id', $userId);

        $forms = $query->withCount('responses')
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        $forms->getCollection()->transform(function ($form) {
            return [
                'id' => $form->id,
                'title' => $form->title,
                'slug' => $form->slug,
                'description' => $form->description,
                'is_active' => $form->is_active,
                'collect_email' => $form->collect_email,
                'response_count' => $form->responses_count,
                'public_url' => url("/f/{$form->slug}"),
                'created_at' => $form->created_at->toISOString(),
                'updated_at' => $form->updated_at->toISOString(),
            ];
        });

        return $this->success($forms);
    }

    /**
     * Get a single form with fields.
     */
    public function show($id)
    {
        $userId = $this->getUserId();

        $form = Form::where('id', $id)
            ->where('user_id', $userId)
            ->with('fields')
            ->withCount('responses')
            ->first();

        if (!$form) {
            return $this->error('Form not found', 404);
        }

        $data = [
            'id' => $form->id,
            'title' => $form->title,
            'slug' => $form->slug,
            'description' => $form->description,
            'is_active' => $form->is_active,
            'collect_email' => $form->collect_email,
            'one_response_per_ip' => $form->one_response_per_ip,
            'settings' => $form->settings,
            'response_count' => $form->responses_count,
            'public_url' => url("/f/{$form->slug}"),
            'fields' => $form->fields->map(function ($field) {
                return [
                    'id' => $field->id,
                    'label' => $field->label,
                    'type' => $field->type,
                    'options' => $field->options,
                    'is_required' => $field->is_required,
                    'placeholder' => $field->placeholder,
                    'default_value' => $field->default_value,
                    'order' => $field->order,
                ];
            }),
            'created_at' => $form->created_at->toISOString(),
            'updated_at' => $form->updated_at->toISOString(),
        ];

        return $this->success($data);
    }

    /**
     * Get responses for a form.
     */
    public function responses(Request $request, $id)
    {
        $userId = $this->getUserId();

        $form = Form::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$form) {
            return $this->error('Form not found', 404);
        }

        $perPage = $request->input('per_page', 50);

        $responses = $form->responses()
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $responses->getCollection()->transform(function ($response) use ($form) {
            return [
                'id' => $response->id,
                'email' => $response->email,
                'answers' => $response->answers,
                'ip_hash' => $response->ip_hash,
                'referrer' => $response->referrer,
                'submitted_at' => $response->created_at->toISOString(),
            ];
        });

        return $this->success($responses);
    }

    /**
     * Get form statistics.
     */
    public function stats($id)
    {
        $userId = $this->getUserId();

        $form = Form::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$form) {
            return $this->error('Form not found', 404);
        }

        $responses = $form->responses();

        $totalResponses = (clone $responses)->count();
        $todayResponses = (clone $responses)->whereDate('created_at', now()->toDateString())->count();
        $weekResponses = (clone $responses)->where('created_at', '>=', now()->subDays(7))->count();
        $monthResponses = (clone $responses)->where('created_at', '>=', now()->subDays(30))->count();

        $start = now()->subDays(29)->startOfDay();
        $dailyRows = (clone $responses)->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $daily = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $daily[$d] = (int) ($dailyRows[$d] ?? 0);
        }

        $data = [
            'form_id' => $form->id,
            'title' => $form->title,
            'total_responses' => $totalResponses,
            'today_responses' => $todayResponses,
            'week_responses' => $weekResponses,
            'month_responses' => $monthResponses,
            'daily' => $daily,
        ];

        return $this->success($data);
    }
}
