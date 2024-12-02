<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Clusters\CustomerRelations;
use Filament\Tables\Actions\ExportBulkAction;
use App\Models\ManagementCRM\CustomerInteraction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Exports\CustomerInteractionExporter;
use App\Filament\Imports\CustomerInteractionImporter;
use App\Filament\Resources\CustomerInteractionResource\Pages;
use App\Filament\Resources\CustomerInteractionResource\RelationManagers;
use App\Filament\Resources\CustomerInteractionResource\RelationManagers\CustomerRelationManager;

class CustomerInteractionResource extends Resource
{
    protected static ?string $model = CustomerInteraction::class;

    protected static ?string $navigationLabel = 'Interaksi Pelanggan';

    protected static ?string $modelLabel = 'Interaksi Pelanggan';
    
    protected static ?string $pluralModelLabel = 'Interaksi Pelanggan';

    protected static ?string $cluster = CustomerRelations::class;

    protected static ?int $navigationSort = 18;

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'customerInteractions';

    protected static ?string $navigationGroup = 'Manajemen CRM';

    protected static ?string $navigationIcon = 'carbon-customer-service';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Section::make('Data Customer')
                            ->schema([
                                Forms\Components\Select::make('branch_id')
                                    ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                                    ->nullable()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('customer_id')
                                    ->relationship('customer', 'name', function ($query) {
                                        return $query->where('status', 'active');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\Hidden::make('company_id')
                                            ->default(Filament::getTenant()->id),
                                        Forms\Components\Select::make('branch_id')
                                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                                            ->nullable()
                                            ->searchable()
                                            ->preload(),
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
                            ])
                    ])->columns(2),
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn($record): string => $record?->updated_at ? $record->updated_at->diffForHumans() : '-'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->icon('heroicon-m-building-storefront')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('interaction_date')
                    ->label('Date')
                    ->date()
                    ->sortable()
                    ->icon('heroicon-o-calendar')
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
            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Branch'),
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
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
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('sendEmail')
                        ->icon('heroicon-o-envelope')
                        ->color('success')
                        ->action(function (CustomerInteraction $record) {
                            // Add email sending logic here
                            $customer = $record->customer;
                            $emailContent = $record->details ?? 'No details provided.';

                            Mail::to($customer->email)->send(new \App\Mail\CustomerInteractionFollowUp($customer, $emailContent));

                            Notification::make()
                                ->title('Email Sent')
                                ->body("An email has been sent to {$customer->name}")
                                ->success()
                                ->sendToDatabase(Auth::user());
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
                                ->sendToDatabase(Auth::user());
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
                                ->body("Click to open WhatsApp with pre-filled message: <a href=\"{$waLink}\" target=\"_blank\" class=\"button\">Click Here</a>")
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                        ->openUrlInNewTab()
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('jadwalkanMeet')
                        ->icon('heroicon-o-calendar')
                        ->color('primary')
                        ->form([
                            Forms\Components\DateTimePicker::make('meeting_time')
                                ->label('Meeting Time')
                                ->required(),
                            Forms\Components\TextInput::make('meeting_link')
                                ->label('Meeting Link')
                                ->url()
                                ->required(),
                        ])
                        ->action(function (array $data, CustomerInteraction $record) {
                            // Add logic to schedule a meeting
                            $customer = $record->customer;
                            $meetingTime = $data['meeting_time'];
                            $meetingLink = $data['meeting_link'];
                            $emailContent = "Meeting scheduled for: " . $meetingTime . "\nMeeting Link: " . $meetingLink . "\n\n" . ($record->details ?? 'No details provided.');

                            Mail::to($customer->email)->send(new \App\Mail\CustomerInteractionFollowUpMeet($customer, $emailContent));

                            Notification::make()
                                ->title('Email Sent')
                                ->body("An email has been sent to {$customer->name} schedule meet")
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ])
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus'),
                ActionGroup::make([
                    ExportAction::make()
                        ->exporter(CustomerInteractionExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Customer interactions exported successfully' . ' ' . now()->toDateTimeString())
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                    ImportAction::make()
                        ->importer(CustomerInteractionImporter::class)
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('warning')
                        ->after(function () {
                            Notification::make()
                                ->title('Customer interactions imported successfully' . ' ' . now()->toDateTimeString())
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        })
                ])->icon('heroicon-o-cog-6-tooth'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(CustomerInteractionExporter::class)
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->title('Customer interactions exported successfully' . ' ' . now()->format('Y-m-d H:i:s'))
                                ->icon('heroicon-o-check-circle')
                                ->success()
                                ->sendToDatabase(Auth::user());
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CustomerRelationManager::class,
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'company_id',
            'branch_id',
            'customer_id',
            'interaction_type',   // call, email, meeting, note, etc.
            'interaction_date',
            'details',
        ];
    }
}
