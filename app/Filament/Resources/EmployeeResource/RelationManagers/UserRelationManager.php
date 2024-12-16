<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class UserRelationManager extends RelationManager
{
    protected static string $relationship = 'user';

    protected static ?string $title = 'Pengguna';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengguna')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('user-images')
                            ->visibility('public')
                            ->maxSize(5024)
                            ->columnSpanFull()
                            ->label('Foto Profil'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->label('Surel'),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Tanggal Verifikasi Email'),
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            // ->multiple()
                            ->preload()
                            ->searchable()
                            ->label('Peran'),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->label('Kata Sandi'),
                        Forms\Components\Select::make('usertype')
                            ->options([
                                'staff' => 'Staf',
                                'member' => 'Anggota',
                            ])
                            ->required()
                            ->default('staff')
                            ->label('Tipe Pengguna'),
                    ])->columns(2),
                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Dibuat pada')
                            ->content(fn($record): string => $record?->created_at ? $record->created_at->diffForHumans() : '-'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Terakhir diubah pada')
                            ->content(fn($record): string => $record?->updated_at ? $record->updated_at->diffForHumans() : '-'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No.')
                    ->formatStateUsing(fn($state, $record, $column) => $column->getTable()->getRecords()->search($record) + 1)
                    ->alignCenter()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->toggleable()
                    ->searchable()
                    ->icon('heroicon-m-user')
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Surel')
                    ->icon('heroicon-m-envelope')
                    ->toggleable()
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Surel disalin')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Verifikasi Email')
                    ->icon('heroicon-m-check-badge')
                    ->toggleable()
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('usertype')
                    ->label('Tipe Pengguna')
                    ->toggleable()
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'dev' => 'Pengembang',
                        'staff' => 'Staf',
                        'member' => 'Anggota',
                    }),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Verifikasi Email')
                    ->placeholder('Semua Pengguna')
                    ->trueLabel('Pengguna Terverifikasi')
                    ->falseLabel('Pengguna Belum Terverifikasi')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn(Builder $query) => $query->whereNull('email_verified_at'),
                    ),
                Tables\Filters\Filter::make('created_at')
                    ->label('Dibuat Pada')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
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
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->color('success'),
                    Tables\Actions\EditAction::make()->color('info'),
                    Tables\Actions\DeleteAction::make()->color('danger'),
                ])
            ])
            ->headerActions([
                // CreateAction::make()->color('primary')->icon('heroicon-o-plus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
