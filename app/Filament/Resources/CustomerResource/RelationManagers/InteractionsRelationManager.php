<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ManagementCRM\CustomerInteraction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;

class InteractionsRelationManager extends RelationManager
{
    protected static string $relationship = 'interactions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer Interaction Details')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(Filament::getTenant()->id),

                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name', fn($query) => $query->where('status', 'active'))
                            ->nullable()
                            ->searchable()
                            ->preload(),

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
                    ]),
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('customer_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter(),
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus'),
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
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
