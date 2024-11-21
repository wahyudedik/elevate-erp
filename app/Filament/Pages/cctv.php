<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\Camera;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Livewire\WithFileUploads;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;

class cctv extends Page implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    protected static ?string $title = '';
    protected static ?string $navigationIcon = 'heroicon-o-video-camera';
    protected static string $view = 'filament.pages.cctv';
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];
    public $cameras = [];

    public function mount(): void
    {
        $this->form->fill();
        
        $this->cameras = Camera::query()
            ->where('company_id', Filament::getTenant()->id)
            ->where('is_active', true)
            ->get()
            ->toArray();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('branch_id')
                    ->options(
                        Branch::where('company_id', Filament::getTenant()->id)
                            ->pluck('name', 'id')
                    )
                    ->required()
                    ->searchable(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('stream_url')
                    ->required()
                    ->url()
                    ->maxLength(255),
                TextInput::make('location')
                    ->maxLength(255),
                TextInput::make('description')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->default(true),
            ])->columns(2)
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $data['company_id'] = Filament::getTenant()->id;

        Camera::create($data);

        $this->redirect(cctv::getUrl());
    }
}
