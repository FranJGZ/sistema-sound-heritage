<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    // =========================================================================
    // 1. CONFIGURACIÓN DEL MENÚ Y MODELO
    // =========================================================================
    protected static ?string $model = Supplier::class;
    protected static ?string $navigationGroup = 'Tienda';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Proveedores';
    protected static ?string $modelLabel = 'Proveedor';
    protected static ?string $pluralModelLabel = 'Proveedores';

    // Desactiva SoftDeletingScope para conservar en el historial los proveedores dados de baja junto a sus órdenes de compra
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    // =========================================================================
    // 2. CONTROL DE PERMISOS (SOLO ADMINISTRADOR PUEDE MODIFICAR)
    // El Encargado de Stock puede consultar el padrón de proveedores en modo lectura.
    // =========================================================================
    private static function isAdminUser(): bool
    {
        return auth()->user()?->hasRole('Administrador') ?? false;
    }

    public static function canCreate(): bool
    {
        return self::isAdminUser();
    }

    public static function canEdit(Model $record): bool
    {
        return self::isAdminUser();
    }

    public static function canDelete(Model $record): bool
    {
        return self::isAdminUser();
    }

    // =========================================================================
    // 3. FORMULARIO DEL PROVEEDOR (CREATE / EDIT)
    // =========================================================================
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Proveedor')
                    ->description('Datos generales y de contacto de la empresa o distribuidor.')
                    ->icon('heroicon-m-identification')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre / Razón Social')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Fender Musical Instruments')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('contact_name')
                            ->label('Persona de Contacto')
                            ->maxLength(255)
                            ->placeholder('Ej: Carlos Gómez')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono Corporativo')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('+54 9 11 ...')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('contacto@proveedor.com')
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('address')
                            ->label('Dirección Fiscal / Depósito')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    // =========================================================================
    // 4. TABLA PRINCIPAL DE PROVEEDORES
    // =========================================================================
    public static function table(Table $table): Table
    {
        return $table
            // -----------------------------------------------------------------
            // A) COLUMNAS VISIBLES EN EL LISTADO
            // -----------------------------------------------------------------
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Proveedor')
                    ->weight(FontWeight::Bold)
                    ->color('secondary')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Contacto')
                    ->icon('heroicon-m-user')
                    ->placeholder('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->icon('heroicon-m-phone')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->icon('heroicon-m-envelope')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->state(fn (Supplier $record): string => $record->trashed() ? 'De Baja' : 'Activo')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Activo' ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Alta')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')

            // -----------------------------------------------------------------
            // B) FILTROS DE BÚSQUEDA
            // -----------------------------------------------------------------
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Estado del Proveedor')
                    ->placeholder('Solo Proveedores Activos')
                    ->trueLabel('Todos (Activos + Dados de Baja)')
                    ->falseLabel('Solo Dados de Baja'),
            ])

            // -----------------------------------------------------------------
            // C) ACCIONES POR FILA Y MASIVAS
            // -----------------------------------------------------------------
            ->recordUrl(fn (Supplier $record): ?string => $record->trashed() ? null : static::getUrl('edit', ['record' => $record]))
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->label('Dar de baja')
                        ->modalHeading('Dar de baja Proveedor')
                        ->modalDescription('¿Estás seguro de dar de baja este proveedor? Las compras anteriores se mantendrán registradas intactas.')
                        ->modalSubmitActionLabel('Sí, dar de baja')
                        ->successNotificationTitle('Proveedor dado de baja')
                        ->hidden(fn (Supplier $record): bool => $record->trashed()),

                    Tables\Actions\RestoreAction::make()
                        ->label('Reactivar')
                        ->modalHeading('Reactivar Proveedor')
                        ->modalDescription('¿Deseas reactivar este proveedor en el sistema?')
                        ->modalSubmitActionLabel('Sí, reactivar')
                        ->successNotificationTitle('Proveedor reactivado')
                        ->visible(fn (Supplier $record): bool => $record->trashed()),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Dar de baja seleccionados')
                        ->modalHeading('Dar de baja proveedores seleccionados')
                        ->modalSubmitActionLabel('Sí, dar de baja')
                        ->successNotificationTitle('Proveedores dados de baja'),

                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Reactivar seleccionados')
                        ->modalHeading('Reactivar proveedores seleccionados')
                        ->modalSubmitActionLabel('Sí, reactivar')
                        ->successNotificationTitle('Proveedores reactivados'),
                ]),
            ]);
    }

    // =========================================================================
    // 5. RUTAS DE LAS PÁGINAS DEL RECURSO
    // =========================================================================
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit'   => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
