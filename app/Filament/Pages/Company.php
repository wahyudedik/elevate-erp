<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Infolists\Infolist;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class Company extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $model = \App\Models\Company::class;

    protected static string $view = 'filament.pages.company';

    protected static ?string $navigationLabel = 'Company';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Company Information';

    public ?TemporaryUploadedFile $logo = null;

    // public function infolist(Infolist $infolist): Infolist
    // {
    //     return $infolist
    //         ->state($this->getCompanyData(  ))
    //         ->schema([
    //             TextEntry::make('name')
    //                 ->label('Company Name'),
    //             ImageEntry::make('logo')
    //                 ->label('Logo'),
    //             TextEntry::make('description')
    //                 ->label('Description'),
    //             TextEntry::make('address')
    //                 ->label('Address'),
    //             TextEntry::make('phone')
    //                 ->label('Phone'),
    //             TextEntry::make('email')
    //                 ->label('Email'),
    //             TextEntry::make('website')
    //                 ->label('Website')
    //                 ->openUrlInNewTab(),
    //             TextEntry::make('slogan')
    //                 ->label('Slogan'),
    //             TextEntry::make('mission')
    //                 ->label('Mission'),
    //             TextEntry::make('vision')
    //                 ->label('Vision'),
    //             TextEntry::make('qna')
    //                 ->label('Q&A')
    //                 ->listWithLineBreaks(),
    //         ]);
    // }

   
    protected function getCompanyData()
    {
        // Fetch and return your company data here
        // For example:
        return \App\Models\Company::first();
    }

    protected function getFormModel(): string
    {
        return \App\Models\Company::class;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->submit('save'),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('name')->required(),
            FileUpload::make('logo')
                ->image()
                ->disk('public')
                ->directory('company-logos')
                ->label('Company Logo'),
            Textarea::make('description')->required(),
            TextInput::make('address')->required(),
            TextInput::make('phone')->required(),
            TextInput::make('email')->email()->required(),
            TextInput::make('website')->url()->required(),
            TextInput::make('slogan')->required(),
            Textarea::make('mission')->required(),
            Textarea::make('vision')->required(),
            KeyValue::make('qna')->required(),
        ];
    }

    public function save()
    {
        $data = $this->form->getState();

        $company = \App\Models\Company::first();

        // Handle logo upload
        if ($this->logo) {
            $data['logo'] = $this->logo->store('company-logos', 'public');
        }

        if ($company) {
            $company->update($data);
        } else {
            \App\Models\Company::create($data);
        }

        Notification::make()
            ->title('Company information saved successfully')
            ->success()
            ->send();

        $this->redirect(Company::getUrl());

        // Refresh the form data
        $this->form->fill($data);

        // Show a notification
        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();
    }
}
