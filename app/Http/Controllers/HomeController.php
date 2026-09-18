<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

use App\Models\Page;
use App\Models\Button;

class HomeController extends Controller
{
    //Show home message, number of buttons and updated pages
    public function home()
    {

        $message = Page::select('home_message')->first();

        $countButton = Button::count();

        // Postgres requires every selected column to be grouped or aggregated
        // (MySQL-only behavior upstream). Grouping by the selected user columns
        // is semantically identical: user_id determines them via the PK join.
        $updatedPages = DB::table('links')->join('users', 'users.id', '=', 'links.user_id')->select('users.littlelink_name', 'users.image', DB::raw('max(links.created_at) as created_at'))->groupBy('links.user_id', 'users.littlelink_name', 'users.image')->orderBy('created_at', 'desc')->take(4)->get();

        return view('home', ['message' => $message, 'countButton' => $countButton, 'updatedPages' => $updatedPages]);
    }

    // Show demo page
    public function demo(request $request)
    {
        $message = Page::select('home_message')->first();

        $countButton = Button::count();

        // Same Postgres GROUP BY fix as home()
        $updatedPages = DB::table('links')->join('users', 'users.id', '=', 'links.user_id')->select('users.littlelink_name', 'users.image', DB::raw('max(links.created_at) as created_at'))->groupBy('links.user_id', 'users.littlelink_name', 'users.image')->orderBy('created_at', 'desc')->take(4)->get();

        return view('demo', ['message' => $message, 'countButton' => $countButton, 'updatedPages' => $updatedPages]);
    }

}
