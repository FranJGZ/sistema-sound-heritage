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

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Proveedores';
    protected static ?string $modelLabel = 'Proveedor';
    protected static ?string $pluralModelLabel = 'Proveedores';

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

                Tables\Columns\TextColumn::make('name')
                    ->label('Proveedor')
                    ->weight(FontWeight::Bold)
                    ->color('secondary')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Contacto')
                    ->icon('heroicon-m-user')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->icon('heroicon-m-phone'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Alta')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('primary'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit'   => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
