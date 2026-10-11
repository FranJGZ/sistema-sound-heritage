<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseResource\Pages;
use App\Models\Product;
use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseResource extends Resource
{

    // =========================================================================
    // 1. CONFIGURACIÓN DEL MENÚ Y MODELO
    // Se agrupa en 'Tienda' con orden 4 para registrar el abastecimiento de mercadería.
    // =========================================================================
    protected static ?string $model = Purchase::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Tienda';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Compra a Proveedor';
    protected static ?string $pluralModelLabel = 'Compras a Proveedores';

    // Se desactiva SoftDeletingScope para que las órdenes de compra anuladas permanezcan visibles para auditoría de stock
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['supplier', 'purchaseDetails.product'])
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    // =========================================================================
    // 2. FUNCIÓN AUXILIAR DE CÁLCULO DE COSTO TOTAL
    // =========================================================================

    // Se usa '$get("purchaseDetails")' y '$get("../../purchaseDetails")' porque puede dispararse desde el Repeater padre o desde un input hijo
    public static function calculateTotal(Get $get, Set $set): void
    {
        $details = $get('purchaseDetails') ?? $get('../../purchaseDetails') ?? [];
        $total   = 0;

        foreach ($details as $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $price    = (float) ($item['price'] ?? 0);
            $total   += $quantity * $price;
        }

        $set('total', round($total, 2));
        $set('../../total', round($total, 2));
    }

    // =========================================================================
    // 3. FORMULARIO DE ORDEN DE COMPRA A PROVEEDOR
    // =========================================================================
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del Proveedor y Fecha')
                ->schema([
                    Forms\Components\Select::make('supplier_id')
                        ->label('Proveedor / Distribuidor')
                        ->relationship('supplier', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\DatePicker::make('purchase_date')
                        ->label('Fecha de Compra')
                        ->default(now())
                        ->required(),
                ])->columns(2),

            Forms\Components\Section::make('Detalle de Mercadería Ingresada')
                ->description('Al guardar la compra, las cantidades ingresadas se sumarán automáticamente al stock de cada producto.')
                ->schema([
                    // Usa relationship() para guardar automáticamente cada renglón en la tabla intermedia 'purchase_details'
                    Forms\Components\Repeater::make('purchaseDetails')
                        ->label('Ítems Comprados')
                        ->relationship()
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateTotal($get, $set))
                        // Necesario para que al quitar un renglón del remito el costo total general no quede con el importe viejo
                        ->deleteAction(
                            fn (Forms\Components\Actions\Action $action) => $action->after(fn (Get $get, Set $set) => self::calculateTotal($get, $set)),
                        )
                        ->schema([
                            // Filtro auxiliar en memoria (dehydrated: false) para acotar productos por categoría sin guardarse en la BD
                            Forms\Components\Select::make('category_filter')
                                ->label('Categoría / Rubro')
                                ->options(ProductResource::getProductTypeOptions())
                                ->placeholder('Todas las categorías')
                                ->live()
                                ->dehydrated(false)
                                ->afterStateUpdated(fn (Set $set) => $set('product_id', null))
                                ->columnSpan(4),

                            Forms\Components\Select::make('product_id')
                                ->label('Producto')
                                ->relationship(
                                    'product',
                                    'name',
                                    function (Builder $query, Get $get) {
                                        $selectedCategory = $get('category_filter');
                                        if ($selectedCategory) {
                                            $query->where('type', $selectedCategory);
                                        }
                                        return $query->orderBy('name');
                                    }
                                )
                                ->getOptionLabelFromRecordUsing(fn (Product $record) => "{$record->name} (Stock actual: {$record->stock})")
                                ->searchable()
                                ->preload()
                                ->required()
                                // Impide repetir el mismo artículo en dos renglones distintos dentro del mismo remito
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->columnSpan(4)
                                // Permite dar de alta un artículo nuevo que acaba de llegar del proveedor sin salir de la carga del remito
                                ->createOptionForm([
                                    Forms\Components\Section::make('Alta Rápida de Nuevo Producto en Catálogo')
                                        ->description('El stock inicial arranca en 0 y se sumará automáticamente la cantidad comprada al guardar esta orden.')
                                        ->schema([
                                            Forms\Components\TextInput::make('name')->label('Nombre del Producto / Modelo')->required()->maxLength(150)->columnSpanFull(),
                                            Forms\Components\Select::make('type')->label('Categoría de Producto')->options(ProductResource::getProductTypeOptions())->live()->required(),
                                            Forms\Components\TextInput::make('price')->label('Precio de Venta al Público ($)')->numeric()->prefix('$')->minValue(0)->required(),
                                            // El stock inicial se fija oculto en 0 porque al guardar la compra el modelo sumará automáticamente la cantidad recibida
                                            Forms\Components\Hidden::make('stock')->default(0),
                                        ])->columns(2),
                                    ...ProductResource::getProductSpecsFormSchema(),
                                ])
                                ->createOptionAction(
                                    fn (Forms\Components\Actions\Action $action) => $action
                                        ->modalHeading('Crear Nuevo Producto en Catálogo')
                                        ->modalWidth('4xl')
                                ),

                            Forms\Components\TextInput::make('quantity')
                                ->label('Cantidad Recibida')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateTotal($get, $set))
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('price')
                                ->label('Costo Unitario ($)')
                                ->numeric()
                                ->prefix('$')
                                ->minValue(0)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateTotal($get, $set))
                                ->columnSpan(2),
                        ])
                        ->columns(12)
                        ->defaultItems(1)
                        ->addActionLabel('Agregar otro artículo'),

                    // readOnly + dehydrated: impide alterar el costo total a mano, pero obliga a Filament a enviar el valor calculado a la BD
                    Forms\Components\TextInput::make('total')
                        ->label('Total de la Compra ($)')
                        ->numeric()
                        ->prefix('$')
                        ->readOnly()
                        ->dehydrated()
                        ->default(0)
                        ->required(),
                ]),
        ]);
    }

    // =========================================================================
    // 4. VISTA DE REPORTE DE LA ORDEN DE COMPRA (INFOLIST)
    // Muestra una ficha limpia de auditoría en lugar del formulario bloqueado en gris.
    // =========================================================================
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Resumen de la Orden de Compra')
                ->icon('heroicon-m-truck')
                ->schema([
                    Infolists\Components\TextEntry::make('id')
                        ->label('N° de Orden')
                        ->formatStateUsing(fn ($state) => '#' . str_pad((string) $state, 5, '0', STR_PAD_LEFT))
                        ->badge()
                        ->color('primary'),

                    Infolists\Components\TextEntry::make('purchase_date')
                        ->label('Fecha de Compra')
                        ->date('d/m/Y'),

                    Infolists\Components\TextEntry::make('supplier.name')
                        ->label('Proveedor / Distribuidor')
                        ->weight('bold'),

                    Infolists\Components\TextEntry::make('estado')
                        ->label('Estado de la Orden')
                        ->state(fn (Purchase $record) => $record->trashed() ? 'Anulada' : 'Activa')
                        ->badge()
                        ->color(fn (string $state) => $state === 'Activa' ? 'success' : 'danger'),

                    Infolists\Components\TextEntry::make('unidades_totales')
                        ->label('Total Unidades Ingresadas')
                        ->state(fn (Purchase $record) => $record->purchaseDetails->sum('quantity') . ' u.'),

                    Infolists\Components\TextEntry::make('total')
                        ->label('Costo Total Abonado')
                        ->money('ARS')
                        ->weight('bold'),
                ])->columns(3),

            Infolists\Components\Section::make('Detalle de Mercadería Recibida')
                ->icon('heroicon-m-archive-box-arrow-down')
                ->schema([
                    // Se usa hiddenLabel() para que un remito de muchos productos se vea en renglones compactos de una sola línea
                    Infolists\Components\RepeatableEntry::make('purchaseDetails')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('product.name')
                                ->hiddenLabel()
                                ->weight('bold')
                                ->columnSpan(6),

                            Infolists\Components\TextEntry::make('quantity')
                                ->hiddenLabel()
                                ->prefix('Cant: ')
                                ->suffix(' u.')
                                ->columnSpan(2),

                            Infolists\Components\TextEntry::make('price')
                                ->hiddenLabel()
                                ->prefix('Costo Unit: ')
                                ->money('ARS')
                                ->columnSpan(2),

                            Infolists\Components\TextEntry::make('subtotal_calculado')
                                ->hiddenLabel()
                                ->state(fn ($record) => 'Subtotal: $' . number_format($record->quantity * $record->price, 2, ',', '.'))
                                ->weight('bold')
                                ->columnSpan(2),
                        ])
                        ->columns(12),
                ]),
        ]);
    }

    // =========================================================================
    // 5. TABLA PRINCIPAL DE COMPRAS A PROVEEDORES
    // =========================================================================
    public static function table(Table $table): Table
    {
        return $table
            // -----------------------------------------------------------------
            // A) COLUMNAS VISIBLES EN EL LISTADO
            // -----------------------------------------------------------------
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('N° Orden')->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')->label('Proveedor')->searchable()->sortable(),

                // Se limita a mostrar 2 ítems por fila con opción desplegable para que un remito largo no deforme el alto de la tabla
                Tables\Columns\TextColumn::make('purchaseDetails.product.name')
                    ->label('Mercadería Ingresada')
                    ->badge()
                    ->color('gray')
                    ->limitList(2)
                    ->expandableLimitedList(),

                Tables\Columns\TextColumn::make('purchase_date')->label('Fecha')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('total')->label('Total Pagado')->money('ARS')->sortable()->weight('bold'),

                // Evalúa trashed() (deleted_at) para distinguir compras vigentes de órdenes anuladas
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(fn (Purchase $record) => $record->trashed() ? 'Anulada' : 'Activa')
                    ->color(fn (string $state) => $state === 'Activa' ? 'success' : 'danger'),
            ])
            ->defaultSort('id', 'desc')

            // -----------------------------------------------------------------
            // B) FILTROS DE AUDITORÍA Y BÚSQUEDA
            // Estos mismos filtros afectan tanto a la tabla en pantalla como al exportar el PDF.
            // -----------------------------------------------------------------
            ->filters([
                // Por defecto muestra solo las activas para no mezclar compras anuladas en el control habitual de gastos
                Tables\Filters\TrashedFilter::make()
                    ->label('Estado de Compra')
                    ->placeholder('Solo Compras Activas')
                    ->trueLabel('Todas (Activas + Anuladas)')
                    ->falseLabel('Solo Compras Anuladas'),

                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),

                // Usa relación anidada con punto para buscar en qué órdenes de compra ingresó un producto específico
                Tables\Filters\SelectFilter::make('producto_comprado')
                    ->label('Producto Incluido')
                    ->relationship('purchaseDetails.product', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('categoria_producto')
                    ->label('Categoría de Mercadería')
                    ->options(ProductResource::getProductTypeOptions())
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('purchaseDetails.product', function (Builder $subQuery) use ($data) {
                                $subQuery->where('type', $data['value']);
                            });
                        }
                    }),

                // Se usa Filter personalizado porque el rango requiere comparar '>=' y '<=' sobre la misma columna purchase_date
                Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        Forms\Components\DatePicker::make('desde')->label('Fecha Desde'),
                        Forms\Components\DatePicker::make('hasta')->label('Fecha Hasta'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data) {
                        if ($data['desde']) {
                            $query->whereDate('purchase_date', '>=', $data['desde']);
                        }
                        if ($data['hasta']) {
                            $query->whereDate('purchase_date', '<=', $data['hasta']);
                        }
                    }),

                Tables\Filters\Filter::make('rango_total')
                    ->form([
                        Forms\Components\TextInput::make('costo_min')->label('Total Mínimo ($)')->numeric()->prefix('$'),
                        Forms\Components\TextInput::make('costo_max')->label('Total Máximo ($)')->numeric()->prefix('$'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data) {
                        if ($data['costo_min']) {
                            $query->where('total', '>=', (float) $data['costo_min']);
                        }
                        if ($data['costo_max']) {
                            $query->where('total', '<=', (float) $data['costo_max']);
                        }
                    }),
            ])
            ->filtersFormColumns(3)
            ->filtersFormWidth(MaxWidth::FourExtraLarge)

            // -----------------------------------------------------------------
            // C) EXPORTACIÓN A PDF DEL LISTADO FILTRADO
            // -----------------------------------------------------------------
            ->headerActions([
                Tables\Actions\Action::make('exportar_pdf')
                    ->label('Exportar PDF')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('primary')
                    ->action(function ($livewire) {
                        // Se usa getFilteredSortedTableQuery() para que el PDF respete los filtros y el orden aplicados en pantalla
                        $purchases = $livewire->getFilteredSortedTableQuery()
                            ->with(['supplier', 'purchaseDetails.product'])
                            ->get();

                        // Se separan las activas para que la inversión total y las unidades ingresadas no sumen órdenes anuladas
                        $activePurchases = $purchases->whereNull('deleted_at');
                        $unidadesTotales = $activePurchases->sum(fn ($purchase) => $purchase->purchaseDetails->sum('quantity'));

                        $rows = [];
                        foreach ($purchases as $purchase) {
                            $items = $purchase->purchaseDetails
                                ->map(fn ($detail) => ($detail->product?->name ?? 'Ítem') . ' (x' . $detail->quantity . ')')
                                ->implode(', ');

                            $rows[] = [
                                $purchase->id,
                                $purchase->purchase_date,
                                $purchase->supplier?->name ?? '-',
                                $items ?: '-',
                                $purchase->purchaseDetails->sum('quantity') . ' u.',
                                '$' . number_format((float) $purchase->total, 2, ',', '.'),
                                $purchase->trashed() ? 'Anulada' : 'Activa',
                            ];
                        }

                        $pdf = Pdf::loadView('reports.pdf-generic', [
                            'title'             => 'Reporte de Compras y Abastecimiento a Proveedores',
                            'filtros_aplicados' => collect($livewire->getTable()->getFilterIndicators())
                                ->map(fn ($indicator) => strip_tags((string) $indicator->getLabel()))
                                ->implode(' | ') ?: 'Ninguno (Todos los registros)',
                            'summary' => [
                                'Órdenes Activas'           => $activePurchases->count(),
                                'Unidades Ingresadas'       => $unidadesTotales . ' u.',
                                'Órdenes Anuladas'          => $purchases->whereNotNull('deleted_at')->count(),
                                'Inversión Total (Activas)' => '$' . number_format((float) $activePurchases->sum('total'), 2, ',', '.'),
                            ],
                            'headers' => ['N° Orden', 'Fecha', 'Proveedor', 'Mercadería Ingresada', 'Unidades', 'Costo Total ($)', 'Estado'],
                            'rows'    => $rows,
                        ])->setPaper('a4', 'landscape');

                        // Se usa streamDownload porque Livewire funciona por AJAX y no permite descargas directas tradicionales
                        return response()->streamDownload(fn () => print($pdf->output()), 'reporte-compras-' . date('Ymd-His') . '.pdf');
                    }),
            ])

            // -----------------------------------------------------------------
            // D) ACCIONES POR CADA FILA (VER Y ANULAR)
            // -----------------------------------------------------------------
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Detalle de Orden de Compra')
                    ->modalWidth(MaxWidth::FourExtraLarge),

                // Se usa DeleteAction como 'Anular' (baja lógica) y se oculta si ya fue anulada para no descontar el stock dos veces
                Tables\Actions\DeleteAction::make()
                    ->label('Anular')
                    ->modalHeading('Anular Compra a Proveedor')
                    ->modalDescription('¿Estás seguro? Al anular esta compra, las unidades ingresadas se descontarán automáticamente del stock actual.')
                    ->hidden(fn (Purchase $record): bool => $record->trashed()),
            ]);
    }

    // =========================================================================
    // 6. RUTAS DE LAS PÁGINAS DEL RECURSO
    // No se incluye ruta 'edit' porque modificar una compra cerrada alteraría el stock ya ingresado;
    // si hubo un error de carga, corresponde anular la orden (que revierte el stock) y cargar una nueva.
    // =========================================================================
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
        ];
    }
}