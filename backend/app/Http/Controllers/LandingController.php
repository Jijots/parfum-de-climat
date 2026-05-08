<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * Controller: LandingController
 *
 * Renders the public-facing landing page at GET /.
 * No authentication required.
 */
class LandingController extends Controller
{
    /**
     * Display the landing page.
     *
     * The view (welcome.blade.php) is a full-page marketing presentation
     * of the Parfum de Climat concept — hero, feature cards, CTA.
     */
    public function index(): View
    {
        return view('welcome');
    }
}
