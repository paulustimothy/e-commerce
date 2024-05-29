<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Filament\Admin\Resources\OrderResource\RelationManagers;
use App\Filament\Admin\Resources\OrderResource\RelationManagers\AddressRelationManager;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?int $navigationSort = 5; //sort

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make([
                    Section::make('Order Information')->schema([
                        Select::make('user_id')
                            ->required()
                            ->relationship('user', 'name')
                            ->label('Customer')
                            ->searchable()
                            ->native(false)
                            ->preload(),
                        Select::make('payment_method')
                            ->options([
                                'stripe' => 'Stripe',
                                'cod' => 'Cash On Delivery'
                            ])
                            ->native(false)
                            ->live()
                            ->required(),
                        Select::make('payment_status')
                            ->required()
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                            ])
                            ->native(false)
                            ->default('pending'),
                        ToggleButtons::make('status')
                            ->required()
                            ->inline()
                            ->options([
                                'new' => 'New',
                                'processing' => 'Processing',
                                'delivered' => 'Delivered',
                                'shipped' => 'Shipped',
                                'cancelled' => 'Cancelled',
                            ])
                            ->colors([
                                'new' => 'info',
                                'processing' => 'warning',
                                'delivered' => 'success',
                                'shipped' => 'success',
                                'cancelled' => 'danger',
                            ])
                            ->icons([
                                'new' => 'heroicon-m-sparkles',
                                'processing' => 'heroicon-m-arrow-path',
                                'delivered' => 'heroicon-m-truck',
                                'shipped' => 'heroicon-m-check-badge',
                                'cancelled' => 'heroicon-m-x-circle',
                            ])
                            ->default('new'),

                        Select::make('currency')
                            ->required()
                            ->options([
                                'idr' => 'IDR',
                            ])
                            ->native(false)
                            ->default('idr'),

                        Select::make('shipping_method')
                            ->options([
                                'j&t' => 'J&T',
                                'jne' => 'JNE'
                            ])
                            ->native(false)
                            ->disabled(fn (Get $get): bool => $get('payment_method') === 'cod'),
                        Textarea::make('notes')
                            ->columnSpanFull()
                            ->rows(3),
                    ])->columns(2)
                ])->columnSpanFull(),

                Section::make('Order Item')->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Select::make('product_id')
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->distinct()
                                ->reactive()
                                ->native(false)
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->afterStateUpdated(fn ($state, Set $set) => $set('unit_amount', Product::find($state)->price ?? 0))
                                ->afterStateUpdated(fn ($state, Set $set) => $set('total_amount', Product::find($state)->price ?? 0))
                                ->columnSpan(4),

                            TextInput::make('quantity')
                                ->numeric()
                                ->required()
                                ->default(1)
                                ->minValue(1)
                                ->reactive()
                                ->afterStateUpdated(fn ($state, Set $set, Get $get) => $set('total_amount', $state * $get('unit_amount')))
                                ->columnSpan(2),

                            TextInput::make('unit_amount')
                                ->numeric()
                                ->required()
                                ->readOnly()
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('IDR')
                                ->columnSpan(3),

                            TextInput::make('total_amount')
                                ->numeric()
                                ->required()
                                ->readOnly()
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('IDR')
                                ->columnSpan(3),

                        ])->columns(12),

                    Placeholder::make('grand_total_placeholder')
                        ->label('Grand Total')
                        ->content(function (Get $get, Set $set) {
                            $total = 0;
                            if (!$repeaters = $get('items')) {
                                return $total;
                            }
                            foreach ($repeaters as $key => $repeater) {
                                $total += $get("items.{$key}.total_amount");
                            }
                            $set('grand_total', $total);
                            return Number::currency($total, 'IDR');
                        }),

                    Hidden::make('grand_total')
                        ->default(0),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('Customer'),
                TextColumn::make('grand_total')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => Str::of(Number::currency($state, 'IDR', 'id'))->replace(',00', '')),
                TextColumn::make('payment_method')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->searchable()
                    ->sortable(),
                SelectColumn::make('status')
                    ->options([
                        'new' => 'New',
                        'processing' => 'Processing',
                        'delivered' => 'Delivered',
                        'shipped' => 'Shipped',
                        'cancelled' => 'Cancelled',
                    ])
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AddressRelationManager::class
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
