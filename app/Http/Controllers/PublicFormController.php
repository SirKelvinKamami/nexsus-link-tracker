<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormResponse;
use App\Services\WebhookService;

class PublicFormController extends Controller
{
    /**
     * Display a form by slug.
     */
    public function show($slug)
    {
        $form = Form::where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->firstOrFail();

        if (!$form->isActive()) {
            return view('forms.closed', compact('form'));
        }

        return view('forms.show', compact('form'));
    }

    /**
     * Submit a form response.
     */
    public function submit(Request $request, $slug)
    {
        $form = Form::where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->firstOrFail();

        if (!$form->isActive()) {
            return redirect()->back()->with('error', 'This form is no longer accepting responses.');
        }

        // Check one response per IP
        if ($form->one_response_per_ip) {
            $ipHash = sha1($request->ip());
            $exists = $form->responses()
                ->where('ip_hash', $ipHash)
                ->exists();
            
            if ($exists) {
                return redirect()->back()
                    ->with('error', 'You have already submitted a response to this form.');
            }
        }

        // Validate based on field definitions
        $rules = [];
        $messages = [];
        
        foreach ($form->fields as $field) {
            $fieldRules = [];
            $fieldName = 'field_' . $field->id;
            
            if ($field->is_required) {
                $fieldRules[] = 'required';
                $messages[$fieldName . '.required'] = "{$field->label} is required.";
            }
            
            match ($field->type) {
                'email' => $fieldRules[] = 'email',
                'url' => $fieldRules[] = 'url',
                'number' => $fieldRules[] = 'numeric',
                default => null,
            };
            
            if (!empty($fieldRules)) {
                $rules[$fieldName] = $fieldRules;
            }
        }

        if ($form->collect_email) {
            $rules['email'] = 'required|email';
            $messages['email.required'] = 'Email is required.';
        }

        $validated = $request->validate($rules, $messages);

        // Build answers array
        $answers = [];
        foreach ($form->fields as $field) {
            $fieldName = 'field_' . $field->id;
            $value = $request->input($fieldName);
            
            if ($field->type === 'checkbox' && is_array($value)) {
                $value = implode(', ', $value);
            }
            
            $answers[$field->label] = $value;
        }

        // Create response
        $response = FormResponse::create([
            'form_id' => $form->id,
            'session_id' => $request->session()->getId(),
            'ip_hash' => sha1($request->ip()),
            'email' => $form->collect_email ? $request->email : null,
            'answers' => $answers,
            'referrer' => $request->headers->get('referer'),
            'user_agent' => $request->userAgent(),
        ]);

        WebhookService::dispatch('form_submit', [
            'form_id' => $form->id,
            'form_title' => $form->title,
            'response_id' => $response->id,
            'email' => $response->email,
            'answers' => $answers,
            'submitted_at' => $response->created_at->toISOString(),
        ], $form->user_id);

        return redirect()->route('forms.thankyou', $form->slug)
            ->with('form_title', $form->title);
    }

    /**
     * Display thank you page.
     */
    public function thankyou($slug)
    {
        $form = Form::where('slug', $slug)->firstOrFail();
        $formTitle = session('form_title', $form->title);
        
        return view('forms.thankyou', ['form' => $form, 'formTitle' => $formTitle]);
    }
}
