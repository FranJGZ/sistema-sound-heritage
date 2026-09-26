<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SponsorResource\Pages;
use App\Models\Sponsor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SponsorResource extends Resource
{
    protected static ?string $model = Sponsor::class;
    protected static ?string $navigationGroup = 'Eventos y Sala';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Auspiciantes';
    protected static ?string $modelLabel = 'Auspiciante';
    protected static ?string $pluralModelLabel = 'Auspiciantes';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('eventSponsors')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos del Auspiciante / Marca')
                    ->icon('heroicon-m-sparkles')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de la Marca / Empresa')
                            ->placeholder('Ej: Yamaha Music / Radio Rock FM')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->label('Rubro / Categoría')
                            ->options([
                                'Instrumentos'   => 'Instrumentos Musicales',
                                'Accesorios'     => 'Accesorios e Insumos',
                                'Prensa y Medios'=> 'Prensa, Radio y Medios',
                                'Bebidas'        => 'Gastronomía y Bebidas',
                                'Productora'     => 'Productora / Audio',
                                'Otro'           => 'Otro',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono de Contacto')
                            ->tel()
                            ->required()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->maxLength(255),
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

                Tables\Columns\TextColumn::make('name')
                    ->label('Auspiciante')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Rubro')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->icon('heroicon-m-phone'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->icon('heroicon-m-envelope')
                    ->searchable(),

                Tables\Columns\TextColumn::make('event_sponsors_count')
                    ->label('Eventos Auspiciados')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(fn (Sponsor $record): string => $record->trashed() ? 'De Baja' : 'Activo')
                    ->color(fn (string $state): string => $state === 'Activo' ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Estado')
                    ->placeholder('Solo Activos')
                    ->trueLabel('Todos (Activos + Dados de Baja)')
                    ->falseLabel('Solo Dados de Baja'),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Rubro')
                    ->options([
                        'Instrumentos'    => 'Instrumentos Musicales',
                        'Accesorios'      => 'Accesorios e Insumos',
                        'Prensa y Medios' => 'Prensa, Radio y Medios',
                        'Bebidas'         => 'Gastronomía y Bebidas',
                        'Productora'      => 'Productora / Audio',
                        'Otro'            => 'Otro',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn (Sponsor $record): bool => $record->trashed()),

                Tables\Actions\DeleteAction::make()
                    ->label('Dar de baja')
                    ->hidden(fn (Sponsor $record): bool => $record->trashed()),

                Tables\Actions\RestoreAction::make()
                    ->label('Reactivar')
                    ->visible(fn (Sponsor $record): bool => $record->trashed()),
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
            'index'  => Pages\ListSponsors::route('/'),
            'create' => Pages\CreateSponsor::route('/create'),
            'edit'   => Pages\EditSponsor::route('/{record}/edit'),
        ];
    }
}