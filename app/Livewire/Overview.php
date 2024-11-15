<?php

namespace App\Livewire;

use Livewire\Component;

class Overview extends Component
{
    public function render()
    {
        $dangernews = \App\Models\DangerDev::get()->first();
        $news = \App\Models\NewsDev::paginate(1);
        return view('livewire.overview', compact('dangernews', 'news'));
    }
}
