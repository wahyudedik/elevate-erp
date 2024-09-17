<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Forms\Form;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Pages\Tenancy\EditTenantProfile;

class EditTeamProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Company profile';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('logo')
                    ->image()
                    ->directory('company-logos')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                    ->disk('public')
                    ->visibility('public')
                    ->maxFiles(1)
                    ->avatar()
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
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
                    ->label('Q&A')
                    ->schema([
                        TextInput::make('question')
                            ->required(),
                        Textarea::make('answer')
                            ->required(),
                    ])
                    ->collapsible()
                    ->itemLabel(fn(array $state): ?string => $state['question'] ?? null)
                    ->columnSpanFull()
            ])->columns(2);
    }
}