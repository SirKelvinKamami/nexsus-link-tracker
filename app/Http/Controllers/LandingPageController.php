<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\LandingPage;
use App\Models\Form;

class LandingPageController extends Controller
{
    /**
     * List all landing pages for the authenticated user.
     */
    public function index()
    {
        $userId = Auth::id();
        $pages = LandingPage::where('user_id', $userId)
            ->with('form')
            ->orderByDesc('updated_at')
            ->get();

        return view('studio.landing-pages.index', compact('pages'));
    }

    /**
     * Show form for creating a new landing page.
     */
    public function create()
    {
        $userId = Auth::id();
        $forms = Form::where('user_id', $userId)->get();
        
        return view('studio.landing-pages.create', compact('forms'));
    }

    /**
     * Store a new landing page.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();
        $slug = Str::slug($request->title);
        
        // Ensure unique slug
        $originalSlug = $slug;
        $count = 1;
        while (LandingPage::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $page = LandingPage::create([
            'user_id' => $userId,
            'title' => $request->title,
            'slug' => $slug,
            'description' => $request->description ?? '',
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'content' => $this->getDefaultContent(),
            'settings' => $this->getDefaultSettings(),
            'is_published' => false,
        ]);

        return redirect()->route('landing-pages.edit', $page->id)
            ->with('success', 'Landing page created. Now customize it.');
    }

    /**
     * Show form for editing a landing page.
     */
    public function edit($id)
    {
        $userId = Auth::id();
        $page = LandingPage::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $forms = Form::where('user_id', $userId)->get();

        return view('studio.landing-pages.edit', compact('page', 'forms'));
    }

    /**
     * Update a landing page.
     */
    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $page = LandingPage::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_published' => 'nullable|boolean',
            'collect_emails' => 'nullable|boolean',
            'form_id' => 'nullable|exists:forms,id',
            'content' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        $page->update([
            'title' => $request->title,
            'description' => $request->description ?? '',
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'is_published' => $request->boolean('is_published', $page->is_published),
            'collect_emails' => $request->boolean('collect_emails', $page->collect_emails),
            'form_id' => $request->form_id,
            'content' => $request->content ?? $page->content,
            'settings' => $request->settings ?? $page->settings,
        ]);

        return redirect()->route('landing-pages.edit', $page->id)
            ->with('success', 'Landing page updated.');
    }

    /**
     * Delete a landing page.
     */
    public function destroy($id)
    {
        $userId = Auth::id();
        $page = LandingPage::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $page->delete();

        return redirect()->route('landing-pages.index')
            ->with('success', 'Landing page deleted.');
    }

    /**
     * Save page content (blocks) via AJAX.
     */
    public function saveContent(Request $request, $id)
    {
        $userId = Auth::id();
        $page = LandingPage::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $page->update([
            'content' => $request->content,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Content saved.',
        ]);
    }

    /**
     * Save page settings via AJAX.
     */
    public function saveSettings(Request $request, $id)
    {
        $userId = Auth::id();
        $page = LandingPage::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $page->update([
            'settings' => $request->settings,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Settings saved.',
        ]);
    }

    /**
     * Publish/unpublish a landing page.
     */
    public function togglePublish($id)
    {
        $userId = Auth::id();
        $page = LandingPage::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $page->update([
            'is_published' => !$page->is_published,
            'published_at' => !$page->is_published ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'is_published' => $page->is_published,
            'public_url' => $page->is_published ? url("/lp/{$page->slug}") : null,
        ]);
    }

    /**
     * Get default content structure.
     */
    protected function getDefaultContent()
    {
        return [
            'blocks' => [
                [
                    'id' => Str::uuid(),
                    'type' => 'hero',
                    'data' => [
                        'heading' => 'Welcome',
                        'subheading' => 'Your subheading here',
                        'background_color' => '#667eea',
                        'text_color' => '#ffffff',
                    ],
                ],
                [
                    'id' => Str::uuid(),
                    'type' => 'text',
                    'data' => [
                        'content' => '<p>Add your content here.</p>',
                        'alignment' => 'center',
                    ],
                ],
                [
                    'id' => Str::uuid(),
                    'type' => 'cta',
                    'data' => [
                        'heading' => 'Ready to get started?',
                        'button_text' => 'Get Started',
                        'button_url' => '#',
                        'button_color' => '#667eea',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get default settings.
     */
    protected function getDefaultSettings()
    {
        return [
            'background_color' => '#ffffff',
            'text_color' => '#333333',
            'font_family' => 'Inter, sans-serif',
            'container_width' => '800px',
            'padding' => '60px 20px',
            'custom_css' => '',
            'custom_head' => '',
        ];
    }
}
