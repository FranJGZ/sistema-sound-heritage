<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static ?string $navigationGroup = 'Administración y RRHH';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Empleados';
    protected static ?string $modelLabel = 'Empleado';
    protected static ?string $pluralModelLabel = 'Empleados';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['city.province', 'employeeCategory', 'user.roles'])
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
                Forms\Components\Section::make('Legajo del Empleado')
                    ->description('Al dar de alta un empleado se genera automáticamente su usuario de sistema con su DNI como contraseña inicial. Luego podés asignarle su rol en "Usuarios y Roles".')
                    ->icon('heroicon-m-identification')
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

                        Forms\Components\Select::make('employee_category_id')
                            ->label('Categoría / Puesto')
                            ->relationship('employeeCategory', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre de la Categoría / Puesto')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono de Contacto')
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Legajo')
                    ->formatStateUsing(fn ($state): string => '#' . str_pad($state, 4, '0', STR_PAD_LEFT))
                    ->weight(FontWeight::SemiBold)
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_name')
                    ->label('Empleado')
                    ->formatStateUsing(fn (Employee $record): string => "{$record->last_name}, {$record->first_name}")
                    ->weight(FontWeight::Bold)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('document_number')
                    ->label('DNI')
                    ->searchable(),

                Tables\Columns\TextColumn::make('employeeCategory.name')
                    ->label('Puesto')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Usuario Vinculado')
                    ->icon('heroicon-m-envelope')
                    ->placeholder('Sin cuenta'),

                Tables\Columns\TextColumn::make('invoices_count')
                    ->label('Ventas')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoices_sum_total')
                    ->label('Total Facturado ($)')
                    ->money('ARS')
                    ->default(0)
                    ->sortable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(fn (Employee $record): string => $record->trashed() ? 'De Baja' : 'Activo')
                    ->color(fn (string $state): string => $state === 'Activo' ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Estado del Empleado')
                    ->placeholder('Solo Empleados Activos')
                    ->trueLabel('Todos (Activos + Dados de Baja)')
                    ->falseLabel('Solo Dados de Baja'),

                Tables\Filters\SelectFilter::make('employee_category_id')
                    ->label('Puesto / Categoría')
                    ->relationship('employeeCategory', 'name')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn (Employee $record): bool => $record->trashed()),

                Tables\Actions\DeleteAction::make()
                    ->label('Dar de baja')
                    ->modalHeading('Dar de baja Empleado')
                    ->modalDescription('¿Estás seguro de dar de baja este empleado? Su cuenta de acceso al sistema también quedará suspendida.')
                    ->hidden(fn (Employee $record): bool => $record->trashed()),

                Tables\Actions\RestoreAction::make()
                    ->label('Reactivar')
                    ->visible(fn (Employee $record): bool => $record->trashed()),
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
            'index'  => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit'   => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}