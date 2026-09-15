<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseResource\Pages;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
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

    public static function form(Form $form): Form
    {
        return $form
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

                // Repeater con campos dinámicos basados en el tipo de producto
                Forms\Components\Repeater::make('purchaseDetails')
                    ->relationship()
                    ->label('Detalle de Productos')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->label('Producto')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $product = \App\Models\Product::find($state);
                                if ($product) {
                                    $set('price', $product->price ?? 0); // Ajustado a 'price'
                                    $set('item_type', $product->type ?? 'accesorio');
                                }
                            }),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1),

                        Forms\Components\TextInput::make('price') // Cambiado de unit_price a price
                            ->label('Precio Unitario ($)')
                            ->numeric()
                            ->required(),

                        // Campos dinámicos / JSON de especificaciones (specs)
                        Forms\Components\Select::make('item_type')
                            ->label('Tipo de Especificación')
                            ->options([
                                'instrumento' => 'Instrumento Musical',
                                'equipo' => 'Equipo de Audio / Sonido',
                                'accesorio' => 'Accesorio General',
                            ])
                            ->reactive()
                            ->default('accesorio'),

                        // Formulario dinámico para Instrumentos (se guarda en specs)
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('specs.material')
                                ->label('Material'),
                            Forms\Components\TextInput::make('specs.cuerdas')
                                ->label('Cantidad de Cuerdas')
                                ->numeric(),
                        ])
                        ->visible(fn (Forms\Get $get): bool => $get('item_type') === 'instrumento')
                        ->columns(2),

                        // Formulario dinámico para Equipos (se guarda en specs)
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('specs.potencia')
                                ->label('Potencia / Watts'),
                            Forms\Components\TextInput::make('specs.voltaje')
                                ->label('Voltaje'),
                        ])
                        ->visible(fn (Forms\Get $get): bool => $get('item_type') === 'equipo')
                        ->columns(2),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('total')
                    ->label('Total General ($)')
                    ->numeric()
                    ->required()
                    ->default(0),
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Fecha de Compra')
                    ->date()
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
                    ->dateTime()
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