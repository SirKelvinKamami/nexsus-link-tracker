<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandingPage;
use App\Models\FormResponse;

class PublicLandingPageController extends Controller
{
    /**
     * Display a landing page by slug.
     */
    public function show($slug)
    {
        $page = LandingPage::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        if (!$page->isPublished()) {
            abort(404);
        }

        $form = $page->form;
        $blocks = $page->getBlocks();
        $settings = $page->settings;

        return view('landing-pages.show', compact('page', 'form', 'blocks', 'settings'));
    }

    /**
     * Submit a landing page form (email capture).
     */
    public function submit(Request $request, $slug)
    {
        $page = LandingPage::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        if (!$page->isPublished()) {
            return redirect()->back()->with('error', 'This page is no longer active.');
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        // If page has an embedded form, submit to that
        if ($page->form_id && $page->form) {
            return redirect()->route('forms.submit', $page->form->slug)
                ->with('email', $request->email);
        }

        // Otherwise, store as a simple email capture
        $existingResponse = $page->responses()->where('email', $request->email)->first();
        
        if (!$existingResponse) {
            $page->responses()->create([
                'email' => $request->email,
                'ip_hash' => sha1($request->ip()),
                'referrer' => $request->headers->get('referer'),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return redirect()->route('landing-pages.thankyou', $page->slug);
    }

    /**
     * Display thank you page.
     */
    public function thankyou($slug)
    {
        $page = LandingPage::where('slug', $slug)->firstOrFail();
        
        return view('landing-pages.thankyou', ['page' => $page]);
    }
}
