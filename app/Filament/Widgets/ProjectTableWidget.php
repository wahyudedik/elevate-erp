<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\ManagementProject\Project;
use Filament\Widgets\TableWidget as BaseWidget;

class ProjectTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Overdue Projects';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Project::query()
                    ->where('end_date', '<', now())
                    ->where('status', 'in_progress')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Project Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('manager.name')
                    ->label('Project Manager')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color('danger'),
            ])
            ->defaultSort('end_date', 'asc');
    }
}
