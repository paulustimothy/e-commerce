<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{

    public int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderResource::getEloquentQuery()
            )
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('Order ID')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->searchable(),

                TextColumn::make('grand_total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'processing' => 'warning',
                        'delivered' => 'success',
                        'shipped' => 'success',
                        'canceled' => 'danger',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'new' => 'heroicon-m-sparkles',
                        'processing' => 'heroicon-m-arrow-path',
                        'delivered' => 'heroicon-m-truck',
                        'shipped' => 'heroicon-m-check-badge',
                        'cancelled' => 'heroicon-m-x-circle',
                    })
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger'
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('View Order')
                    ->url(fn (Order $record) => OrderResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye')
                    ->color('info'),
            ]);
    }
}
