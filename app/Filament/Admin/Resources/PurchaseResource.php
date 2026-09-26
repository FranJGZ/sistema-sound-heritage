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
use Filament\Tables\Enums\FiltersLayout;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;
    protected static ?string $navigationGroup = 'Tienda';
    protected static ?int $navigationSort = 4;
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

                Tables\Columns\TextColumn::make('purchaseDetails.product.name')
                    ->label('Productos')
                    ->badge()
                    ->separator(', '),

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
                // 1. Estado Contable
                Tables\Filters\TrashedFilter::make()
                    ->label('Estado de la Compra')
                    ->placeholder('Solo Compras Activas')
                    ->trueLabel('Todas (Activas + Anuladas)')
                    ->falseLabel('Solo Compras Anuladas'),

                // 2. Por Proveedor
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),

                // 3. Por Producto específico incluido en la compra
                Tables\Filters\SelectFilter::make('producto_comprado')
                    ->label('Contiene el Producto')
                    ->options(fn (): array => \App\Models\Product::query()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn (Builder $q) => $q->whereHas('purchaseDetails', fn (Builder $sq) => $sq->where('product_id', $data['value']))
                        );
                    }),

                // 4. Por Categoría de Mercadería
                Tables\Filters\SelectFilter::make('categoria_producto')
                    ->label('Categoría de Mercadería')
                    ->options(ProductResource::getProductTypeOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn (Builder $q) => $q->whereHas('purchaseDetails.product', fn (Builder $sq) => $sq->where('type', $data['value']))
                        );
                    }),

                // 5. Rango de Fechas de Compra
                Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        Forms\Components\DatePicker::make('desde')->label('Fecha Desde'),
                        Forms\Components\DatePicker::make('hasta')->label('Fecha Hasta'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(filled($data['desde'] ?? null), fn (Builder $q) => $q->whereDate('purchase_date', '>=', $data['desde']))
                            ->when(filled($data['hasta'] ?? null), fn (Builder $q) => $q->whereDate('purchase_date', '<=', $data['hasta']));
                    }),

                // 6. Rango de Monto Total ($)
                Tables\Filters\Filter::make('rango_total')
                    ->form([
                        Forms\Components\TextInput::make('total_min')->label('Monto Mínimo ($)')->numeric()->prefix('$'),
                        Forms\Components\TextInput::make('total_max')->label('Monto Máximo ($)')->numeric()->prefix('$'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(filled($data['total_min'] ?? null), fn (Builder $q) => $q->where('total', '>=', $data['total_min']))
                            ->when(filled($data['total_max'] ?? null), fn (Builder $q) => $q->where('total', '<=', $data['total_max']));
                    }),
                       ])
            ->filtersFormColumns(3)
            ->filtersFormWidth(\Filament\Support\Enums\MaxWidth::FourExtraLarge)
            ->headerActions([
                Tables\Actions\Action::make('exportar_pdf')
                    ->label('Exportar PDF')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('primary')
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredSortedTableQuery()->get();

                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf-generic', [
                            'title'   => 'Reporte de Compras a Proveedores',
                            'summary' => [
                                'Compras Listadas' => $records->count(),
                                'Compras Activas'  => $records->whereNull('deleted_at')->count(),
                                'Compras Anuladas' => $records->whereNotNull('deleted_at')->count(),
                                'Inversión Total'  => '$' . number_format((float) $records->whereNull('deleted_at')->sum('total'), 2, ',', '.'),
                            ],
                            'headers' => ['ID', 'Fecha', 'Proveedor', 'Productos Incluidos', 'Total ($)', 'Estado'],
                            'rows'    => $records->map(fn ($r) => [
                                '#' . str_pad($r->id, 4, '0', STR_PAD_LEFT),
                                \Carbon\Carbon::parse($r->purchase_date)->format('d/m/Y'),
                                $r->supplier?->name ?? '-',
                                $r->purchaseDetails->map(fn ($d) => ($d->product?->name ?? '') . ' (x' . $d->quantity . ')')->implode(', '),
                                '$' . number_format((float) $r->total, 2, ',', '.'),
                                $r->trashed() ? 'Anulada' : 'Activa',
                            ])->toArray(),
                        ])->setPaper('a4', 'landscape');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'reporte-compras-N' . str_pad((string) \Illuminate\Support\Facades\Cache::get('sh_pdf_report_seq', 1), 4, '0', STR_PAD_LEFT) . '-' . now()->format('Ymd-His') . '.pdf'
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Anular')
                    ->modalHeading('Anular Compra (Irreversible)')
                    ->modalDescription('¿Estás seguro de anular esta compra? Esto descontará el stock ingresado y la compra quedará registrada como ANULADA.')
                    ->hidden(fn (Purchase $record): bool => $record->trashed()),
            ])
            ->bulkActions([]);
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