<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Company;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Navigation\MenuItem;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Pages\Tenancy\RegisterTenant;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register Company';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('logo')
                    ->image()
                    ->directory('company-logos')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                    ->disk('public')
                    ->avatar()
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur:true)
                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->readOnly(),
                Textarea::make('description')
                    ->maxLength(65535),
            ]);
    }

    protected function handleRegistration(array $data): Company
    {
        $data['slug'] = $data['slug'] ?? \Illuminate\Support\Str::slug($data['name']);

        $company = Company::create(attributes: $data);

        $company->members()->attach(Auth::user());

        return $company;
    }
}
