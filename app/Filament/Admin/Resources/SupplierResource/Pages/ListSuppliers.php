<?php

namespace App\Filament\Admin\Resources\SupplierResource\Pages;

use App\Filament\Admin\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuevo Proveedor'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'activos' => Tab::make('Proveedores Activos')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),

            'bajas' => Tab::make('Dados de Baja')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('deleted_at')),

            'todos' => Tab::make('Todos')
        ];
    }
}
