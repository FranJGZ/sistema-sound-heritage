<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    // =========================================================================
    // 1. CONFIGURACIÓN DEL MENÚ Y MODELO
    // Se agrupa en 'Tienda' con orden 3 para gestionar la cartera de compradores.
    // =========================================================================
    protected static ?string $model = Customer::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Tienda';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';

    // Se precargan conteos y sumas con withCount/withSum para evitar el problema N+1 (una consulta extra por fila al evaluar si es VIP).
    // Además se desactiva SoftDeletingScope para que los clientes dados de baja sigan visibles al auditar ventas históricas.
    public static function getEloquentQuery(): Builder
        {
            return parent::getEloquentQuery()
                ->with(['city.province', 'user'])
                ->withCount('invoices')
                ->withSum('invoices', 'total')
                ->withoutGlobalScopes([SoftDeletingScope::class])
                ->orderByRaw("CASE WHEN document_number = '0' THEN 0 ELSE 1 END"); // <- Siempre primero
        }

    // =========================================================================
    // 2. FORMULARIO DE CARGA Y EDICIÓN DE CLIENTE
    // =========================================================================
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos Personales y de Contacto')
                ->schema([
                    Forms\Components\TextInput::make('first_name')->label('Nombre')->required()->maxLength(100),
                    Forms\Components\TextInput::make('last_name')->label('Apellido')->required()->maxLength(100),
                    // Se usa ignoreRecord para que al editar otros datos del cliente el validador no rechace su propio DNI o email actual
                    Forms\Components\TextInput::make('document_number')->label('DNI / Documento')->required()->unique(ignoreRecord: true)->maxLength(20),
                    Forms\Components\TextInput::make('email')
                        ->label('Correo Electrónico')
                        ->email()
                        ->nullable()
                        ->unique(ignoreRecord: true)
                        ->maxLength(150),
                    Forms\Components\TextInput::make('phone')->label('Teléfono / WhatsApp')->tel()->required()->maxLength(30),

                    Forms\Components\Select::make('city_id')
                        ->label('Ciudad / Localidad')
                        ->relationship('city', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        // Permite registrar una localidad nueva en el momento sin perder los datos personales que ya se estaban cargando
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->label('Nombre de la Ciudad')->required()->maxLength(100),
                            Forms\Components\Select::make('province_id')->label('Provincia')->relationship('province', 'name')->searchable()->preload()->required(),
                        ]),

                    Forms\Components\TextInput::make('address')->label('Dirección / Domicilio')->required()->maxLength(200)->columnSpanFull(),

                    Forms\Components\Placeholder::make('cuenta_usuario_activa')
                        ->label('Acceso a Tienda Online')
                        ->content(fn (?Customer $record) => $record?->user_id
                            ? '✓ Cuenta de tienda online activa (' . ($record->user?->email ?? $record->email) . ')'
                            : 'ℹ Se creará una cuenta de tienda online automáticamente al registrar un correo.')
                        ->columnSpanFull(),
                ])->columns(2),

            // Se separa en una sección fiscal porque la condición frente al IVA determina en el POS si corresponde Factura A o B
            Forms\Components\Section::make('Datos Fiscales para Facturación')
                ->schema([
                    Forms\Components\Select::make('tax_condition')
                        ->label('Condición frente al IVA')
                        ->options([
                            'Consumidor Final'      => 'Consumidor Final',
                            'Responsable Inscripto' => 'Responsable Inscripto',
                            'Monotributista'        => 'Monotributista',
                            'Exento'                => 'Exento',
                        ])
                        ->default('Consumidor Final')
                        ->required(),

                    Forms\Components\TextInput::make('tax_id')
                        ->label('CUIT / CUIL')
                        ->placeholder('Ej: 20-12345678-9')
                        ->required()
                        ->maxLength(20),
                ])->columns(2),
        ]);
    }

    /**
     * Esquema para el modal de alta rápida de clientes desde el selector en Facturas o Reservas.
     */
    public static function getQuickCreateFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('first_name')->label('Nombre')->required()->maxLength(100),
                Forms\Components\TextInput::make('last_name')->label('Apellido')->required()->maxLength(100),
                Forms\Components\TextInput::make('document_number')->label('DNI / Documento')->required()->maxLength(20),
                Forms\Components\TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->email()
                    ->nullable()
                    ->maxLength(150),
                Forms\Components\TextInput::make('phone')->label('Teléfono / WhatsApp')->tel()->required()->maxLength(30),
                Forms\Components\Select::make('city_id')
                    ->label('Ciudad')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('address')->label('Dirección')->required()->maxLength(200)->columnSpanFull(),
                Forms\Components\Select::make('tax_condition')
                    ->label('Condición IVA')
                    ->options([
                        'Consumidor Final'      => 'Consumidor Final',
                        'Responsable Inscripto' => 'Responsable Inscripto',
                        'Monotributista'        => 'Monotributista',
                        'Exento'                => 'Exento',
                    ])
                    ->default('Consumidor Final')
                    ->required(),
                Forms\Components\TextInput::make('tax_id')->label('CUIT / CUIL')->placeholder('Ej: 20-12345678-9')->required()->maxLength(20),
            ]),
        ];
    }

    // =========================================================================
    // 3. TABLA PRINCIPAL DE CARTERA DE CLIENTES
    // =========================================================================
    public static function table(Table $table): Table
    {
        return $table
            // -----------------------------------------------------------------
            // A) COLUMNAS VISIBLES EN EL LISTADO
            // -----------------------------------------------------------------
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),

                Tables\Columns\TextColumn::make('last_name')
                    ->label('Cliente')
                    ->formatStateUsing(fn (Customer $record) => "{$record->last_name}, {$record->first_name}")
                    ->description(fn (Customer $record) => $record->email ?: 'Sin correo')
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('document_number')
                    ->label('DNI / CUIT')
                    ->description(fn (Customer $record) => "CUIT: {$record->tax_id}")
                    ->searchable(['document_number', 'tax_id']),

                Tables\Columns\TextColumn::make('tax_condition')
                    ->label('Condición IVA')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Responsable Inscripto' => 'warning',
                        'Monotributista'        => 'info',
                        'Consumidor Final'      => 'gray',
                        default                 => 'success',
                    }),

                Tables\Columns\TextColumn::make('city.name')
                    ->label('Ciudad')
                    ->description(fn (Customer $record) => $record->city?->province?->name ?? '')
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoices_count')
                    ->label('Compras')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoices_sum_total')
                    ->label('Total Gastado')
                    ->money('ARS')
                    ->default(0)
                    ->weight('bold')
                    ->sortable(),

                // Se calcula en tiempo real en lugar de guardarse en la BD para que la categoría se actualice sola tras cada compra o anulación
                Tables\Columns\TextColumn::make('nivel_vip')
                    ->label('Categoría')
                    ->state(function (Customer $record): string {
                        // Si es el mostrador, nunca es VIP aunque tenga 1000 ventas
                        if ($record->document_number === '0') {
                            return 'Mostrador';
                        }

                        $gastado = (float) ($record->invoices_sum_total ?? 0);
                        $compras = (int) ($record->invoices_count ?? 0);

                        if ($gastado >= 300000 || $compras >= 3) {
                            return 'Cliente VIP';
                        }
                        if ($compras >= 1) {
                            return 'Regular';
                        }
                        return 'Sin Compras';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Cliente VIP' => 'warning',
                        'Regular'     => 'info',
                        'Mostrador'   => 'gray',
                        default       => 'gray',
                    }),

                // Evalúa trashed() (deleted_at) para distinguir clientes activos de cuentas dadas de baja
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->state(fn (Customer $record): string => $record->trashed() ? 'De Baja' : 'Activo')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Activo' ? 'success' : 'danger'),
            ])
            ->defaultSort('id', 'desc')

            // -----------------------------------------------------------------
            // B) FILTROS DE AUDITORÍA Y BÚSQUEDA 
            // Estos mismos filtros afectan tanto a la tabla en pantalla como al exportar el PDF.
            // -----------------------------------------------------------------
            ->filters([
                // Por defecto muestra solo clientes activos para no mezclar cuentas dadas de baja en la operatoria diaria
                Tables\Filters\TrashedFilter::make()
                    ->label('Estado del Cliente')
                    ->placeholder('Solo Clientes Activos')
                    ->trueLabel('Todos (Activos + De Baja)')
                    ->falseLabel('Solo Dados de Baja'),

                
                Tables\Filters\SelectFilter::make('segmento_comercial')
                    ->label('Segmento Comercial')
                    ->options([
                        'vip'          => 'Clientes VIP (+$300k o 3+ compras)',
                        'con_compras'  => 'Clientes Activos (Con compras)',
                        'sin_compras'  => 'Prospectos (Sin compras aún)',
                        'con_reservas' => 'Alquilaron Sala de Ensayo',
                        'asistentes'   => 'Asistieron a Eventos',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $valor = $data['value'] ?? null;

                        if ($valor === 'vip') {
                            $query->where('document_number', '!=', '0') // <- Excluimos al mostrador
                                ->where(function (Builder $subQuery) {
                                    $subQuery->has('invoices', '>=', 3)
                                        ->orWhereHas('invoices', fn (Builder $invoiceQuery) => $invoiceQuery->where('total', '>=', 300000));
                                });
                        } elseif ($valor === 'con_compras') {
                            $query->has('invoices');
                        } elseif ($valor === 'sin_compras') {
                            $query->doesntHave('invoices');
                        } elseif ($valor === 'con_reservas') {
                            $query->has('reservations');
                        } elseif ($valor === 'asistentes') {
                            $query->has('attendances');
                        }
                    }),

                Tables\Filters\SelectFilter::make('tax_condition')
                    ->label('Condición IVA')
                    ->options([
                        'Consumidor Final'      => 'Consumidor Final',
                        'Responsable Inscripto' => 'Responsable Inscripto',
                        'Monotributista'        => 'Monotributista',
                        'Exento'                => 'Exento',
                    ]),

                // Usa relación anidada con punto (cliente -> ciudad -> provincia) para filtrar por provincia sin escribir subconsultas manuales
                Tables\Filters\SelectFilter::make('provincia')
                    ->label('Provincia')
                    ->relationship('city.province', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('city_id')
                    ->label('Ciudad / Localidad')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),

                // Filtra mediante la relación 'invoices' con whereHas para buscar clientes según el monto de sus compras
                Tables\Filters\Filter::make('rango_gasto')
                    ->form([
                        Forms\Components\TextInput::make('gasto_min')->label('Compra Mín. ($)')->numeric()->prefix('$'),
                        Forms\Components\TextInput::make('gasto_max')->label('Compra Máx. ($)')->numeric()->prefix('$'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data) {
                        if ($data['gasto_min']) {
                            $query->whereHas('invoices', fn (Builder $subQuery) => $subQuery->where('total', '>=', (float) $data['gasto_min']));
                        }
                        if ($data['gasto_max']) {
                            $query->whereHas('invoices', fn (Builder $subQuery) => $subQuery->where('total', '<=', (float) $data['gasto_max']));
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
                        $customers = $livewire->getFilteredSortedTableQuery()
                            ->with(['city.province'])
                            ->withCount('invoices')
                            ->withSum('invoices', 'total')
                            ->get();

                        $totalFacturado = (float) $customers->sum('invoices_sum_total');
                        $totalCompras   = (int) $customers->sum('invoices_count');
                        $cantidadVip    = 0;

                        $rows = [];
                        foreach ($customers as $customer) {
                            $gastado = (float) ($customer->invoices_sum_total ?? 0);
                            $compras = (int) ($customer->invoices_count ?? 0);

                            if ($customer->document_number === '0') {
                                $categoria = 'Mostrador';
                            } elseif ($gastado >= 300000 || $compras >= 3) {
                                $categoria = 'Cliente VIP';
                                $cantidadVip++;
                            } elseif ($compras >= 1) {
                                $categoria = 'Regular';
                            } else {
                                $categoria = 'Sin Compras';
                            }

                            $rows[] = [
                                $customer->id,
                                "{$customer->last_name}, {$customer->first_name}",
                                $customer->document_number,
                                $customer->tax_condition,
                                $customer->city ? "{$customer->city->name} ({$customer->city->province?->name})" : '-',
                                $compras,
                                '$' . number_format($gastado, 2, ',', '.'),
                                $categoria,
                            ];
                        }

                        $pdf = Pdf::loadView('reports.pdf-generic', [
                            'title'             => 'Cartera y Ranking Comercial de Clientes',
                            'filtros_aplicados' => collect($livewire->getTable()->getFilterIndicators())
                                ->map(fn ($indicator) => strip_tags((string) $indicator->getLabel()))
                                ->implode(' | ') ?: 'Ninguno (Todos los registros)',
                            'summary' => [
                                'Clientes Filtrados'    => $customers->count(),
                                'Clientes VIP'          => $cantidadVip,
                                'Compras Acumuladas'    => $totalCompras,
                                'Facturación Acumulada' => '$' . number_format($totalFacturado, 2, ',', '.'),
                            ],
                            'headers' => ['ID', 'Cliente', 'DNI / CUIT', 'Condición IVA', 'Ciudad', 'Compras', 'Total Gastado ($)', 'Categoría'],
                            'rows'    => $rows,
                        ])->setPaper('a4', 'landscape');

                        // Se usa streamDownload porque Livewire funciona por AJAX y no permite descargas directas tradicionales
                        return response()->streamDownload(fn () => print($pdf->output()), 'reporte-clientes-' . date('Ymd-His') . '.pdf');
                    }),
            ])

            // -----------------------------------------------------------------
            // D) ACCIONES POR CADA FILA (EDITAR, DAR DE BAJA Y REACTIVAR)
            // -----------------------------------------------------------------
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn (Customer $record) => $record->trashed()),

                // Se aplica baja lógica (SoftDelete) porque borrar físicamente al cliente rompería la integridad referencial de sus facturas
                Tables\Actions\DeleteAction::make()
                    ->label('Dar de baja')
                    ->modalHeading('Dar de baja cliente')
                    ->modalDescription('El cliente quedará inactivo pero se conservará todo su historial de facturas y reservas.')
                    ->hidden(fn (Customer $record) => $record->trashed()),

                Tables\Actions\RestoreAction::make()
                    ->label('Reactivar')
                    ->visible(fn (Customer $record) => $record->trashed()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}