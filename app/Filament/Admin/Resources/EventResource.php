<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EventResource\Pages;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\Sponsor;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static ?string $navigationGroup = 'Eventos y Sala';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Eventos';
    protected static ?string $modelLabel = 'Evento';
    protected static ?string $pluralModelLabel = 'Eventos';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['venue', 'eventSponsors.sponsor'])
            ->withCount('attendances')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Evento')
                    ->description('Configurá el evento cultural y su capacidad máxima.')
                    ->icon('heroicon-m-ticket')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Evento')
                            ->placeholder('Ej: Clínica de Batería / Recital Acústico')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('type')
                            ->label('Tipo de Evento')
                            ->options([
                                'Recital'            => 'Recital / Concierto',
                                'Taller/Clínica'     => 'Taller / Clínica Musical',
                                'Presentación'       => 'Presentación de Disco',
                                'Encuentro Cultural' => 'Encuentro Cultural / Jam Session',
                                'Feria'              => 'Feria de Audio e Instrumentos',
                            ])
                            ->required(),

                        Forms\Components\Select::make('venue_id')
                            ->label('Salón / Espacio')
                            ->relationship('venue', 'name')
                            ->default(1)
                            ->required()
                            ->selectablePlaceholder(false),

                        Forms\Components\DatePicker::make('date')
                            ->label('Fecha del Evento')
                            ->required()
                            ->rules([
                                fn (Forms\Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                    $venueId = $get('venue_id') ?? 1;

                                    $otroEvento = Event::query()
                                        ->where('venue_id', $venueId)
                                        ->whereDate('date', $value)
                                        ->when($record, fn (Builder $q) => $q->where('id', '!=', $record->id))
                                        ->first();

                                    if ($otroEvento) {
                                        $fail("Ya existe otro Evento programado en esa fecha: «{$otroEvento->name}».");
                                        return;
                                    }

                                    $reservaTercero = Reservation::query()
                                        ->where('venue_id', $venueId)
                                        ->whereDate('date', $value)
                                        ->where('status', 'Confirmada')
                                        ->first();

                                    if ($reservaTercero) {
                                        $fail("El salón ya tiene una Reserva Confirmada de un tercero en esa fecha ({$reservaTercero->type}).");
                                    }
                                },
                            ]),

                        Forms\Components\TextInput::make('capacity')
                            ->label('Capacidad / Cupo de Entradas')
                            ->numeric()
                            ->default(100)
                            ->minValue(1)
                            ->maxValue(100)
                            ->helperText('Capacidad máxima de Sound Heritage Live Room: 100 personas.')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Auspiciantes del Evento (EventSponsors)')
                    ->icon('heroicon-m-sparkles')
                    ->schema([
                        Forms\Components\Repeater::make('eventSponsors')
                            ->relationship()
                            ->label('Marcas Auspiciantes')
                            ->schema([
                                Forms\Components\Select::make('sponsor_id')
                                    ->label('Auspiciante')
                                    ->relationship('sponsor', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('+ Vincular Auspiciante al Evento'),
                    ]),

                Forms\Components\Section::make('Asistentes / Inscripciones (Attendances)')
                    ->icon('heroicon-m-users')
                    ->schema([
                        Forms\Components\Repeater::make('attendances')
                            ->relationship()
                            ->label('Lista de Inscriptos')
                            ->schema([
                                Forms\Components\Select::make('customer_id')
                                    ->label('Cliente Asistente')
                                    ->relationship('customer', 'last_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->last_name}, {$record->first_name} (DNI: {$record->document_number})")
                                    ->searchable(['first_name', 'last_name', 'document_number'])
                                    ->preload()
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->columnSpan(2),

                                Forms\Components\DatePicker::make('registration_date')
                                    ->label('Fecha de Inscripción')
                                    ->default(now())
                                    ->required()
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('+ Inscribir Cliente al Evento'),
                    ]),
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
                    ->label('Evento')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ocupacion')
                    ->label('Inscriptos / Cupo')
                    ->badge()
                    ->getStateUsing(fn (Event $record): string => "{$record->attendances_count} / {$record->capacity}")
                    ->color(function (Event $record): string {
                        if ($record->attendances_count >= $record->capacity) {
                            return 'danger';
                        }
                        if ($record->attendances_count >= ($record->capacity * 0.7)) {
                            return 'warning';
                        }
                        return 'success';
                    }),

                Tables\Columns\TextColumn::make('eventSponsors.sponsor.name')
                    ->label('Auspiciantes')
                    ->badge()
                    ->color('primary')
                    ->placeholder('Sin sponsors'),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->getStateUsing(fn (Event $record): string => $record->trashed() ? 'Cancelado' : 'Activo')
                    ->color(fn (string $state): string => $state === 'Activo' ? 'success' : 'danger'),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                                // Semáforo de Convocatoria (100% Eloquent ORM)
                Tables\Filters\SelectFilter::make('nivel_ocupacion')
                    ->label('Nivel de Convocatoria')
                    ->options([
                        'alta'           => 'Alta Convocatoria (5 o más inscriptos)',
                        'con_inscriptos' => 'Con Inscriptos (Al menos 1 entrada)',
                        'baja'           => 'Baja Convocatoria (Menos de 5 inscriptos)',
                        'sin_inscriptos' => 'Sin Inscriptos (0 entradas)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'alta'           => $query->has('attendances', '>=', 5),
                            'con_inscriptos' => $query->has('attendances'),
                            'baja'           => $query->has('attendances', '>=', 1)->has('attendances', '<', 5),
                            'sin_inscriptos' => $query->doesntHave('attendances'),
                            default          => $query,
                        };
                    }),
                Tables\Filters\TrashedFilter::make()
                    ->label('Estado del Evento')
                    ->placeholder('Solo Eventos Activos')
                    ->trueLabel('Todos (Activos + Cancelados)')
                    ->falseLabel('Solo Cancelados'),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo de Evento')
                    ->options([
                        'Recital'            => 'Recital / Concierto',
                        'Taller/Clínica'     => 'Taller / Clínica Musical',
                        'Presentación'       => 'Presentación de Disco',
                        'Encuentro Cultural' => 'Encuentro Cultural / Jam Session',
                        'Feria'              => 'Feria de Audio e Instrumentos',
                    ]),

                Tables\Filters\SelectFilter::make('sponsor_id')
                    ->label('Auspiciado por')
                    ->options(fn (): array => Sponsor::query()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn (Builder $q) => $q->whereHas('eventSponsors', fn (Builder $sq) => $sq->where('sponsor_id', $data['value']))
                        );
                    }),

                Tables\Filters\SelectFilter::make('vigencia')
                    ->label('Vigencia')
                    ->options([
                        'proximos' => 'Próximos a realizarse',
                        'pasados'  => 'Finalizados / Históricos',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'proximos' => $query->whereDate('date', '>=', now()),
                            'pasados'  => $query->whereDate('date', '<', now()),
                            default    => $query,
                        };
                    }),

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
            ->filtersFormColumns(3)
            ->filtersFormWidth(\Filament\Support\Enums\MaxWidth::FourExtraLarge)
            ->headerActions([
                Tables\Actions\Action::make('exportar_pdf')
                    ->label('Exportar PDF')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('primary')
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredSortedTableQuery()->get();

                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf-generic', [
                            'title'   => 'Reporte de Eventos, Convocatoria y Auspiciantes',
                            'summary' => [
                                'Eventos Listados'   => $records->count(),
                                'Total Inscriptos'   => $records->sum('attendances_count'),
                                'Capacidad Ofrecida' => $records->sum('capacity') . ' lugares',
                                'Eventos Activos'    => $records->whereNull('deleted_at')->count(),
                            ],
                            'headers' => ['ID', 'Evento', 'Tipo', 'Fecha', 'Inscriptos / Cupo', 'Auspiciantes', 'Estado'],
                            'rows'    => $records->map(fn ($r) => [
                                '#' . str_pad($r->id, 4, '0', STR_PAD_LEFT),
                                $r->name,
                                $r->type,
                                \Carbon\Carbon::parse($r->date)->format('d/m/Y'),
                                "{$r->attendances_count} / {$r->capacity}",
                                $r->eventSponsors->map(fn ($es) => $es->sponsor?->name)->filter()->implode(', ') ?: 'Sin sponsors',
                                filled($r->deleted_at) ? 'Cancelado' : 'Activo',
                            ])->toArray(),
                        ])->setPaper('a4', 'landscape');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'reporte-eventos-N' . str_pad((string) \Illuminate\Support\Facades\Cache::get('sh_pdf_report_seq', 1), 4, '0', STR_PAD_LEFT) . '-' . now()->format('Ymd-His') . '.pdf'
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn (Event $record): bool => $record->trashed()),

                Tables\Actions\DeleteAction::make()
                    ->label('Cancelar Evento')
                    ->hidden(fn (Event $record): bool => $record->trashed()),

                Tables\Actions\RestoreAction::make()
                    ->label('Reactivar')
                    ->visible(fn (Event $record): bool => $record->trashed()),
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
            'index'  => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit'   => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}