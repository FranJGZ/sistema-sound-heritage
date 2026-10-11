<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Administración y RRHH';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Usuarios y Roles';
    protected static ?string $modelLabel = 'Usuario';
    protected static ?string $pluralModelLabel = 'Usuarios';

    public static function canCreate(): bool
    {
        // Las cuentas nacen desde Empleados, Clientes o el Registro Web
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['roles', 'employee', 'customer'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la Cuenta (Vinculados)')
                    ->description('El nombre y correo provienen de la ficha del Empleado o Cliente asociado.')
                    ->icon('heroicon-m-user-circle')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre Completo')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Permisos de Acceso y Roles')
                    ->description('Modificá los roles de acceso al sistema para este usuario. Para cambiar la contraseña, utilizá el envío de enlace de restablecimiento por correo.')
                    ->icon('heroicon-m-shield-check')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Roles Asignados')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Usuario')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->icon('heroicon-m-envelope')
                    ->searchable(),

                Tables\Columns\TextColumn::make('origen')
                    ->label('Ficha Vinculada')
                    ->badge()
                    ->getStateUsing(function (User $record): string {
                        if ($record->employee) {
                            return 'Empleado (DNI ' . $record->employee->document_number . ')';
                        }
                        if ($record->customer) {
                            return 'Cliente (DNI ' . $record->customer->document_number . ')';
                        }
                        return 'Cuenta de Sistema / Web';
                    })
                    ->color(fn (string $state): string => match (true) {
                        str_starts_with($state, 'Empleado') => 'info',
                        str_starts_with($state, 'Cliente')  => 'success',
                        default                             => 'gray',
                    }),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Administrador'      => 'danger',
                        'Vendedor'           => 'warning',
                        'Encargado de Stock' => 'info',
                        default              => 'success',
                    }),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(fn (User $record): string => $record->trashed() ? 'Suspendido' : 'Activo')
                    ->color(fn (string $state): string => $state === 'Activo' ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Estado de la Cuenta')
                    ->placeholder('Solo cuentas activas')
                    ->trueLabel('Todas (Activas + Suspendidas)')
                    ->falseLabel('Solo cuentas suspendidas'),

                Tables\Filters\SelectFilter::make('roles')
                    ->label('Filtrar por Rol')
                    ->relationship('roles', 'name')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Roles')
                    ->hidden(fn (User $record): bool => $record->trashed()),

                Tables\Actions\Action::make('enviar_reset')
                    ->label('Resetear Clave')
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Enviar Enlace de Recuperación de Contraseña')
                    ->modalDescription(fn (User $record) => "¿Confirmás enviar un correo a {$record->email} con el enlace para que el usuario restablezca su propia contraseña?")
                    ->action(function (User $record) {
                        $status = Password::sendResetLink(['email' => $record->email]);

                        if ($status === Password::RESET_LINK_SENT) {
                            Notification::make()
                                ->title('Correo de recuperación enviado')
                                ->body("Se envió el enlace de restablecimiento a {$record->email}.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('No se pudo enviar el correo')
                                ->body(__($status))
                                ->danger()
                                ->send();
                        }
                    })
                    ->hidden(fn (User $record): bool => $record->trashed()),

                Tables\Actions\DeleteAction::make()
                    ->label('Suspender')
                    ->modalHeading('Suspender acceso de usuario')
                    ->modalDescription('¿Estás seguro de suspender esta cuenta? El usuario ya no podrá iniciar sesión en el sistema.')
                    ->hidden(fn (User $record): bool => $record->trashed() || $record->id === auth()->id()),

                Tables\Actions\RestoreAction::make()
                    ->label('Reactivar')
                    ->modalHeading('Reactivar cuenta de usuario')
                    ->visible(fn (User $record): bool => $record->trashed()),
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
            'index' => Pages\ListUsers::route('/'),
            'edit'  => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}