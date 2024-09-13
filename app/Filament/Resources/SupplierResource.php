<?php

namespace App\Filament\Resources;

use App\Filament\Exports\SupplierExporter;
use App\Filament\Imports\SupplierImporter;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\ManagementStock\Supplier;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SupplierResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Filament\Resources\SupplierResource\RelationManagers\SupplierTransactionsRelationManager;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationBadgeTooltip = 'Total Suppliers';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Management Stock';

    protected static ?string $navigationParentItem = null;

    protected static ?string $navigationIcon = 'polaris-package-fulfilled-icon';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('supplier_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('supplier_code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('fax')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tax_identification_number')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('state')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('postal_code')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('country')
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->required()
                            ->default('active'),
                        Forms\Components\TextInput::make('credit_limit')
                            ->numeric()
                            ->prefix('IDR')
                            ->maxValue(9999999999999.99),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplier_name')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier_code')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->toggleable()
                    ->colors([
                        'danger' => 'inactive',
                        'success' => 'active',
                    ]),
                Tables\Columns\TextColumn::make('credit_limit')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->hidden(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->hidden()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->label('Status')
                    ->placeholder('All Statuses'),
                Tables\Filters\SelectFilter::make('country')
                    ->options(function () {
                        return Supplier::distinct()->pluck('country', 'country')->toArray();
                    })
                    ->label('Country')
                    ->placeholder('All Countries')
                    ->searchable(),
                Tables\Filters\Filter::make('credit_limit')
                    ->form([
                        Forms\Components\TextInput::make('credit_limit_from')
                            ->numeric()
                            ->label('Minimum Credit Limit'),
                        Forms\Components\TextInput::make('credit_limit_to')
                            ->numeric()
                            ->label('Maximum Credit Limit'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['credit_limit_from'],
                                fn(Builder $query, $limit): Builder => $query->where('credit_limit', '>=', $limit),
                            )
                            ->when(
                                $data['credit_limit_to'],
                                fn(Builder $query, $limit): Builder => $query->where('credit_limit', '<=', $limit),
                            );
                    })->columns(2),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created Until'),
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
                    })->columns(2),
                Tables\Filters\TernaryFilter::make('has_website')
                    ->label('Has Website')
                    ->placeholder('All')
                    ->trueLabel('With Website')
                    ->falseLabel('Without Website')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('website'),
                        false: fn(Builder $query) => $query->whereNull('website'),
                    ),
                Tables\Filters\TernaryFilter::make('has_tax_id')
                    ->label('Has Tax ID')
                    ->placeholder('All')
                    ->trueLabel('With Tax ID')
                    ->falseLabel('Without Tax ID')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('tax_identification_number'),
                        false: fn(Builder $query) => $query->whereNull('tax_identification_number'),
                    ),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('send_email')
                        ->icon('heroicon-o-envelope')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('subject')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\RichEditor::make('content')
                                ->required()
                                ->maxLength(65535),
                        ])
                        ->action(function (Supplier $record, array $data) {
                            // Add email sending logic here
                            Notification::make()
                                ->title('Email sent successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('update_status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                ])
                                ->required(),
                        ])
                        ->action(function (Supplier $record, array $data) {
                            $record->update($data);
                            Notification::make()
                                ->title('Supplier status updated successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->headerActions([
                ExportAction::make()->exporter(SupplierExporter::class),
                ImportAction::make()->importer(SupplierImporter::class)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()->exporter(SupplierExporter::class)
            ])
            ->emptyStateActions([
                CreateAction::make()
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SupplierTransactionsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
