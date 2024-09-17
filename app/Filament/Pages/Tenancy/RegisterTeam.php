<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Team;
use App\Models\Company;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Pages\Tenancy\RegisterTenant;

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
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('logo')
                    ->image()
                    ->directory('company-logos')
                    ->maxSize(1024),
                Textarea::make('description')
                    ->maxLength(65535),
                TextInput::make('address')
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('website')
                    ->url()
                    ->maxLength(255),
                TextInput::make('slogan')
                    ->maxLength(255),
                Textarea::make('mission')
                    ->maxLength(65535),
                Textarea::make('vision')
                    ->maxLength(65535),
                Repeater::make('qna')
                    ->schema([
                        TextInput::make('question')
                            ->required(),
                        Textarea::make('answer')
                            ->required(),
                    ])
                    ->collapsible()
                    ->itemLabel(fn(array $state): ?string => $state['question'] ?? null)
                    ->columns(2)
            ]);
    }

    protected function handleRegistration(array $data): Company
    {
        $company = Company::create($data);

        $company->members()->attach(Auth::user());

        return $company;
    }
}
