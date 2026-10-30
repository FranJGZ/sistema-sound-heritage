<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AuditResource\Pages;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Models\Audit;

class AuditResource extends Resource
{
    protected static ?string $model = Audit::class;

    protected static ?string $navigationGroup = 'Administración y RRHH';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Auditorías';

    protected static ?string $modelLabel = 'Auditoría';

    protected static ?string $pluralModelLabel = 'Auditorías';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    // =========================================================================
    // LISTADO Y TABLA PRINCIPAL
    // =========================================================================
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordAction('view')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->default('Sistema / Web')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('event')
                    ->label('Acción')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created'  => 'success',
                        'updated'  => 'info',
                        'deleted'  => 'danger',
                        'restored' => 'warning',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'created'  => 'Creado',
                        'updated'  => 'Modificado',
                        'deleted'  => 'Anulado / Borrado',
                        'restored' => 'Restaurado',
                        default    => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->description(fn (Audit $record) => $record->created_at?->diffForHumans())
                    ->sortable(),

                Tables\Columns\TextColumn::make('old_values')
                    ->label('Valores Anteriores')
                    ->state(function (Audit $record) {
                        $formatted = static::formatValuesForDisplay($record->old_values);
                        if (empty($formatted)) {
                            return '—';
                        }
                        return collect($formatted)
                            ->map(fn ($val, $key) => "{$key}: {$val}")
                            ->take(3)
                            ->implode("\n");
                    })
                    ->wrap()
                    ->lineClamp(3)
                    ->color('gray'),

                Tables\Columns\TextColumn::make('new_values')
                    ->label('Valores Nuevos')
                    ->state(function (Audit $record) {
                        $formatted = static::formatValuesForDisplay($record->new_values);
                        if (empty($formatted)) {
                            return '—';
                        }
                        return collect($formatted)
                            ->map(fn ($val, $key) => "{$key}: {$val}")
                            ->take(3)
                            ->implode("\n");
                    })
                    ->wrap()
                    ->lineClamp(3),

                Tables\Columns\TextColumn::make('auditable_type')
                    ->label('Módulo / Registro')
                    ->state(function (Audit $record) {
                        $module = static::getModuleLabel($record->auditable_type);
                        $item   = static::getRecordLabel($record);
                        return "{$module} ({$item})";
                    })
                    ->badge()
                    ->color('gray')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label('Tipo de Acción')
                    ->options([
                        'created'  => 'Creado',
                        'updated'  => 'Modificado',
                        'deleted'  => 'Anulado / Borrado',
                    ]),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Usuario Responsable')
                    ->options(fn () => \App\Models\User::pluck('name', 'id')->toArray()),
            ])
            ->recordAction(Tables\Actions\ViewAction::class)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->modalHeading('Detalle del Registro de Auditoría')
                    ->modalWidth(MaxWidth::FourExtraLarge),
            ]);
    }

    // =========================================================================
    // FICHA DE DETALLE EN MODAL (PESTAÑAS INFO, VALORES ANTERIORES Y NUEVOS)
    // =========================================================================
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Tabs::make('Auditoría')
                ->tabs([
                    Infolists\Components\Tabs\Tab::make('Info')
                        ->label('Información General')
                        ->icon('heroicon-m-information-circle')
                        ->schema([
                            Infolists\Components\Grid::make(3)->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Usuario Responsable')
                                    ->default('Sistema / Web')
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Fecha y Hora')
                                    ->dateTime('d/m/Y H:i:s')
                                    ->hint(fn (Audit $record) => $record->created_at?->diffForHumans()),

                                Infolists\Components\TextEntry::make('event')
                                    ->label('Tipo de Evento')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'created'  => 'success',
                                        'updated'  => 'info',
                                        'deleted'  => 'danger',
                                        default    => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'created'  => 'Alta / Creación',
                                        'updated'  => 'Modificación',
                                        'deleted'  => 'Anulación / Baja',
                                        default    => ucfirst($state),
                                    }),

                                Infolists\Components\TextEntry::make('auditable_type')
                                    ->label('Módulo Auditado')
                                    ->formatStateUsing(fn (string $state, Audit $record) => static::getModuleLabel($state) . ' — ' . static::getRecordLabel($record))
                                    ->badge()
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('auditable_id')
                                    ->label('ID de Registro')
                                    ->prefix('#'),

                                Infolists\Components\TextEntry::make('ip_address')
                                    ->label('Dirección IP')
                                    ->default('N/D'),
                            ]),
                        ]),

                    Infolists\Components\Tabs\Tab::make('Diff')
                        ->label('Comparativa (Antes ➜ Después)')
                        ->icon('heroicon-m-arrows-right-left')
                        ->badge(fn (Audit $record) => count(static::getDiffDisplay($record)))
                        ->schema([
                            Infolists\Components\KeyValueEntry::make('diff_display')
                                ->label('')
                                ->keyLabel('Atributo Modificado')
                                ->valueLabel('Valor Previo (Antes)  ➜  Valor Nuevo (Después)')
                                ->getStateUsing(fn (Audit $record) => static::getDiffDisplay($record)),
                        ])
                        ->visible(fn (Audit $record) => $record->event === 'updated' && !empty(static::getDiffDisplay($record))),

                    Infolists\Components\Tabs\Tab::make('Old Values')
                        ->label('Valores Anteriores (Antes)')
                        ->icon('heroicon-m-arrow-left-circle')
                        ->badge(fn (Audit $record) => count($record->old_values ?? []))
                        ->schema([
                            Infolists\Components\KeyValueEntry::make('old_values_display')
                                ->label('')
                                ->keyLabel('Campo Modificado')
                                ->valueLabel('Valor Anterior')
                                ->getStateUsing(fn (Audit $record) => static::formatValuesForDisplay($record->old_values)),
                        ])
                        ->visible(fn (Audit $record) => !empty($record->old_values)),

                    Infolists\Components\Tabs\Tab::make('New Values')
                        ->label('Valores Nuevos (Después)')
                        ->icon('heroicon-m-arrow-right-circle')
                        ->badge(fn (Audit $record) => count($record->new_values ?? []))
                        ->schema([
                            Infolists\Components\KeyValueEntry::make('new_values_display')
                                ->label('')
                                ->keyLabel('Campo Registrado')
                                ->valueLabel('Valor Nuevo')
                                ->getStateUsing(fn (Audit $record) => static::formatValuesForDisplay($record->new_values)),
                        ])
                        ->visible(fn (Audit $record) => !empty($record->new_values)),
                ])
                ->columnSpanFull(),
        ]);
    }

    // =========================================================================
    // DICCIONARIOS Y FORMATEO DE ATRIBUTOS PARA HUMANOS
    // =========================================================================
    public static function getModuleLabel(?string $type): string
    {
        return match ($type) {
            'App\Models\Invoice'        => 'Ventas',
            'App\Models\InvoiceDetail'  => 'Detalle de Factura',
            'App\Models\PaymentDetail'  => 'Detalle de Pago',
            'App\Models\Customer'       => 'Clientes',
            'App\Models\Employee'       => 'Empleados',
            'App\Models\Event'          => 'Eventos',
            'App\Models\EventSponsor'   => 'Sponsors de Evento',
            'App\Models\User'           => 'Usuarios y Roles',
            'App\Models\Attendance'     => 'Asistencias',
            'App\Models\Sponsor'        => 'Sponsors',
            'App\Models\Product'        => 'Productos',
            'App\Models\Purchase'       => 'Compras',
            'App\Models\PurchaseDetail' => 'Detalle de Compra',
            'App\Models\Supplier'       => 'Proveedores',
            default                     => class_basename($type ?? 'Desconocido'),
        };
    }

    public static function getRecordLabel(Audit $record): string
    {
        $all = array_merge($record->old_values ?? [], $record->new_values ?? []);

        if ($record->auditable_type === 'App\Models\Invoice') {
            $type = $all['invoice_type'] ?? '';
            $pos  = str_pad((string) ($all['point_of_sale'] ?? '1'), 4, '0', STR_PAD_LEFT);
            $num  = str_pad((string) ($all['receipt_number'] ?? $record->auditable_id), 8, '0', STR_PAD_LEFT);
            return trim("FC {$type} {$pos}-{$num}");
        }

        if (isset($all['first_name']) || isset($all['last_name'])) {
            return trim(($all['last_name'] ?? '') . ' ' . ($all['first_name'] ?? ''));
        }

        if (isset($all['name'])) {
            return (string) $all['name'];
        }

        return '#' . $record->auditable_id;
    }

    public static function getFieldLabel(string $field): string
    {
        return match ($field) {
            // Facturación y Ventas
            'invoice_type'         => 'Tipo de Comprobante',
            'point_of_sale'        => 'Punto de Venta',
            'receipt_number'       => 'Número de Comprobante',
            'issue_date'           => 'Fecha de Emisión',
            'due_date'             => 'Fecha de Vencimiento',
            'total'                => 'Total Facturado',
            'customer_id'          => 'Cliente ID',
            'employee_id'          => 'Empleado / Vendedor ID',
            'store_id'             => 'Sucursal / Depósito ID',
            'status'               => 'Estado',
            'payment_method'       => 'Método de Pago',
            'caea_cae'             => 'Código CAE / CAEA',
            'cae_expiration'       => 'Vencimiento CAE',

            // Clientes y Empleados
            'first_name'           => 'Nombre',
            'last_name'            => 'Apellido',
            'document_type'        => 'Tipo de Documento',
            'document_number'      => 'N° de Documento',
            'email'                => 'Correo Electrónico',
            'phone'                => 'Teléfono',
            'address'              => 'Dirección',
            'city_id'              => 'Ciudad ID',
            'tax_condition'        => 'Condición de IVA',
            'tax_id'               => 'CUIT / CUIL',
            'employee_category_id' => 'Categoría ID',
            'position'             => 'Puesto / Cargo',
            'salary'               => 'Salario / Remuneración',
            'hire_date'            => 'Fecha de Contratación',

            // Productos, Stock y Compras
            'name'                 => 'Nombre',
            'sku'                  => 'Código SKU',
            'barcode'              => 'Código de Barras',
            'description'          => 'Descripción',
            'price'                => 'Precio de Venta',
            'cost'                 => 'Costo de Compra',
            'stock'                => 'Stock Actual',
            'min_stock'            => 'Stock Mínimo',
            'supplier_id'          => 'Proveedor ID',
            'purchase_date'        => 'Fecha de Compra',
            'category_id'          => 'Categoría ID',
            'brand_id'             => 'Marca ID',
            'type'                 => 'Tipo',
            'specs'                => 'Especificaciones Técnicas',
            'is_active'            => 'Activo',
            'quantity'             => 'Cantidad',
            'unit_price'           => 'Precio Unitario',
            'subtotal'             => 'Subtotal',
            'invoice_id'           => 'Factura ID',
            'purchase_id'          => 'Compra ID',
            'payment_method_id'    => 'Medio de Pago ID',
            'event_id'             => 'Evento ID',
            'sponsor_id'           => 'Sponsor ID',

            // Proveedores y Sponsors
            'company_name'         => 'Razón Social / Empresa',
            'contact_name'         => 'Persona de Contacto',
            'contribution_amount'  => 'Monto de Aporte',

            // Asistencias
            'check_in'             => 'Hora de Entrada',
            'check_out'            => 'Hora de Salida',

            // Eventos
            'title'                => 'Título',
            'date'                 => 'Fecha',
            'time'                 => 'Horario',
            'venue_id'             => 'Lugar ID',
            'capacity'             => 'Capacidad',

            // Sistema y Usuarios
            'role'                 => 'Rol / Permiso',
            'user_id'              => 'Usuario ID',
            'notes'                => 'Observaciones / Notas',
            'email_verified_at'    => 'Fecha Verificación Email',
            'deleted_at'           => 'Fecha de Anulación',
            'created_at'           => 'Fecha de Creación',
            'updated_at'           => 'Fecha de Actualización',

            default                => str($field)->replace('_', ' ')->title()->toString(),
        };
    }

    public static function formatFieldValue(string $field, mixed $val): string
    {
        if ($val === null || $val === '') {
            return '—';
        }

        if (is_bool($val)) {
            return $val ? 'Sí' : 'No';
        }

        if (is_array($val)) {
            return json_encode($val, JSON_UNESCAPED_UNICODE);
        }

        if (in_array($field, ['total', 'price', 'cost', 'unit_price', 'subtotal', 'salary', 'contribution_amount'], true) && is_numeric($val)) {
            return '$ ' . number_format((float) $val, 2, ',', '.');
        }

        if (is_string($val) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
            $parts = explode('-', $val);
            return "{$parts[2]}/{$parts[1]}/{$parts[0]}";
        }

        if (is_string($val) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $val)) {
            $timestamp = strtotime($val);
            return date('d/m/Y H:i:s', $timestamp);
        }

        return (string) $val;
    }

    public static function formatValuesForDisplay(?array $values): array
    {
        if (empty($values)) {
            return [];
        }

        $formatted = [];
        $ignored   = ['remember_token', 'password'];

        foreach ($values as $key => $val) {
            if (in_array($key, $ignored, true)) {
                continue;
            }

            $label             = static::getFieldLabel($key);
            $formatted[$label] = static::formatFieldValue($key, $val);
        }

        return $formatted;
    }

    public static function getDiffDisplay(Audit $record): array
    {
        $oldFormatted = static::formatValuesForDisplay($record->old_values ?? []);
        $newFormatted = static::formatValuesForDisplay($record->new_values ?? []);

        $allKeys = array_unique(array_merge(array_keys($oldFormatted), array_keys($newFormatted)));
        $diff = [];

        foreach ($allKeys as $key) {
            $old = $oldFormatted[$key] ?? '—';
            $new = $newFormatted[$key] ?? '—';
            if ($old !== $new) {
                $diff[$key] = "{$old}  ➜  {$new}";
            }
        }

        return $diff;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAudits::route('/'),
        ];
    }
}
