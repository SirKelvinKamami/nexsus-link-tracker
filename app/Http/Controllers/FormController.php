<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormResponse;

class FormController extends Controller
{
    /**
     * List all forms for the authenticated user.
     */
    public function index()
    {
        $userId = Auth::id();
        $forms = Form::where('user_id', $userId)
            ->withCount('responses')
            ->orderByDesc('updated_at')
            ->get();

        return view('studio.forms.index', compact('forms'));
    }

    /**
     * Show form builder for creating a new form.
     */
    public function create()
    {
        return view('studio.forms.create');
    }

    /**
     * Store a new form.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'collect_email' => 'nullable|boolean',
            'one_response_per_ip' => 'nullable|boolean',
        ]);

        $userId = Auth::id();
        $slug = Str::slug($request->title);
        
        // Ensure unique slug
        $originalSlug = $slug;
        $count = 1;
        while (Form::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $form = Form::create([
            'user_id' => $userId,
            'title' => $request->title,
            'slug' => $slug,
            'description' => $request->description ?? '',
            'collect_email' => $request->boolean('collect_email', false),
            'one_response_per_ip' => $request->boolean('one_response_per_ip', false),
            'is_active' => true,
        ]);

        return redirect()->route('forms.edit', $form->id)
            ->with('success', 'Form created. Now add your fields.');
    }

    /**
     * Show form builder for editing an existing form.
     */
    public function edit($id)
    {
        $userId = Auth::id();
        $form = Form::where('id', $id)
            ->where('user_id', $userId)
            ->with('fields')
            ->firstOrFail();

        return view('studio.forms.edit', compact('form'));
    }

    /**
     * Update form settings.
     */
    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $form = Form::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'collect_email' => 'nullable|boolean',
            'one_response_per_ip' => 'nullable|boolean',
        ]);

        $form->update([
            'title' => $request->title,
            'description' => $request->description ?? '',
            'is_active' => $request->boolean('is_active', true),
            'collect_email' => $request->boolean('collect_email', false),
            'one_response_per_ip' => $request->boolean('one_response_per_ip', false),
        ]);

        return redirect()->route('forms.edit', $form->id)
            ->with('success', 'Form updated.');
    }

    /**
     * Delete a form.
     */
    public function destroy($id)
    {
        $userId = Auth::id();
        $form = Form::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $form->delete();

        return redirect()->route('forms.index')
            ->with('success', 'Form deleted.');
    }

    /**
     * Add a field to a form.
     */
    public function addField(Request $request, $formId)
    {
        $userId = Auth::id();
        $form = Form::where('id', $formId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', FormField::getValidTypes()),
            'options' => 'nullable|array',
            'is_required' => 'nullable|boolean',
            'placeholder' => 'nullable|string|max:255',
            'default_value' => 'nullable|string|max:255',
        ]);

        $maxOrder = $form->fields()->max('order') ?? 0;

        $field = FormField::create([
            'form_id' => $form->id,
            'label' => $request->label,
            'type' => $request->type,
            'options' => $request->options,
            'is_required' => $request->boolean('is_required', false),
            'placeholder' => $request->placeholder,
            'default_value' => $request->default_value,
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'field' => $field,
        ]);
    }

    /**
     * Update a field.
     */
    public function updateField(Request $request, $formId, $fieldId)
    {
        $userId = Auth::id();
        $form = Form::where('id', $formId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $field = FormField::where('id', $fieldId)
            ->where('form_id', $form->id)
            ->firstOrFail();

        $field->update([
            'label' => $request->label ?? $field->label,
            'type' => $request->type ?? $field->type,
            'options' => $request->options ?? $field->options,
            'is_required' => $request->boolean('is_required', $field->is_required),
            'placeholder' => $request->placeholder ?? $field->placeholder,
            'default_value' => $request->default_value ?? $field->default_value,
            'order' => $request->order ?? $field->order,
        ]);

        return response()->json([
            'success' => true,
            'field' => $field,
        ]);
    }

    /**
     * Delete a field.
     */
    public function deleteField($formId, $fieldId)
    {
        $userId = Auth::id();
        $form = Form::where('id', $formId)
            ->where('user_id', $userId)
            ->firstOrFail();

        FormField::where('id', $fieldId)
            ->where('form_id', $form->id)
            ->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Reorder fields.
     */
    public function reorderFields(Request $request, $formId)
    {
        $userId = Auth::id();
        $form = Form::where('id', $formId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $order = $request->input('order', []);
        
        foreach ($order as $index => $fieldId) {
            FormField::where('id', $fieldId)
                ->where('form_id', $form->id)
                ->update(['order' => $index]);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * View responses for a form.
     */
    public function responses($id)
    {
        $userId = Auth::id();
        $form = Form::where('id', $id)
            ->where('user_id', $userId)
            ->with('fields')
            ->firstOrFail();

        $responses = $form->responses()
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('studio.forms.responses', compact('form', 'responses'));
    }

    /**
     * Export responses as CSV.
     */
    public function exportCsv($id)
    {
        $userId = Auth::id();
        $form = Form::where('id', $id)
            ->where('user_id', $userId)
            ->with('fields')
            ->firstOrFail();

        $responses = $form->responses()
            ->orderByDesc('created_at')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"form-{$form->slug}-responses.csv\"",
        ];

        $callback = function () use ($form, $responses) {
            $file = fopen('php://output', 'w');
            
            // Header row
            $header = ['Response ID', 'Submitted At', 'IP Hash'];
            if ($form->collect_email) {
                $header[] = 'Email';
            }
            foreach ($form->fields as $field) {
                $header[] = $field->label;
            }
            fputcsv($file, $header);

            // Data rows
            foreach ($responses as $response) {
                $row = [
                    $response->id,
                    $response->created_at->format('Y-m-d H:i:s'),
                    $response->ip_hash,
                ];
                if ($form->collect_email) {
                    $row[] = $response->email;
                }
                foreach ($form->fields as $field) {
                    $row[] = $response->answers[$field->label] ?? '';
                }
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
