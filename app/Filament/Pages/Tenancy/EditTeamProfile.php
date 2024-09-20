<?php

namespace App\Filament\Pages\Tenancy;

use App\Filament\Resources\EmployeeResource\RelationManagers\UserRelationManager;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
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
                Section::make('Company Information')
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
                            ->live()
                            ->afterStateUpdated(fn($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->readOnly(),
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
                    ])->columns(2),
            ]);
    }

    public static function getRelations(): array
        {
            return [
                
            ];
        }
    
}
