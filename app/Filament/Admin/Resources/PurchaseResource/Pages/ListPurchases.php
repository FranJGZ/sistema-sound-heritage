<?php

namespace App\Filament\Admin\Resources\PurchaseResource\Pages;

use App\Filament\Admin\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nueva Compra'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'activas' => Tab::make('Compras Activas')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),
                
            'anuladas' => Tab::make('Compras Anuladas')
                ->modifyQueryUsing(fn (Builder $query) => $query->withTrashed()->whereNotNull('deleted_at')),
                
            'todas' => Tab::make('Todas')
                ->modifyQueryUsing(fn (Builder $query) => $query->withTrashed()),
        ];
    }
}