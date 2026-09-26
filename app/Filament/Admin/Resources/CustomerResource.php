<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CustomerResource\Pages;
use App\Models\Customer;
use App\Models\Province;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $navigationGroup = 'Tienda';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['city.province', 'user'])
            ->withCount('invoices')
            ->withSum('invoices', 'total')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos Personales y de Contacto')
                    ->description('Al registrar un cliente nuevo se creará automáticamente su cuenta de acceso web con su correo y su DNI como contraseña inicial.')
                    ->icon('heroicon-m-user')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('last_name')
                            ->label('Apellido')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('document_number')
                            ->label('DNI / Documento')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono / WhatsApp')
                            ->tel()
                            ->required()
                            ->maxLength(50),

                        Forms\Components\Select::make('city_id')
                            ->label('Ciudad / Localidad')
                            ->relationship('city', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre de la Ciudad')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('province_id')
                                    ->label('Provincia')
                                    ->relationship('province', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),

                        Forms\Components\TextInput::make('address')
                            ->label('Dirección / Domicilio')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Datos Fiscales para Facturación')
                    ->icon('heroicon-m-identification')
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
                            ->placeholder('Ej: 20-35111222-3')
                            ->required()
                            ->maxLength(20),
                    ])
                    ->columns(2),
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

                Tables\Columns\TextColumn::make('last_name')
                    ->label('Cliente')
                    ->formatStateUsing(fn (Customer $record): string => "{$record->last_name}, {$record->first_name}")
                    ->weight(FontWeight::Bold)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('document_number')
                    ->label('DNI')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tax_condition')
                    ->label('Condición IVA')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Responsable Inscripto' => 'info',
                        'Monotributista'        => 'warning',
                        'Exento'                => 'gray',
                        default                 => 'success',
                    }),

                Tables\Columns\TextColumn::make('city.name')
                    ->label('Ciudad')
                    ->description(fn (Customer $record): string => $record->city?->province?->name ?? ''),

                Tables\Columns\TextColumn::make('invoices_count')
                    ->label('Compras')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoices_sum_total')
                    ->label('Total Gastado ($)')
                    ->money('ARS')
                    ->default(0)
                    ->weight(FontWeight::Bold)
                    ->sortable(),

                Tables\Columns\TextColumn::make('nivel_vip')
                    ->label('Categoría')
                    ->badge()
                    ->getStateUsing(function (Customer $record): string {
                        $gasto = (float) ($record->invoices_sum_total ?? 0);
                        $compras = (int) ($record->invoices_count ?? 0);

                        if ($gasto >= 300000 || $compras >= 3) {
                            return 'Cliente VIP';
                        }
                        if ($compras >= 1) {
                            return 'Regular';
                        }
                        return 'Sin Compras';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Cliente VIP' => 'warning',
                        'Regular'     => 'success',
                        default       => 'gray',
                    }),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(fn (Customer $record): string => $record->trashed() ? 'De Baja' : 'Activo')
                    ->color(fn (string $state): string => $state === 'Activo' ? 'success' : 'danger'),
            ])
            ->filters([
                // 1. Estado (Activos / Dados de baja)
                Tables\Filters\TrashedFilter::make()
                    ->label('Estado del Cliente')
                    ->placeholder('Solo Clientes Activos')
                    ->trueLabel('Todos (Activos + Dados de Baja)')
                    ->falseLabel('Solo Clientes Dados de Baja'),

                // 2. Segmentación Comercial / VIP
                Tables\Filters\SelectFilter::make('segmento_comercial')
                    ->label('Segmentación Comercial / VIP')
                    ->options([
                        'vip'          => 'Clientes VIP (3+ compras o +$300.000)',
                        'con_compras'  => 'Clientes con Compras (Activos)',
                        'sin_compras'  => 'Registrados sin Compras (Inactivos)',
                        'con_reservas' => 'Clientes que alquilaron Sala',
                        'asistentes'   => 'Clientes que asistieron a Eventos',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                                'vip' => $query->where(function (Builder $q) {
                                $q->has('invoices', '>=', 3)
                                  ->orWhereHas('invoices', fn (Builder $sq) => $sq->where('total', '>=', 300000));
                            }),
                            'con_compras'  => $query->has('invoices'),
                            'sin_compras'  => $query->doesntHave('invoices'),
                            'con_reservas' => $query->has('reservations'),
                            'asistentes'   => $query->has('attendances'),
                            default        => $query,
                        };
                    }),

                // 3. Condición frente al IVA
                Tables\Filters\SelectFilter::make('tax_condition')
                    ->label('Condición IVA')
                    ->options([
                        'Consumidor Final'      => 'Consumidor Final',
                        'Responsable Inscripto' => 'Responsable Inscripto',
                        'Monotributista'        => 'Monotributista',
                        'Exento'                => 'Exento',
                    ]),

                // 4. Provincia
                Tables\Filters\SelectFilter::make('provincia')
                    ->label('Provincia')
                    ->options(fn (): array => Province::query()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn (Builder $q) => $q->whereHas('city', fn (Builder $sq) => $sq->where('province_id', $data['value']))
                        );
                    }),

                // 5. Ciudad
                Tables\Filters\SelectFilter::make('city_id')
                    ->label('Ciudad / Localidad')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),

                // 6. Rango de Monto Total Gastado ($)
                                // 6. Rango de Monto de Compra ($) — 100% Eloquent ORM
                Tables\Filters\Filter::make('rango_gasto')
                    ->form([
                        Forms\Components\TextInput::make('gasto_min')
                            ->label('Con Compra Mínima de ($)')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('gasto_max')
                            ->label('Con Compra Máxima de ($)')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['gasto_min'] ?? null),
                                fn (Builder $q) => $q->whereHas('invoices', fn (Builder $sq) => $sq->where('total', '>=', $data['gasto_min']))
                            )
                            ->when(
                                filled($data['gasto_max'] ?? null),
                                fn (Builder $q) => $q->whereHas('invoices', fn (Builder $sq) => $sq->where('total', '<=', $data['gasto_max']))
                            );
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

                        $vipCount = $records->filter(fn ($c) => ((float) ($c->invoices_sum_total ?? 0)) >= 300000 || ((int) ($c->invoices_count ?? 0)) >= 3)->count();

                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf-generic', [
                            'title'   => 'Padrón Comercial y Ranking de Clientes',
                            'summary' => [
                                'Clientes Filtrados'    => $records->count(),
                                'Clientes VIP'          => $vipCount,
                                'Compras Acumuladas'    => $records->sum('invoices_count'),
                                'Facturación Acumulada' => '$' . number_format((float) $records->sum('invoices_sum_total'), 2, ',', '.'),
                            ],
                            'headers' => ['ID', 'Cliente', 'DNI / CUIT', 'Condición IVA', 'Ciudad / Provincia', 'Compras', 'Total Gastado ($)', 'Categoría'],
                            'rows'    => $records->map(function ($r) {
                                $gasto = (float) ($r->invoices_sum_total ?? 0);
                                $compras = (int) ($r->invoices_count ?? 0);
                                $cat = ($gasto >= 300000 || $compras >= 3) ? 'Cliente VIP' : ($compras >= 1 ? 'Regular' : 'Sin Compras');

                                return [
                                    '#' . str_pad($r->id, 4, '0', STR_PAD_LEFT),
                                    "{$r->last_name}, {$r->first_name}",
                                    "{$r->document_number} ({$r->tax_id})",
                                    $r->tax_condition,
                                    ($r->city?->name ?? '-') . ($r->city?->province ? " ({$r->city->province->name})" : ''),
                                    $compras,
                                    '$' . number_format($gasto, 2, ',', '.'),
                                    $cat,
                                ];
                            })->toArray(),
                        ])->setPaper('a4', 'landscape');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'reporte-clientes-N' . str_pad((string) \Illuminate\Support\Facades\Cache::get('sh_pdf_report_seq', 1), 4, '0', STR_PAD_LEFT) . '-' . now()->format('Ymd-His') . '.pdf'
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn (Customer $record): bool => $record->trashed()),

                Tables\Actions\DeleteAction::make()
                    ->label('Dar de baja')
                    ->modalHeading('Dar de baja Cliente')
                    ->modalDescription('¿Estás seguro de dar de baja este cliente? También se suspenderá su cuenta de acceso web, manteniendo intacto su historial de facturas.')
                    ->hidden(fn (Customer $record): bool => $record->trashed()),

                Tables\Actions\RestoreAction::make()
                    ->label('Reactivar')
                    ->visible(fn (Customer $record): bool => $record->trashed()),
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
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}