<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Livewire\WithFileUploads;
use App\Models\Camera;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class cctv extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.cctv';

    protected static bool $shouldRegisterNavigation = false; 

    use WithFileUploads;

    public $streamUrl;
    public $cameras = [];
    
    public function mount()
    {
        $this->cameras = [
            'camera1' => [
                'name' => 'Camera 1',
                'stream_url' => 'rtsp://camera1_ip:port/stream',
            ],
            'camera2' => [
                'name' => 'Camera 2', 
                'stream_url' => 'rtsp://camera2_ip:port/stream',
            ]
        ];
    }
}
