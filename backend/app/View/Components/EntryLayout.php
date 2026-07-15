<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/** Blade shell for staff sign-in / password recovery (no web guard session yet). */
class EntryLayout extends Component
{
    public function __construct(public bool $wide = false) {}

    public function render(): View
    {
        return view('layouts.entry');
    }
}
