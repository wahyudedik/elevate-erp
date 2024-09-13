<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ExportBulkAction;
use App\Models\ManagementCRM\CustomerInteraction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Exports\CustomerInteractionExporter;
use App\Filament\Imports\CustomerInteractionImporter;
use App\Filament\Resources\CustomerInteractionResource\Pages;
use App\Filament\Resources\CustomerInteractionResource\RelationManagers;

class CustomerInteractionResource extends Resource
{
    protected static ?string $model = CustomerInteraction::class;

    protected static ?string $navigationBadgeTooltip = 'Total Customer Interactions';
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management CRM';

    protected static ?string $navigationParentItem = 'Customers';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name', function ($query) {
                        return $query->where('status', 'active');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->searchable()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('company')
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->required()
                            ->default('active'),
                    ])->columns(2),
                Forms\Components\DatePicker::make('interaction_date')
                    ->required()
                    ->default(now()),
                Forms\Components\Select::make('interaction_type')
                    ->options([
                        'email' => 'Email',
                        'call' => 'Call',
                        'meeting' => 'Meeting',
                        'chat' => 'Chat',
                    ])
                    ->required()
                    ->multiple(),
                Forms\Components\MarkdownEditor::make('details')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('interaction_date')
                    ->label('Date')
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('interaction_type')
                    ->label('Type')
                    ->toggleable()
                    ->badge()
                    ->icon(function (string $state): string {
                        return match ($state) {
                            'email' => 'heroicon-o-envelope',
                            'call' => 'heroicon-o-phone',
                            'meeting' => 'heroicon-o-users',
                            'chat' => 'heroicon-o-chat-bubble-left-right',
                            default => 'heroicon-o-question-mark-circle',
                        };
                    })
                    ->color(function (string $state): string {
                        return match ($state) {
                            'email' => 'info',
                            'call' => 'success',
                            'meeting' => 'danger',
                            'chat' => 'primary',
                            default => 'secondary',
                        };
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('details')
                    ->label('Details')
                    ->limit(50)
                    ->toggleable()
                    ->tooltip(function (CustomerInteraction $record): string {
                        return $record->details ?? '';
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                // Tables\Filters\DateFilter::make('interaction_date'),
                Tables\Filters\SelectFilter::make('interaction_type')
                    ->options([
                        'email' => 'Email',
                        'call' => 'Call',
                        'meeting' => 'Meeting',
                        'chat' => 'Chat',
                    ])
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('has_details')
                    ->label('Has Details')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('details'),
                        false: fn(Builder $query) => $query->whereNull('details'),
                    ),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })->columns(2)
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('sendEmail')
                        ->icon('heroicon-o-envelope')
                        ->color('success')
                        ->action(function (CustomerInteraction $record) {
                            // Add email sending logic here
                            $customer = $record->customer;
                            $emailContent = "Dear {$customer->name},\n\nThank you for your recent interaction with us. We appreciate your business.\n\nBest regards,\nOur Company";

                            Mail::to($customer->email)->send(new \App\Mail\CustomerInteractionFollowUp($record, $emailContent));

                            Notification::make()
                                ->title('Email Sent')
                                ->body("An email has been sent to {$customer->name}")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('Phone Number')
                        ->icon('heroicon-o-phone')
                        ->color('info')
                        ->action(function (CustomerInteraction $record) {
                            // Add logic to display customer's phone number
                            $phoneNumber = $record->customer->phone;
                            Notification::make()
                                ->title('Customer Phone Number')
                                ->body($phoneNumber)
                                ->info()
                                ->send();
                        }),
                    Tables\Actions\Action::make('Whatsapp')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('success')
                        ->action(function (CustomerInteraction $record) {
                            // Add WhatsApp sending logic here
                            $phoneNumber = $record->customer->phone;
                            $waLink = "https://wa.me/" . preg_replace('/^0/', '+62', preg_replace('/[^0-9]/', '', $phoneNumber));
                            $message = urlencode("Dear {$record->customer->name},\n\nThank you for your recent interaction with us. 
                            \n\n{$record->details}\n\nIf you have any questions, please don't hesitate to contact us.\n\nBest regards,\nOur Company");
                            $waLink .= "?text={$message}";

                            Notification::make()
                                ->title('WhatsApp Link')
                                ->body("Click to open WhatsApp with pre-filled message: <a href=\"{$waLink}\" target=\"_blank\">{$waLink}</a>")
                                ->success()
                                ->send();
                        })
                        ->openUrlInNewTab()
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('jadwalkanMeet')
                        ->icon('heroicon-o-calendar')
                        ->color('primary')
                        ->action(function (CustomerInteraction $record) {
                            // Add logic to schedule a meeting
                        })
                        ->form([
                            Forms\Components\DateTimePicker::make('meeting_time')
                                ->label('Meeting Time')
                                ->required(),
                            Forms\Components\TextInput::make('meeting_link')
                                ->label('Meeting Link')
                                ->url()
                                ->required(),
                        ]),
                ])
            ])
            ->headerActions([
                ExportAction::make()->exporter(CustomerInteractionExporter::class),
                ImportAction::make()->importer(CustomerInteractionImporter::class),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    // Tables\Actions\BulkAction::make('sendBulkEmail')
                    //     ->icon('heroicon-o-envelope')
                    //     ->action(function (Collection $records) {
                    //         // Add bulk email sending logic here
                    //     })
                    //     ->requiresConfirmation(),
                ]),
                ExportBulkAction::make()->exporter(CustomerInteractionExporter::class)
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver()
                    ->form([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\DatePicker::make('interaction_date')
                            ->required(),
                        Forms\Components\Select::make('interaction_type')
                            ->options([
                                'email' => 'Email',
                                'call' => 'Call',
                                'meeting' => 'Meeting',
                                'chat' => 'Chat',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('details')
                            ->nullable()
                            ->columnSpan('full'),
                    ])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerInteractions::route('/'),
            'create' => Pages\CreateCustomerInteraction::route('/create'),
            'edit' => Pages\EditCustomerInteraction::route('/{record}/edit'),
        ];
    }
}
