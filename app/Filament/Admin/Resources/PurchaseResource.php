<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseResource\Pages;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Compras';
    protected static ?string $modelLabel = 'Compra';
    protected static ?string $pluralModelLabel = 'Compras';

    public static function calculateTotal(Forms\Get $get, Forms\Set $set): void
    {
        $items = $get('purchaseDetails') ?? $get('../../purchaseDetails') ?? [];
        $total = 0;

        foreach ($items as $item) {
            $qty = floatval($item['quantity'] ?? 0);
            $price = floatval($item['price'] ?? 0);
            $total += ($qty * $price);
        }

        $formatted = number_format($total, 2, '.', '');
        $set('total', $formatted);
        $set('../../total', $formatted);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la Factura / Compra')
                    ->description('Selecciona el proveedor y la fecha de la transacción.')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->label('Proveedor')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\DatePicker::make('purchase_date')
                            ->label('Fecha de Compra')
                            ->required()
                            ->default(now()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Productos Comprados')
                    ->description('Indica los productos adquiridos, sus cantidades y costos. Si un producto es nuevo, puedes darlo de alta con sus especificaciones técnicas mediante el botón (+).')
                    ->icon('heroicon-m-shopping-bag')
                    ->schema([
                        Forms\Components\Repeater::make('purchaseDetails')
                            ->relationship()
                            ->label('Ítems de la Compra')
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotal($get, $set))
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->label('Producto')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre del Producto')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Ej: Fender Stratocaster / Shure SM58 / Marshall MG15')
                                            ->columnSpanFull(),

                                        Forms\Components\Select::make('type')
                                            ->label('Categoría / Tipo de Producto')
                                            ->options(ProductResource::getProductTypeOptions())
                                            ->required()
                                            ->reactive()
                                            ->default('cuerda')
                                            ->columnSpan(2),

                                        Forms\Components\TextInput::make('price')
                                            ->label('Precio de Venta al Público ($)')
                                            ->numeric()
                                            ->prefix('$')
                                            ->required()
                                            ->default(0)
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('stock')
                                            ->label('Stock Inicial')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Se sumará automáticamente con la compra.')
                                            ->columnSpan(1),

                                        ...ProductResource::getProductSpecsFormSchema(),
                                    ])
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $product = \App\Models\Product::find($state);
                                        if ($product) {
                                            $set('price', $product->price ?? 0);
                                        }
                                        self::calculateTotal($get, $set);
                                    })
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotal($get, $set))
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('price')
                                    ->label('Costo Unitario ($)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotal($get, $set))
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->columnSpanFull()
                            ->defaultItems(1)
                            ->addActionLabel('+ Agregar otro producto a la compra'),

                        Forms\Components\TextInput::make('total')
                            ->label('Total General de la Compra ($)')
                            ->numeric()
                            ->prefix('$')
                            ->readOnly()
                            ->dehydrated()
                            ->default(0)
                            ->helperText('Se calcula automáticamente multiplicando cantidades por costo unitario.'),
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['supplier', 'purchaseDetails.product'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->formatStateUsing(fn ($state): string => '#' . str_pad($state, 4, '0', STR_PAD_LEFT))
                    ->weight(FontWeight::SemiBold)
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->weight(FontWeight::Bold)
                    ->color('secondary')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Fecha de Compra')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total ($)')
                    ->money('ARS')
                    ->sortable(),

                Tables\Columns\IconColumn::make('deleted_at')
                    ->label('Estado')
                    ->options([
                        'heroicon-o-x-circle' => fn ($state): bool => filled($state),
                        'heroicon-o-check-circle' => fn ($state): bool => blank($state),
                    ])
                    ->colors([
                        'danger' => fn ($state): bool => filled($state),
                        'success' => fn ($state): bool => blank($state),
                    ])
                    ->tooltip(fn ($record): string => filled($record->deleted_at) ? 'Anulada el: ' . $record->deleted_at : 'Activa'),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Fecha Anulación')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Los estados (activas, anuladas, todas) se manejan desde las pestañas superiores
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Anular')
                    ->modalHeading('Anular Compra')
                    ->modalDescription('¿Estás seguro de anular esta compra? Esto revertirá el stock automáticamente.')
                    ->hidden(fn (Purchase $record): bool => $record->trashed()),
                Tables\Actions\RestoreAction::make()
                    ->label('Restaurar')
                    ->modalHeading('Restaurar Compra')
                    ->modalDescription('¿Deseas restaurar esta compra?')
                    ->visible(fn (Purchase $record): bool => $record->trashed()),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
        ];
    }
}