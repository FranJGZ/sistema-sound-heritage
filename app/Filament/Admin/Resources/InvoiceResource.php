<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvoiceResource\Pages;
use App\Models\City;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\EmployeeCategory;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Store;
use App\Services\MercadoPagoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    // =========================================================================
    // 1. CONFIGURACIÓN DEL MENÚ Y MODELO
    // Se agrupa en 'Tienda' con orden 2 para que quede justo debajo de Productos.
    // =========================================================================
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationGroup = 'Tienda';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationLabel = 'Facturas / Ventas';
    protected static ?string $modelLabel = 'Venta / Factura';
    protected static ?string $pluralModelLabel = 'Facturas y Ventas';

    // =========================================================================
    // 2. FUNCIONES AUXILIARES (CAJERO EN SESIÓN, CÁLCULO EN VIVO Y REPORTES PDF)
    // =========================================================================

    // Obtiene el empleado vinculado a la cuenta logueada (Vendedor o Admin); si el Admin aún no tiene legajo en employees, lo vincula automáticamente
    public static function getCurrentEmployee(): ?Employee
    {
        $user = auth()->user();
        if (! $user) {
            return Employee::first();
        }

        $employee = Employee::where('user_id', $user->id)->first();
        if ($employee) {
            return $employee;
        }

        $customer = $user->customer;
        $parts    = explode(' ', trim($user->name), 2);

        return Employee::create([
            'user_id'              => $user->id,
            'document_number'      => $customer?->document_number ?: ('990000' . $user->id),
            'first_name'           => $customer?->first_name ?: ($parts[0] ?? $user->name),
            'last_name'            => $customer?->last_name ?: ($parts[1] ?? 'Administrador'),
            'address'              => $customer?->address ?: 'Sede Central',
            'phone'                => $customer?->phone ?: '-',
            'city_id'              => $customer?->city_id ?: (City::first()?->id ?? 1),
            'employee_category_id' => EmployeeCategory::first()?->id ?? 1,
        ]);
    }

    // Se usa '$get("invoiceDetails")' y '$get("../../invoiceDetails")' porque puede dispararse desde el Repeater padre o desde un input hijo
    public static function calculateTotal(Forms\Get $get, Forms\Set $set): void
    {
        $items = $get('invoiceDetails') ?? $get('../../invoiceDetails') ?? [];
        $total = 0;

        foreach ($items as $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $price    = (float) ($item['unit_price'] ?? 0);
            $total   += $quantity * $price;
        }

        $formattedTotal = number_format($total, 2, '.', '');
        $set('total', $formattedTotal);
        $set('../../total', $formattedTotal);
    }

    // El alto ($height) se calcula según la cantidad de renglones para simular un rollo térmico de 80mm (226.77pt) sin cortar páginas
    public static function downloadTicketPdf(Invoice $invoice)
    {
        $itemCount = max(1, $invoice->invoiceDetails->count());
        $height    = 430 + ($itemCount * 38);

        $pdf = Pdf::loadView('reports.invoice-ticket-pdf', [
            'invoice' => $invoice,
        ])->setPaper([0, 0, 226.77, $height], 'portrait');

        $filename = "ticket-FC-{$invoice->invoice_type}-{$invoice->point_of_sale}-{$invoice->receipt_number}.pdf";

        return response()->streamDownload(fn () => print($pdf->output()), $filename);
    }

    // Genera el reporte PDF general de facturación respetando los filtros activos en pantalla
    public static function exportPdf($livewire)
    {
        $invoices       = $livewire->getFilteredSortedTableQuery()->get();
        // Se separan las emitidas para que el total recaudado no sume dinero de facturas anuladas
        $activeInvoices = $invoices->whereNull('deleted_at');

        $rows = [];
        foreach ($invoices as $invoice) {
            $paymentMethods = $invoice->paymentDetails->pluck('paymentMethod.name')->implode(', ');

            $rows[] = [
                $invoice->invoice_type . ' ' . $invoice->point_of_sale . '-' . $invoice->receipt_number,
                $invoice->issue_date,
                $invoice->customer->last_name . ' ' . $invoice->customer->first_name,
                $invoice->employee->last_name . ' ' . $invoice->employee->first_name,
                $paymentMethods,
                '$' . number_format((float) $invoice->total, 2, ',', '.'),
                $invoice->deleted_at ? 'Anulada' : 'Emitida',
            ];
        }

        $pdf = Pdf::loadView('reports.pdf-generic', [
            'title'             => 'Reporte de Facturación y Ventas',
            'filtros_aplicados' => collect($livewire->getTable()->getFilterIndicators())
                ->map(fn ($indicator) => strip_tags((string) $indicator->getLabel()))
                ->implode(' | ') ?: 'Ninguno (Todos los registros)',
            'summary' => [
                'Comprobantes'    => $invoices->count(),
                'Emitidas'        => $activeInvoices->count(),
                'Anuladas'        => $invoices->whereNotNull('deleted_at')->count(),
                'Total Facturado' => '$' . number_format((float) $activeInvoices->sum('total'), 2, ',', '.'),
            ],
            'headers' => ['Comprobante', 'Fecha', 'Cliente', 'Vendedor', 'Medio de Pago', 'Total ($)', 'Estado'],
            'rows'    => $rows,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(fn () => print($pdf->output()), 'reporte-ventas-' . date('Ymd-His') . '.pdf');
    }

    // Se precargan relaciones para evitar consultas N+1 y se desactiva SoftDeletingScope para auditar facturas anuladas
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['customer', 'employee', 'paymentDetails.paymentMethod'])
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    // =========================================================================
    // 3. FORMULARIO DE VENTA (ESTILO POS A 2 COLUMNAS)
    // Se divide en 12 columnas (7 izquierda y 5 derecha) para ver productos y pago a la vez.
    // =========================================================================
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(12)->schema([

                // -----------------------------------------------------------------
                // COLUMNA IZQUIERDA (7 de 12): PRODUCTOS A VENDER Y TOTAL
                // -----------------------------------------------------------------
                Forms\Components\Group::make()
                    ->columnSpan(['default' => 12, 'lg' => 7])
                    ->schema([
                        Forms\Components\Section::make('Detalle de Productos a Vender')
                            ->description('Podés filtrar por categoría o buscar directamente el producto; el precio y el total se calculan solos.')
                            ->icon('heroicon-m-shopping-bag')
                            ->schema([
                                // Usa relationship() para guardar automáticamente cada renglón en la tabla intermedia 'invoice_details'
                                Forms\Components\Repeater::make('invoiceDetails')
                                    ->relationship()
                                    ->label('Productos de la Venta')
                                    ->live()
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotal($get, $set))
                                    // Necesario para que al quitar un renglón el total general no quede con el valor viejo
                                    ->deleteAction(fn ($action) => $action->after(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotal($get, $set)))
                                    ->schema([
                                        // Filtro auxiliar en memoria (dehydrated: false) para acotar el catálogo por rubro sin guardarse en la BD
                                        Forms\Components\Select::make('category_filter')
                                            ->label('Categoría / Rubro')
                                            ->options(ProductResource::getProductTypeOptions())
                                            ->placeholder('Seleccioná una categoría')
                                            ->required()
                                            ->live()
                                            ->dehydrated(false)
                                            ->afterStateUpdated(fn (Forms\Set $set) => $set('product_id', null))
                                            ->columnSpan(5),

                                        Forms\Components\Select::make('product_id')
                                            ->label('Producto')
                                            ->disabled(fn (Forms\Get $get): bool => blank($get('category_filter')))
                                            // Ordena de la A a la Z, filtra por stock > 0 e incluye el ID ya elegido para que no muestre el número de ID cuando el stock queda en 0
                                            ->relationship(
                                                'product',
                                                'name',
                                                function (Builder $query, Forms\Get $get) {
                                                    $selectedCategory = $get('category_filter');
                                                    $selectedProduct  = $get('product_id');

                                                    if ($selectedCategory) {
                                                        $query->where('type', $selectedCategory);
                                                    }

                                                    return $query
                                                        ->where(function (Builder $q) use ($selectedProduct) {
                                                            $q->where('stock', '>', 0)
                                                              ->orWhere('id', $selectedProduct);
                                                        })
                                                        ->orderBy('name');
                                                }
                                            )
                                            ->getOptionLabelFromRecordUsing(function (Product $record) {
                                                $hasOffer = ($record->discount_percentage && $record->discount_percentage > 0);
                                                $cashPrice = number_format($record->cash_price, 2, ',', '.');
                                                $listPrice = number_format($record->price, 2, ',', '.');
                                                if ($hasOffer) {
                                                    return "{$record->name} (Stock: {$record->stock}) — Efectivo: \${$cashPrice} [{$record->discount_percentage}% OFF Oferta] (Lista: \${$listPrice})";
                                                }
                                                return "{$record->name} (Stock: {$record->stock}) — Efectivo: \${$cashPrice} (Lista: \${$listPrice})";
                                            })
                                            ->searchable(['name'])
                                            ->preload()
                                            ->required()
                                            // Impide cargar el mismo producto en dos renglones distintos y burlar el tope de stock por fila
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                            ->live()
                                            // Al elegir el producto se calcula su precio aplicando oferta y descuento de contado si es efectivo
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                $product = Product::find($state);
                                                if (!$product) {
                                                    return;
                                                }
                                                $paymentDetails = $get('../../paymentDetails') ?? $get('../../../paymentDetails') ?? [];
                                                $isMp = false;
                                                foreach ($paymentDetails as $pd) {
                                                    $pmId = $pd['payment_method_id'] ?? null;
                                                    if ($pmId) {
                                                        $pmName = \App\Models\PaymentMethod::find($pmId)?->name;
                                                        if ($pmName && str_contains(strtolower($pmName), 'mercado pago')) {
                                                            $isMp = true;
                                                            break;
                                                        }
                                                    }
                                                }

                                                $unitPrice = $isMp ? (float) $product->offer_price : (float) $product->cash_price;
                                                $quantity = (float) ($get('quantity') ?? 1);

                                                $set('unit_price', number_format($unitPrice, 2, '.', ''));
                                                $set('subtotal', number_format($unitPrice * $quantity, 2, '.', ''));
                                                self::calculateTotal($get, $set);
                                            })
                                            ->columnSpan(7),

                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Cantidad')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            // Bloquea la carga si el vendedor intenta vender más unidades de las que hay en depósito
                                            ->maxValue(fn (Forms\Get $get) => Product::find($get('product_id'))?->stock ?? 9999)
                                            ->validationMessages(['max' => 'Supera el stock disponible (:max u.).'])
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                $quantity = max(1, (float) ($state ?? 1));
                                                $price    = (float) ($get('unit_price') ?? 0);

                                                $set('subtotal', number_format($quantity * $price, 2, '.', ''));
                                                self::calculateTotal($get, $set);
                                            })
                                            ->columnSpan(4),

                                        // readOnly + dehydrated: impide alterar precios a mano, pero obliga a Filament a enviar el valor a la BD
                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('Precio Unitario ($)')
                                            ->numeric()
                                            ->prefix('$')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->required()
                                            ->columnSpan(4),

                                        Forms\Components\TextInput::make('subtotal')
                                            ->label('Subtotal ($)')
                                            ->numeric()
                                            ->prefix('$')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->required()
                                            ->columnSpan(4),
                                    ])
                                    ->columns(12)
                                    ->defaultItems(1)
                                    ->minItems(1)
                                    ->addActionLabel('+ Agregar otro producto'),

                                Forms\Components\TextInput::make('total')
                                    ->label('TOTAL A COBRAR ($)')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default('0.00')
                                    ->extraInputAttributes(['class' => 'text-xl font-bold']),
                            ]),
                    ]),

                // -----------------------------------------------------------------
                // COLUMNA DERECHA (5 de 12): CLIENTE, DATOS FISCALES Y PAGO
                // -----------------------------------------------------------------
                Forms\Components\Group::make()
                    ->columnSpan(['default' => 12, 'lg' => 5])
                    ->schema([
                        Forms\Components\Section::make('Cabecera del Comprobante Fiscal')
                            ->icon('heroicon-m-document-text')
                            ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship(
                                'customer',
                                'last_name',
                                // Esto hace que el cliente de mostrador (DNI 0) aparezca SIEMPRE PRIMERO arriba de todo en el desplegable
                                modifyQueryUsing: fn (Builder $query) => $query->orderByRaw("CASE WHEN document_number = '0' THEN 0 ELSE 1 END")->orderBy('last_name')
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->document_number === '0' 
                                ? "★ {$record->last_name} ({$record->first_name})" 
                                : "{$record->last_name}, {$record->first_name} (DNI: {$record->document_number})")
                            ->searchable(['first_name', 'last_name', 'document_number', 'tax_id'])
                            ->preload()
                            ->required()
                            // Viene seleccionado por defecto en toda nueva venta
                            ->default(fn () => Customer::where('document_number', '0')->value('id'))
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $customer = Customer::find($state);
                                if ($customer) {
                                    $type = ($customer->tax_condition === 'Responsable Inscripto') ? 'A' : 'B';
                                    $set('invoice_type', $type);
                                    $set('invoice_type_label', $type === 'A' ? 'Factura A (Resp. Inscripto)' : 'Factura B (Cons. Final / Monotrib.)');
                                }
                            })
                            ->createOptionForm(CustomerResource::getQuickCreateFormSchema())
                            ->createOptionAction(
                                fn (Forms\Components\Actions\Action $action) => $action
                                    ->modalHeading('Registrar Nuevo Cliente')
                                    ->modalWidth('3xl')
                            )
                            ->columnSpanFull(),

                                // Carga automáticamente el cajero de la cuenta logueada (Vendedor o Admin) en gris y sin opción de cambiarlo
                                Forms\Components\Hidden::make('employee_id')
                                    ->default(fn () => self::getCurrentEmployee()?->id)
                                    ->required(),

                                Forms\Components\TextInput::make('employee_name')
                                    ->label('Vendedor / Cajero')
                                    ->default(function (): string {
                                        $employee = self::getCurrentEmployee();
                                        return $employee
                                            ? "{$employee->last_name}, {$employee->first_name}"
                                            : (auth()->user()?->name ?? 'Administrador');
                                    })
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpanFull(),

                                // Campo oculto que guarda el código real ('A' o 'B') en la BD + TextInput gris sin flecha desplegable
                                Forms\Components\Hidden::make('invoice_type')
                                    ->default('B')
                                    ->required(),

                                Forms\Components\TextInput::make('invoice_type_label')
                                    ->label('Tipo de Factura')
                                    ->default('Factura B (Cons. Final / Monotrib.)')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\Hidden::make('store_id')
                                    ->default(1)
                                    ->required(),

                                Forms\Components\TextInput::make('store_name')
                                    ->label('Sucursal')
                                    ->default(fn () => Store::find(1)?->company_name ?? 'Sound Heritage')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('point_of_sale')
                                    ->label('Punto de Venta')
                                    ->default('0001')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),

                                Forms\Components\TextInput::make('receipt_number')
                                    ->label('N° de Comprobante')
                                    ->default(fn () => Invoice::getNextReceiptNumber('0001'))
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),

                                Forms\Components\DatePicker::make('issue_date')
                                    ->label('Fecha de Emisión')
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),

                                Forms\Components\DatePicker::make('due_date')
                                    ->label('Fecha de Vencimiento')
                                    ->default(now()->addDays(10))
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),
                            ])
                            ->columns(2),

                        // Medio de Pago: Efectivo o Mercado Pago (el estado pendiente/abonado se infiere automáticamente)
                        Forms\Components\Section::make('Medio de Pago')
                            ->icon('heroicon-m-credit-card')
                            ->schema([
                                Forms\Components\Repeater::make('paymentDetails')
                                    ->relationship()
                                    ->label('Pagos Registrados')
                                    ->schema([
                                        Forms\Components\Select::make('payment_method_id')
                                            ->label('Método de Pago')
                                            ->relationship('paymentMethod', 'name')
                                            ->default(fn () => PaymentMethod::where('name', 'Efectivo')->value('id') ?? PaymentMethod::first()?->id)
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                $items = $get('../../invoiceDetails') ?? [];
                                                $isMp = false;
                                                if ($state) {
                                                    $pmName = PaymentMethod::find($state)?->name;
                                                    if ($pmName && str_contains(strtolower($pmName), 'mercado pago')) {
                                                        $isMp = true;
                                                    }
                                                }
                                                $updatedItems = [];
                                                $total = 0.0;
                                                foreach ($items as $k => $item) {
                                                    if (!empty($item['product_id'])) {
                                                        $prod = Product::find($item['product_id']);
                                                        if ($prod) {
                                                            $qty = (float) ($item['quantity'] ?? 1);
                                                            $uPrice = $isMp ? (float) $prod->offer_price : (float) $prod->cash_price;
                                                            $item['unit_price'] = number_format($uPrice, 2, '.', '');
                                                            $item['subtotal'] = number_format($uPrice * $qty, 2, '.', '');
                                                            $total += $uPrice * $qty;
                                                        }
                                                    }
                                                    $updatedItems[$k] = $item;
                                                }
                                                $set('../../invoiceDetails', $updatedItems);
                                                $set('../../total', number_format($total, 2, '.', ''));
                                            }),

                                        Forms\Components\TextInput::make('monto_recibido')
                                            ->label('Efectivo Recibido / Con cuánto abona')
                                            ->numeric()
                                            ->prefix('$')
                                            ->placeholder('Ej: 50000')
                                            ->live(debounce: 300)
                                            ->dehydrated(false)
                                            ->visible(function (Forms\Get $get) {
                                                $pmId = $get('payment_method_id');
                                                $pmName = $pmId ? PaymentMethod::find($pmId)?->name : null;
                                                return $pmName && str_contains(strtolower($pmName), 'efectivo');
                                            })
                                            ->helperText(function (Forms\Get $get) {
                                                $recibido = (float) $get('monto_recibido');
                                                $total = (float) $get('../../total');
                                                if ($recibido > 0) {
                                                    $cambio = $recibido - $total;
                                                    if ($cambio >= 0) {
                                                        return '💵 Cambio / Vuelto a entregar al cliente: $ ' . number_format($cambio, 2, ',', '.');
                                                    } else {
                                                        return '⚠️ Faltan $ ' . number_format(abs($cambio), 2, ',', '.') . ' para cubrir el total.';
                                                    }
                                                }
                                                return 'Ingresá el importe entregado por el cliente para calcular automáticamente el vuelto.';
                                            }),
                                    ])
                                    ->defaultItems(1)
                                    ->addActionLabel('+ Agregar otro medio de pago'),
                            ]),
                    ]),
            ]),
        ]);
    }

    // =========================================================================
    // 4. VISTA DE REPORTE DEL COMPROBANTE A PANTALLA COMPLETA (INFOLIST)
    // =========================================================================
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Resumen del Comprobante Fiscal')
                ->icon('heroicon-m-document-text')
                ->schema([
                    Infolists\Components\TextEntry::make('receipt_number')
                        ->label('N° de Comprobante')
                        ->formatStateUsing(fn (Invoice $record) => "FC {$record->invoice_type} {$record->point_of_sale}-{$record->receipt_number}")
                        ->badge()
                        ->color('primary'),

                    Infolists\Components\TextEntry::make('issue_date')
                        ->label('Fecha de Emisión')
                        ->date('d/m/Y'),

                    Infolists\Components\TextEntry::make('estado')
                        ->label('Estado Contable')
                        ->state(fn (Invoice $record) => $record->trashed() ? 'Anulada' : 'Emitida')
                        ->badge()
                        ->color(fn (string $state) => $state === 'Emitida' ? 'success' : 'danger'),

                    Infolists\Components\TextEntry::make('total')
                        ->label('Total Facturado')
                        ->money('ARS')
                        ->weight('bold'),

                    Infolists\Components\TextEntry::make('customer.last_name')
                        ->label('Cliente')
                        ->formatStateUsing(fn (Invoice $record) => "{$record->customer->last_name}, {$record->customer->first_name} (DNI: {$record->customer->document_number})"),

                    Infolists\Components\TextEntry::make('customer.tax_condition')
                        ->label('Condición frente al IVA')
                        ->badge()
                        ->color('gray'),

                    Infolists\Components\TextEntry::make('employee.last_name')
                        ->label('Vendedor / Cajero')
                        ->formatStateUsing(fn (Invoice $record) => "{$record->employee->last_name}, {$record->employee->first_name}"),

                    Infolists\Components\TextEntry::make('paymentDetails.paymentMethod.name')
                        ->label('Medio de Pago')
                        ->badge()
                        ->color('info'),
                ])->columns(4),

            Infolists\Components\Section::make('Detalle de Productos Vendidos')
                ->icon('heroicon-m-shopping-bag')
                ->schema([
                    // Se usa hiddenLabel() en los hijos para que cada artículo ocupe 1 sola línea compacta aunque haya 50 productos
                    Infolists\Components\RepeatableEntry::make('invoiceDetails')
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

                            Infolists\Components\TextEntry::make('unit_price')
                                ->hiddenLabel()
                                ->prefix('Unit: ')
                                ->money('ARS')
                                ->columnSpan(2),

                            Infolists\Components\TextEntry::make('subtotal')
                                ->hiddenLabel()
                                ->prefix('Subtotal: ')
                                ->money('ARS')
                                ->weight('bold')
                                ->columnSpan(2),
                        ])
                        ->columns(12),
                ]),
        ]);
    }

    // =========================================================================
    // 5. TABLA PRINCIPAL DE COMPROBANTES EMITIDOS
    // =========================================================================
    public static function table(Table $table): Table
    {
        return $table
            ->recordAction(Tables\Actions\ViewAction::class)
            // -----------------------------------------------------------------
            // A) COLUMNAS VISIBLES EN EL LISTADO
            // -----------------------------------------------------------------
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')
                    ->label('Comprobante')
                    ->formatStateUsing(fn (Invoice $record) => "FC {$record->invoice_type} {$record->point_of_sale}-{$record->receipt_number}")
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.last_name')
                    ->label('Cliente')
                    ->formatStateUsing(fn (Invoice $record) => "{$record->customer->last_name}, {$record->customer->first_name}")
                    ->searchable(['first_name', 'last_name']),

                Tables\Columns\TextColumn::make('employee.last_name')
                    ->label('Vendedor')
                    ->formatStateUsing(fn (Invoice $record) => "{$record->employee->last_name}, {$record->employee->first_name}"),

                Tables\Columns\TextColumn::make('paymentDetails.paymentMethod.name')
                    ->label('Medio de Pago')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total ($)')
                    ->money('ARS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Cobro')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'completed' => 'success',
                        'refunded'  => 'danger',
                        'pending'   => 'warning',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state, Invoice $record): string => match ($state) {
                        'completed' => 'Abonado',
                        'refunded'  => 'Reembolsado' . ($record->mp_refund_id ? " (#{$record->mp_refund_id})" : ''),
                        'pending'   => 'Esperando QR',
                        default     => $state ?: 'Pendiente',
                    }),

                // Evalúa trashed() (deleted_at) para distinguir visualmente comprobantes válidos de anulados
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->state(fn (Invoice $record): string => $record->trashed() ? 'Anulada' : 'Emitida')
                    ->color(fn (string $state): string => $state === 'Emitida' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('3s')

            // -----------------------------------------------------------------
            // B) FILTROS DE AUDITORÍA Y BÚSQUEDA
            // Estos mismos filtros afectan tanto a la tabla en pantalla como al exportar el PDF.
            // -----------------------------------------------------------------
            ->filters([
                // Por defecto muestra solo las activas para no mezclar ventas anuladas en la operatoria diaria
                Tables\Filters\TrashedFilter::make()
                    ->label('Estado Contable')
                    ->placeholder('Solo Facturas Emitidas (Activas)')
                    ->trueLabel('Todas (Emitidas + Anuladas)')
                    ->falseLabel('Solo Facturas Anuladas'),

                Tables\Filters\SelectFilter::make('invoice_type')
                    ->label('Tipo de Comprobante')
                    ->options([
                        'A' => 'Factura A',
                        'B' => 'Factura B',
                        'C' => 'Factura C',
                    ]),

                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->last_name}, {$record->first_name}")
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Vendedor')
                    ->relationship('employee', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->last_name}, {$record->first_name}")
                    ->searchable()
                    ->preload(),

                // Usa relación anidada con punto para filtrar facturas según la tabla intermedia payment_details
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Medio de Pago')
                    ->relationship('paymentDetails.paymentMethod', 'name'),

                // Se usa Filter personalizado porque el rango requiere comparar '>=' y '<=' sobre la misma columna issue_date
                Tables\Filters\Filter::make('issue_date')
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Fecha Desde'),
                        Forms\Components\DatePicker::make('hasta')
                            ->label('Fecha Hasta'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data) {
                        if ($data['desde']) {
                            $query->whereDate('issue_date', '>=', $data['desde']);
                        }
                        if ($data['hasta']) {
                            $query->whereDate('issue_date', '<=', $data['hasta']);
                        }
                    }),

                // Permite acotar ventas entre un monto mínimo y un monto máximo en pesos
                Tables\Filters\Filter::make('rango_total')
                    ->form([
                        Forms\Components\TextInput::make('total_min')
                            ->label('Monto Mínimo ($)')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('total_max')
                            ->label('Monto Máximo ($)')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data) {
                        if ($data['total_min']) {
                            $query->where('total', '>=', $data['total_min']);
                        }
                        if ($data['total_max']) {
                            $query->where('total', '<=', $data['total_max']);
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
                    ->action(fn ($livewire) => self::exportPdf($livewire)),
            ])

            // -----------------------------------------------------------------
            // D) ACCIONES POR CADA FILA (TICKET, VER EN MODAL Y ANULAR)
            // -----------------------------------------------------------------
            ->actions([
                Tables\Actions\Action::make('cobrar_qr')
                    ->label('Cobrar QR')
                    ->icon('heroicon-m-qr-code')
                    ->color('warning')
                    ->visible(fn (Invoice $record): bool => ! $record->trashed() && empty($record->mp_order_id) && $record->payment_status !== 'completed')
                    ->requiresConfirmation()
                    ->modalHeading('Cobro Presencial con Código QR')
                    ->modalDescription(fn (Invoice $record) => "Se emitirá una orden de pago por $ " . number_format($record->total, 2, ',', '.') . " a la Caja 1. El cliente deberá escanear el cartel QR de la caja con su app de Mercado Pago.")
                    ->modalSubmitActionLabel('Enviar a Caja 1')
                    ->action(function (Invoice $record) {
                        $mp = app(MercadoPagoService::class);
                        $mp->cancelAnyActiveCajaOrder('CAJA1');
                        $order = $mp->createQrOrder(
                            amount: (float) $record->total,
                            description: "Factura FC {$record->invoice_type} {$record->point_of_sale}-{$record->receipt_number}",
                            externalReference: "INV-{$record->id}",
                            posId: 'CAJA1'
                        );

                        if ($order && isset($order['id'])) {
                            $record->update([
                                'mp_order_id'    => $order['id'],
                                'payment_status' => 'pending',
                            ]);

                            Notification::make()
                                ->title('Cobro enviado a Caja 1')
                                ->body('La orden está activa en la Caja 1. El cliente puede escanear el cartel QR para pagar.')
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('ver_cartel')
                                        ->label('Ver QR en Pantalla')
                                        ->url('/img/qr/caja1_qr.png', shouldOpenInNewTab: true),
                                ])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Error al conectar con Mercado Pago')
                                ->body('No se pudo enviar la orden a la Caja 1. Verificá la configuración.')
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('ver_qr_cartel')
                    ->label('Cartel QR')
                    ->icon('heroicon-m-qr-code')
                    ->color('warning')
                    ->visible(fn (Invoice $record) => $record->payment_status === 'pending' && !$record->trashed())
                    ->modalHeading(fn (Invoice $record) => "Cobro QR Mostrador · Caja 1 (FC {$record->invoice_type} {$record->point_of_sale}-{$record->receipt_number})")
                    ->modalDescription(fn (Invoice $record) => "Monto a cobrar: $ " . number_format((float) $record->total, 2, ',', '.') . " ARS")
                    ->modalContent(fn (Invoice $record) => view('filament.modals.qr-caja1', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar Ventana'),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Ver Detalle')
                        ->modalHeading('Detalle del Comprobante Fiscal')
                        ->modalWidth(MaxWidth::FourExtraLarge)
                        ->extraModalFooterActions([
                            Tables\Actions\Action::make('modal_ticket_pdf')
                                ->label('Imprimir Ticket Fiscal (PDF)')
                                ->icon('heroicon-m-printer')
                                ->color('success')
                                ->action(fn (Invoice $record) => self::downloadTicketPdf($record)),

                            Tables\Actions\DeleteAction::make('modal_anular')
                                ->label('Anular y Devolver Dinero')
                                ->modalHeading('Anular Factura y Devolución')
                                ->modalDescription(function (Invoice $record): string {
                                    $desc = '¿Estás seguro de anular esta factura? Se reintegrará el stock de los productos al depósito y el comprobante quedará registrado como ANULADO.';
                                    if (!empty($record->mp_payment_id) || !empty($record->mp_order_id)) {
                                        $desc .= ' ⚠️ Esta venta fue cobrada mediante Mercado Pago ($ ' . number_format((float)$record->total, 2, ',', '.') . '). Al anularla, el sistema ordenará la DEVOLUCIÓN AUTOMÁTICA DEL DINERO al comprador.';
                                    }
                                    return $desc;
                                })
                                ->hidden(fn (Invoice $record): bool => $record->trashed()),
                        ]),

                    Tables\Actions\Action::make('ticket_pdf')
                        ->label('Imprimir Ticket')
                        ->icon('heroicon-m-printer')
                        ->color('success')
                        ->action(fn (Invoice $record) => self::downloadTicketPdf($record)),

                    Tables\Actions\Action::make('cancelar_qr')
                        ->label('Liberar Caja')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->visible(fn (Invoice $record): bool => ! empty($record->mp_order_id) && $record->payment_status !== 'completed')
                        ->requiresConfirmation()
                        ->modalHeading('Cancelar cobro en Caja 1')
                        ->modalDescription('¿Deseás cancelar la orden activa en la Caja 1 para liberar el punto de venta?')
                        ->action(function (Invoice $record) {
                            $mp = app(MercadoPagoService::class);
                            $mp->cancelQrOrder($record->mp_order_id);
                            $record->update(['mp_order_id' => null]);

                            Notification::make()
                                ->title('Caja 1 liberada')
                                ->body('La orden fue cancelada de la caja.')
                                ->warning()
                                ->send();
                        }),

                    // Se usa DeleteAction como 'Anular' (baja lógica) y se oculta si ya fue anulada para no devolver stock dos veces
                    Tables\Actions\DeleteAction::make()
                        ->label('Anular')
                        ->modalHeading('Anular Factura y Devolución')
                        ->modalDescription(function (Invoice $record): string {
                            $desc = '¿Estás seguro de anular esta factura? Se reintegrará el stock de los productos al depósito y el comprobante quedará registrado como ANULADO.';
                            if (!empty($record->mp_payment_id) || !empty($record->mp_order_id)) {
                                $desc .= ' ⚠️ Esta venta fue cobrada mediante Mercado Pago ($ ' . number_format((float)$record->total, 2, ',', '.') . '). Al anularla, el sistema ordenará la DEVOLUCIÓN AUTOMÁTICA DEL DINERO al comprador.';
                            }
                            return $desc;
                        })
                        ->hidden(fn (Invoice $record): bool => $record->trashed()),
                ]),
            ]);
    }

    // =========================================================================
    // 6. RUTAS DE LAS PÁGINAS DEL RECURSO
    // Sin la ruta 'view', Filament abre automáticamente el reporte infolist() en un modal igual que en Compras.
    // =========================================================================
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
        ];
    }
}