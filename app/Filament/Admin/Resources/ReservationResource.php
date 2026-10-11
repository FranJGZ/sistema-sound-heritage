<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReservationResource\Pages;
use App\Models\Event;
use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReservationResource extends Resource
{

    // =========================================================================
    // 1. CONFIGURACIÓN DEL MENÚ Y MODELO
    // Se agrupa en 'Eventos y Sala' con orden 2 para quedar debajo de Eventos Propios.
    // =========================================================================
    protected static ?string $model = Reservation::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Eventos y Sala';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Reserva de Sala';
    protected static ?string $pluralModelLabel = 'Reservas de Sala';

    // =========================================================================
    // 2. FORMULARIO DE SOLICITUD DE RESERVA (CREATE / EDIT)
    // =========================================================================
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Solicitud de Reserva del Salón (Terceros)')
                ->description('Gestión de alquileres del salón para ensayos de bandas, grabaciones o eventos privados.')
                ->schema([
                    Forms\Components\Select::make('customer_id')
                        ->label('Cliente / Banda Solicitante')
                        ->relationship(
                            'customer',
                            'last_name',
                            modifyQueryUsing: fn (Builder $query) => $query->where('document_number', '!=', '0')->orderBy('last_name')
                        )
                        // Muestra apellido, nombre y DNI juntos para evitar confusiones entre clientes con el mismo apellido
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->last_name}, {$record->first_name} (DNI: {$record->document_number})")
                        ->searchable(['first_name', 'last_name', 'document_number'])
                        ->preload()
                        ->required(),

                    // Se fija por defecto el salón principal (ID 1) y se quita la opción vacía porque el local posee una única sala
                    Forms\Components\Select::make('venue_id')
                        ->label('Espacio / Salón')
                        ->relationship('venue', 'name')
                        ->default(1)
                        ->required()
                        ->selectablePlaceholder(false),

                    Forms\Components\Select::make('type')
                        ->label('Motivo / Uso del Salón')
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
                        // Se valida contra dos tablas (events y reservations) porque el mismo salón físico se comparte entre eventos propios y alquileres
                        ->rules([
                            fn (Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                $venueId = $get('venue_id') ?: 1;

                                if (Event::where('venue_id', $venueId)->whereDate('date', $value)->exists()) {
                                    $fail('El salón ya está ocupado por un Evento propio de Sound Heritage en esa fecha.');
                                    return;
                                }

                                $consultaReserva = Reservation::where('venue_id', $venueId)
                                    ->whereDate('date', $value)
                                    ->where('status', 'Confirmada');

                                // Al editar una reserva ya confirmada se excluye su propio ID para que no detecte un falso choque consigo misma
                                if ($record) {
                                    $consultaReserva->where('id', '!=', $record->id);
                                }

                                if ($consultaReserva->exists()) {
                                    $fail('Ya existe otra reserva confirmada para el salón en esa fecha.');
                                }
                            },
                        ]),

                    // Nace como 'Pendiente' para registrar el pedido sin bloquear la fecha en el calendario hasta que el cliente abone la seña
                    Forms\Components\Select::make('status')
                        ->label('Estado de la Solicitud')
                        ->options([
                            'Pendiente'  => 'Pendiente de Aprobación',
                            'Confirmada' => 'Confirmada',
                            'Cancelada'  => 'Cancelada',
                        ])
                        ->default('Pendiente')
                        ->required(),
                ])->columns(2),
        ]);
    }

    // =========================================================================
    // 3. TABLA PRINCIPAL DE RESERVAS DE SALA
    // =========================================================================
    public static function table(Table $table): Table
    {
        return $table
            // -----------------------------------------------------------------
            // A) COLUMNAS VISIBLES EN EL LISTADO
            // -----------------------------------------------------------------
            ->columns([
                Tables\Columns\TextColumn::make('date')->label('Fecha Reservada')->date('d/m/Y')->sortable(),

                // Incluye el teléfono como subtítulo para poder contactar rápido al solicitante sin tener que abrir el formulario
                Tables\Columns\TextColumn::make('customer.last_name')
                    ->label('Cliente Solicitante')
                    ->formatStateUsing(fn ($record) => "{$record->customer->last_name}, {$record->customer->first_name}")
                    ->description(fn ($record) => "Tel: {$record->customer->phone}")
                    ->searchable(['first_name', 'last_name', 'document_number'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')->label('Uso / Motivo')->badge()->color('info'),
                Tables\Columns\TextColumn::make('venue.name')->label('Salón')->toggleable(),

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

            // -----------------------------------------------------------------
            // B) FILTROS DE AUDITORÍA Y BÚSQUEDA
            // Estos mismos filtros afectan tanto a la tabla en pantalla como al exportar el PDF.
            // -----------------------------------------------------------------
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'Pendiente'  => 'Pendientes de Aprobación',
                        'Confirmada' => 'Confirmadas',
                        'Cancelada'  => 'Canceladas',
                    ]),

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

                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Cliente Solicitante')
                    ->relationship('customer', 'last_name')
                    ->searchable()
                    ->preload(),

                // Se usa Filter personalizado porque el rango requiere comparar '>=' y '<=' sobre la misma columna date
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('desde')->label('Fecha Desde'),
                        Forms\Components\DatePicker::make('hasta')->label('Fecha Hasta'),
                    ])
                    ->columns(2)
                    ->columnSpan(2)
                    ->query(function (Builder $query, array $data) {
                        if ($data['desde']) {
                            $query->whereDate('date', '>=', $data['desde']);
                        }
                        if ($data['hasta']) {
                            $query->whereDate('date', '<=', $data['hasta']);
                        }
                    }),
            ])
            ->filtersFormColumns(4)
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
                        $reservations = $livewire->getFilteredSortedTableQuery()->with(['customer', 'venue'])->get();

                        $rows = [];
                        foreach ($reservations as $reservation) {
                            $rows[] = [
                                $reservation->date,
                                $reservation->customer ? "{$reservation->customer->last_name}, {$reservation->customer->first_name}" : '-',
                                $reservation->customer?->phone ?? '-',
                                $reservation->type,
                                $reservation->venue?->name ?? 'Salón Principal',
                                $reservation->status,
                            ];
                        }

                        $pdf = Pdf::loadView('reports.pdf-generic', [
                            'title'             => 'Reporte de Reservas de Sala (Alquiler a Terceros)',
                            'filtros_aplicados' => collect($livewire->getTable()->getFilterIndicators())
                                ->map(fn ($indicator) => strip_tags((string) $indicator->getLabel()))
                                ->implode(' | ') ?: 'Ninguno (Todos los registros)',
                            'summary' => [
                                'Total Solicitudes' => $reservations->count(),
                                'Confirmadas'       => $reservations->where('status', 'Confirmada')->count(),
                                'Pendientes'        => $reservations->where('status', 'Pendiente')->count(),
                                'Canceladas'        => $reservations->where('status', 'Cancelada')->count(),
                            ],
                            'headers' => ['Fecha Reservada', 'Cliente', 'Teléfono', 'Motivo / Uso', 'Salón', 'Estado'],
                            'rows'    => $rows,
                        ])->setPaper('a4', 'landscape');

                        // Se usa streamDownload porque Livewire funciona por AJAX y no permite descargas directas tradicionales
                        return response()->streamDownload(fn () => print($pdf->output()), 'reporte-reservas-' . date('Ymd-His') . '.pdf');
                    }),
            ])

            // -----------------------------------------------------------------
            // D) ACCIONES POR CADA FILA (APROBAR, CANCELAR Y EDITAR)
            // -----------------------------------------------------------------
            ->actions([
                // Se incluye como acción directa en la fila para agilizar el cambio de estado al cobrar la seña sin entrar al formulario
                Tables\Actions\Action::make('confirmar')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Reservation $record) => $record->status === 'Pendiente')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Reserva de Salón')
                    ->modalDescription('¿Confirmás que el salón está disponible y el cliente abonó la seña?')
                    ->action(function (Reservation $record) {
                        $record->update(['status' => 'Confirmada']);
                        Notification::make()->title('Reserva confirmada con éxito')->success()->send();
                    }),

                // Cambia el estado a 'Cancelada' para liberar la fecha en el calendario sin borrar el registro histórico de la solicitud
                Tables\Actions\Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Reservation $record) => $record->status !== 'Cancelada')
                    ->requiresConfirmation()
                    ->action(function (Reservation $record) {
                        $record->update(['status' => 'Cancelada']);
                        Notification::make()->title('Reserva cancelada')->warning()->send();
                    }),

                Tables\Actions\EditAction::make(),
            ]);
    }

    // =========================================================================
    // 4. RUTAS DE LAS PÁGINAS DEL RECURSO
    // =========================================================================
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit'   => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}