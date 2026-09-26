<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvoiceResource\Pages;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Cache;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationGroup = 'Tienda';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationLabel = 'Facturas / Ventas';
    protected static ?string $modelLabel = 'Venta / Factura';
    protected static ?string $pluralModelLabel = 'Facturas y Ventas';

    public static function calculateTotal(Forms\Get $get, Forms\Set $set): void
    {
        $items = $get('invoiceDetails') ?? $get('../../invoiceDetails') ?? [];
        $total = 0;

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $total += ($qty * $price);
        }

        $formatted = number_format($total, 2, '.', '');
        $set('total', $formatted);
        $set('../../total', $formatted);
    }

    public static function nextReceiptNumber(): string
    {
        $maxNumber = (int) Invoice::withTrashed()->max('receipt_number');
        $countNumber = Invoice::withTrashed()->count();
        $next = max($maxNumber, $countNumber) + 1;

        return str_pad((string) $next, 8, '0', STR_PAD_LEFT);
    }

    public static function downloadTicketPdf(Invoice $invoice)
    {
        $invoice->loadMissing([
            'store',
            'customer',
            'employee',
            'invoiceDetails.product',
            'paymentDetails.paymentMethod',
        ]);

        // Alto dinámico según cantidad de renglones para formato ticket térmico 80mm (226.77pt)
        $itemCount = max(1, $invoice->invoiceDetails->count());
        $heightPt = 430 + ($itemCount * 38);

        $pdf = Pdf::loadView('reports.invoice-ticket-pdf', [
            'invoice' => $invoice,
        ])->setPaper([0, 0, 226.77, $heightPt], 'portrait');

        $filename = "ticket-FC-{$invoice->invoice_type}-{$invoice->point_of_sale}-{$invoice->receipt_number}.pdf";

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename
        );
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['customer', 'employee', 'invoiceDetails.product', 'paymentDetails.paymentMethod'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(12)
                    ->schema([
                        // =========================================================
                        // COLUMNA IZQUIERDA (7/12): PRODUCTOS A VENDER Y TOTAL
                        // =========================================================
                        Forms\Components\Group::make()
                            ->columnSpan(['default' => 12, 'lg' => 7])
                            ->schema([
                                Forms\Components\Section::make('Detalle de Productos a Vender')
                                    ->description('Seleccioná los productos a la izquierda; el precio y el total se calculan automáticamente.')
                                    ->icon('heroicon-m-shopping-bag')
                                    ->schema([
                                        Forms\Components\Repeater::make('invoiceDetails')
                                            ->relationship()
                                            ->label('Productos de la Venta')
                                            ->live()
                                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => self::calculateTotal($get, $set))
                                            ->deleteAction(
                                                fn (Forms\Components\Actions\Action $action) => $action->after(
                                                    fn (Forms\Get $get, Forms\Set $set) => self::calculateTotal($get, $set)
                                                )
                                            )
                                            ->schema([
                                                Forms\Components\Select::make('product_id')
                                                    ->label('Producto')
                                                    ->relationship('product', 'name', fn (Builder $query) => $query->where('stock', '>', 0))
                                                    ->getOptionLabelFromRecordUsing(
                                                        fn (Product $record) => "{$record->name} (Stock: {$record->stock}) — $" . number_format((float) $record->price, 2, ',', '.')
                                                    )
                                                    ->searchable(['name'])
                                                    ->preload()
                                                    ->required()
                                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                        $product = Product::find($state);
                                                        $unitPrice = (float) ($product?->price ?? 0);
                                                        $qty = (float) ($get('quantity') ?? 1);
                                                        $set('unit_price', number_format($unitPrice, 2, '.', ''));
                                                        $set('subtotal', number_format($unitPrice * $qty, 2, '.', ''));
                                                        self::calculateTotal($get, $set);
                                                    })
                                                    ->columnSpan(12),

                                                Forms\Components\TextInput::make('quantity')
                                                    ->label('Cantidad')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(1)
                                                    ->maxValue(fn (Forms\Get $get): int => (int) (Product::find($get('product_id'))?->stock ?? 9999))
                                                    ->validationMessages([
                                                        'max' => 'Supera el stock disponible (:max u.).',
                                                    ])
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                        $qty = max(1, (float) ($state ?? 1));
                                                        $unitPrice = (float) ($get('unit_price') ?? 0);
                                                        $set('subtotal', number_format($qty * $unitPrice, 2, '.', ''));
                                                        self::calculateTotal($get, $set);
                                                    })
                                                    ->columnSpan(4),

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
                                            ->addActionLabel('+ Agregar otro producto'),

                                        Forms\Components\TextInput::make('total')
                                            ->label('TOTAL A COBRAR ($)')
                                            ->numeric()
                                            ->prefix('$')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->default('0.00')
                                            ->extraInputAttributes([
                                                'class' => 'text-xl font-bold',
                                            ]),
                                    ]),
                            ]),

                        // =========================================================
                        // COLUMNA DERECHA (5/12): CABECERA FISCAL Y MEDIO DE PAGO
                        // =========================================================
                        Forms\Components\Group::make()
                            ->columnSpan(['default' => 12, 'lg' => 5])
                            ->schema([
                                Forms\Components\Section::make('Cabecera del Comprobante Fiscal')
                                    ->icon('heroicon-m-document-text')
                                    ->schema([
                                        Forms\Components\Select::make('customer_id')
                                            ->label('Cliente')
                                            ->relationship('customer', 'last_name')
                                            ->getOptionLabelFromRecordUsing(
                                                fn ($record) => "{$record->last_name}, {$record->first_name} ({$record->tax_condition})"
                                            )
                                            ->searchable(['first_name', 'last_name', 'document_number', 'tax_id'])
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                $customer = Customer::find($state);
                                                if ($customer) {
                                                    $set('invoice_type', $customer->tax_condition === 'Responsable Inscripto' ? 'A' : 'B');
                                                }
                                            })
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('first_name')
                                                    ->label('Nombre')
                                                    ->required(),
                                                Forms\Components\TextInput::make('last_name')
                                                    ->label('Apellido')
                                                    ->required(),
                                                Forms\Components\TextInput::make('document_number')
                                                    ->label('DNI')
                                                    ->required()
                                                    ->unique('customers', 'document_number'),
                                                Forms\Components\TextInput::make('tax_id')
                                                    ->label('CUIT / CUIL (Opcional)'),
                                                Forms\Components\Select::make('tax_condition')
                                                    ->label('Condición frente al IVA')
                                                    ->options([
                                                        'Consumidor Final'      => 'Consumidor Final',
                                                        'Monotributista'        => 'Monotributista',
                                                        'Responsable Inscripto' => 'Responsable Inscripto',
                                                        'Exento'                => 'Exento',
                                                    ])
                                                    ->default('Consumidor Final')
                                                    ->required(),
                                                Forms\Components\TextInput::make('email')
                                                    ->label('Correo Electrónico')
                                                    ->email()
                                                    ->required()
                                                    ->unique('customers', 'email'),
                                            ])
                                            ->columnSpanFull(),

                                        Forms\Components\Select::make('employee_id')
                                            ->label('Vendedor / Cajero')
                                            ->relationship('employee', 'last_name')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->last_name}, {$record->first_name}")
                                            ->searchable(['first_name', 'last_name'])
                                            ->preload()
                                            ->default(fn () => Employee::where('user_id', auth()->id())->value('id') ?? Employee::query()->value('id'))
                                            ->required()
                                            ->columnSpanFull(),

                                        Forms\Components\Select::make('invoice_type')
                                            ->label('Tipo de Factura')
                                            ->options([
                                                'A' => 'Factura A (Resp. Inscripto)',
                                                'B' => 'Factura B (Cons. Final / Monotrib.)',
                                                'C' => 'Factura C',
                                            ])
                                            ->default('B')
                                            ->disabled()
                                            ->dehydrated()
                                            ->required(),

                                        Forms\Components\Select::make('store_id')
                                            ->label('Sucursal')
                                            ->relationship('store', 'company_name')
                                            ->default(1)
                                            ->disabled()
                                            ->dehydrated()
                                            ->required(),

                                        Forms\Components\TextInput::make('point_of_sale')
                                            ->label('Punto de Venta')
                                            ->default('0001')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->required(),

                                        Forms\Components\TextInput::make('receipt_number')
                                            ->label('N° de Comprobante')
                                            ->default(fn () => self::nextReceiptNumber())
                                            ->readOnly()
                                            ->dehydrated()
                                            ->required(),

                                        Forms\Components\DatePicker::make('issue_date')
                                            ->label('Fecha de Emisión')
                                            ->default(now())
                                            ->readOnly()
                                            ->dehydrated()
                                            ->required(),

                                        Forms\Components\DatePicker::make('due_date')
                                            ->label('Fecha de Vencimiento')
                                            ->default(now()->addDays(10))
                                            ->readOnly()
                                            ->dehydrated()
                                            ->required(),
                                    ])
                                    ->columns(2),

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
                                                    ->default(fn () => PaymentMethod::query()->value('id'))
                                                    ->preload()
                                                    ->required(),
                                            ])
                                            ->defaultItems(1)
                                            ->addActionLabel('+ Agregar otro medio de pago'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('comprobante')
                    ->label('Comprobante')
                    ->getStateUsing(fn (Invoice $record): string => "FC {$record->invoice_type} {$record->point_of_sale}-{$record->receipt_number}")
                    ->weight(FontWeight::Bold)
                    ->searchable(['receipt_number'])
                    ->sortable(['receipt_number']),

                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.last_name')
                    ->label('Cliente')
                    ->formatStateUsing(fn (Invoice $record): string => $record->customer ? "{$record->customer->last_name}, {$record->customer->first_name}" : '-')
                    ->searchable(['first_name', 'last_name']),

                Tables\Columns\TextColumn::make('employee.last_name')
                    ->label('Vendedor')
                    ->formatStateUsing(fn (Invoice $record): string => $record->employee ? "{$record->employee->last_name}, {$record->employee->first_name}" : '-'),

                Tables\Columns\TextColumn::make('paymentDetails.paymentMethod.name')
                    ->label('Medio de Pago')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total ($)')
                    ->money('ARS')
                    ->weight(FontWeight::Bold)
                    ->sortable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(fn (Invoice $record): string => $record->trashed() ? 'Anulada' : 'Emitida')
                    ->color(fn (string $state): string => $state === 'Emitida' ? 'success' : 'danger'),
            ])
            ->defaultSort('issue_date', 'desc')
            ->filters([
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

                Tables\Filters\SelectFilter::make('payment_method_id')
                    ->label('Medio de Pago')
                    ->options(fn (): array => PaymentMethod::query()->pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn (Builder $q) => $q->whereHas('paymentDetails', fn (Builder $sq) => $sq->where('payment_method_id', $data['value']))
                        );
                    }),

                Tables\Filters\Filter::make('issue_date')
                    ->form([
                        Forms\Components\DatePicker::make('desde')->label('Fecha Desde'),
                        Forms\Components\DatePicker::make('hasta')->label('Fecha Hasta'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(filled($data['desde'] ?? null), fn (Builder $q) => $q->whereDate('issue_date', '>=', $data['desde']))
                            ->when(filled($data['hasta'] ?? null), fn (Builder $q) => $q->whereDate('issue_date', '<=', $data['hasta']));
                    }),

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
            ->filtersFormWidth(MaxWidth::FourExtraLarge)
            ->headerActions([
                Tables\Actions\Action::make('exportar_pdf')
                    ->label('Exportar PDF')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('primary')
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredSortedTableQuery()->get();

                        $pdf = Pdf::loadView('reports.pdf-generic', [
                            'title'   => 'Reporte Contable de Facturación y Ventas',
                            'summary' => [
                                'Comprobantes Listados' => $records->count(),
                                'Facturas Emitidas'     => $records->whereNull('deleted_at')->count(),
                                'Facturas Anuladas'     => $records->whereNotNull('deleted_at')->count(),
                                'Total Facturado Neto'  => '$' . number_format((float) $records->whereNull('deleted_at')->sum('total'), 2, ',', '.'),
                            ],
                            'headers' => ['Comprobante', 'Fecha', 'Cliente', 'Vendedor', 'Medio de Pago', 'Total ($)', 'Estado'],
                            'rows'    => $records->map(fn ($r) => [
                                "FC {$r->invoice_type} {$r->point_of_sale}-{$r->receipt_number}",
                                \Carbon\Carbon::parse($r->issue_date)->format('d/m/Y'),
                                $r->customer ? "{$r->customer->last_name}, {$r->customer->first_name}" : '-',
                                $r->employee ? "{$r->employee->last_name}, {$r->employee->first_name}" : '-',
                                $r->paymentDetails->map(fn ($p) => $p->paymentMethod?->name)->filter()->implode(', ') ?: '-',
                                '$' . number_format((float) $r->total, 2, ',', '.'),
                                filled($r->deleted_at) ? 'Anulada' : 'Emitida',
                            ])->toArray(),
                        ])->setPaper('a4', 'landscape');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'reporte-ventas-N' . str_pad((string) Cache::get('sh_pdf_report_seq', 1), 4, '0', STR_PAD_LEFT) . '-' . now()->format('Ymd-His') . '.pdf'
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('ticket_pdf')
                    ->label('Ticket')
                    ->icon('heroicon-m-printer')
                    ->color('success')
                    ->action(fn (Invoice $record) => self::downloadTicketPdf($record)),

                Tables\Actions\ViewAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->label('Anular')
                    ->modalHeading('Anular Factura (Irreversible)')
                    ->modalDescription('¿Estás seguro de anular esta factura? Se reintegrará el stock de los productos al depósito y el comprobante quedará registrado como ANULADO.')
                    ->hidden(fn (Invoice $record): bool => $record->trashed()),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view'   => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}