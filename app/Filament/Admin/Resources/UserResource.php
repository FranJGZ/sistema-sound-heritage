<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

        public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                \Filament\Forms\Components\TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->email()
                    ->required()
                    ->maxLength(255),

                \Filament\Forms\Components\TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),

                // SELECTOR MÚLTIPLE DE ROLES DE SPATIE
                \Filament\Forms\Components\Select::make('roles')
                    ->label('Roles Asignados')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }

        public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable(),

                // MUESTRA LOS ROLES COMO ETIQUETAS DE COLORES
                \Filament\Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Administrador' => 'danger',
                        'Vendedor' => 'warning',
                        'Encargado de Stock' => 'info',
                        'Empleado' => 'gray',
                        default => 'success',
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
