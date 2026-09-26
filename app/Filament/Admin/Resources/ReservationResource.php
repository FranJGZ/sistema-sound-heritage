<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReservationResource\Pages;
use App\Models\Event;
use App\Models\Reservation;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;
    protected static ?string $navigationGroup = 'Eventos y Sala';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Reservas de Sala';
    protected static ?string $modelLabel = 'Reserva';
    protected static ?string $pluralModelLabel = 'Reservas de Sala';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['customer', 'venue']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Solicitud de Reserva del Salón (Terceros)')
                    ->description('Verificá que la fecha solicitada no coincida con un evento propio ni con otra reserva confirmada.')
                    ->icon('heroicon-m-calendar-days')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente Solicitante')
                            ->relationship(
                                name: 'customer',
                                titleAttribute: 'last_name',
                                modifyQueryUsing: fn (Builder $query) => $query->orderBy('last_name')
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->last_name}, {$record->first_name} (DNI: {$record->document_number})")
                            ->searchable(['first_name', 'last_name', 'document_number'])
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('venue_id')
                            ->label('Salón / Espacio')
                            ->relationship('venue', 'name')
                            ->default(1)
                            ->required()
                            ->selectablePlaceholder(false),

                        Forms\Components\Select::make('type')
                            ->label('Motivo / Tipo de Uso')
                            ->options([
                                'Ensayo de Banda'          => 'Ensayo de Banda',
                                'Ensayo General'           => 'Ensayo General',
                                'Grabación Acústica'       => 'Grabación Acústica',
                                'Lanzamiento de Videoclip' => 'Lanzamiento de Videoclip',
                                'Masterclass Externa'      => 'Masterclass Externa',
                                'Evento Privado'           => 'Evento Privado',
                            ])
                            ->required(),

                        Forms\Components\DatePicker::make('date')
                            ->label('Fecha Solicitada')
                            ->required()
                            ->rules([
                                fn (Forms\Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                    $venueId = $get('venue_id') ?? 1;

                                    $eventoExistente = Event::query()
                                        ->where('venue_id', $venueId)
                                        ->whereDate('date', $value)
                                        ->first();

                                    if ($eventoExistente) {
                                        $fail("El salón ya está ocupado en esa fecha por el Evento propio: «{$eventoExistente->name}».");
                                        return;
                                    }

                                    $reservaExistente = Reservation::query()
                                        ->where('venue_id', $venueId)
                                        ->whereDate('date', $value)
                                        ->where('status', 'Confirmada')
                                        ->when($record, fn (Builder $q) => $q->where('id', '!=', $record->id))
                                        ->first();

                                    if ($reservaExistente) {
                                        $fail("El salón ya tiene otra Reserva Confirmada en esa fecha.");
                                    }
                                },
                            ]),

                        Forms\Components\Select::make('status')
                            ->label('Estado de la Reserva')
                            ->options([
                                'Pendiente'  => 'Pendiente de Aprobación',
                                'Confirmada' => 'Confirmada',
                                'Cancelada'  => 'Cancelada',
                            ])
                            ->default('Pendiente')
                            ->required(),
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

                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha Reservada')
                    ->date('d/m/Y')
                    ->weight(FontWeight::Bold)
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.last_name')
                    ->label('Cliente Responsable')
                    ->formatStateUsing(fn (Reservation $record): string => $record->customer ? "{$record->customer->last_name}, {$record->customer->first_name}" : '-')
                    ->description(fn (Reservation $record): string => $record->customer ? "Tel: {$record->customer->phone}" : '')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo de Uso')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('venue.name')
                    ->label('Salón'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Confirmada' => 'success',
                        'Pendiente'  => 'warning',
                        'Cancelada'  => 'danger',
                        default      => 'gray',
                    }),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                // 1. Por Estado
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado de Reserva')
                    ->options([
                        'Pendiente'  => 'Pendiente',
                        'Confirmada' => 'Confirmada',
                        'Cancelada'  => 'Cancelada',
                    ]),

                // 2. Por Tipo de Uso
                Tables\Filters\SelectFilter::make('type')
                    ->label('Motivo / Uso')
                    ->options([
                        'Ensayo de Banda'          => 'Ensayo de Banda',
                        'Ensayo General'           => 'Ensayo General',
                        'Grabación Acústica'       => 'Grabación Acústica',
                        'Lanzamiento de Videoclip' => 'Lanzamiento de Videoclip',
                        'Masterclass Externa'      => 'Masterclass Externa',
                        'Evento Privado'           => 'Evento Privado',
                    ]),

                // 3. Por Cliente
                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->last_name}, {$record->first_name}")
                    ->searchable()
                    ->preload(),

                // 4. Rango de Fechas
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('desde')->label('Fecha Desde'),
                        Forms\Components\DatePicker::make('hasta')->label('Fecha Hasta'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(filled($data['desde'] ?? null), fn (Builder $q) => $q->whereDate('date', '>=', $data['desde']))
                            ->when(filled($data['hasta'] ?? null), fn (Builder $q) => $q->whereDate('date', '<=', $data['hasta']));
                    }),
                        ])
            ->filtersFormColumns(4)
            ->filtersFormWidth(\Filament\Support\Enums\MaxWidth::FourExtraLarge)
            ->headerActions([
                Tables\Actions\Action::make('exportar_pdf')
                    ->label('Exportar PDF')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('primary')
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredSortedTableQuery()->get();

                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf-generic', [
                            'title'   => 'Cronograma de Reservas de Sala (Sound Heritage Live Room)',
                            'summary' => [
                                'Total Solicitudes'    => $records->count(),
                                'Reservas Confirmadas' => $records->where('status', 'Confirmada')->count(),
                                'Pendientes'           => $records->where('status', 'Pendiente')->count(),
                                'Canceladas'           => $records->where('status', 'Cancelada')->count(),
                            ],
                            'headers' => ['ID', 'Fecha Reservada', 'Cliente Responsable', 'Teléfono', 'Motivo / Uso', 'Salón', 'Estado'],
                            'rows'    => $records->map(fn ($r) => [
                                '#' . str_pad($r->id, 4, '0', STR_PAD_LEFT),
                                \Carbon\Carbon::parse($r->date)->format('d/m/Y'),
                                $r->customer ? "{$r->customer->last_name}, {$r->customer->first_name}" : '-',
                                $r->customer?->phone ?? '-',
                                $r->type,
                                $r->venue?->name ?? 'Sound Heritage Live Room',
                                $r->status,
                            ])->toArray(),
                        ])->setPaper('a4', 'landscape');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'reporte-reservas-N' . str_pad((string) \Illuminate\Support\Facades\Cache::get('sh_pdf_report_seq', 1), 4, '0', STR_PAD_LEFT) . '-' . now()->format('Ymd-His') . '.pdf'
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('confirmar')
                    ->label('Confirmar')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Reservation $record): bool => $record->status === 'Pendiente')
                    ->action(fn (Reservation $record) => $record->update(['status' => 'Confirmada'])),

                Tables\Actions\Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Reservation $record): bool => $record->status !== 'Cancelada')
                    ->action(fn (Reservation $record) => $record->update(['status' => 'Cancelada'])),

                Tables\Actions\EditAction::make(),
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
            'index'  => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit'   => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}