<?php

namespace App\Filament\Admin\Resources\PurchaseResource\Pages;

use App\Filament\Admin\Resources\PurchaseResource;
use App\Models\Purchase;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'activas' => Tab::make('Compras Activas')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at'))
                ->badge(Purchase::query()->whereNull('deleted_at')->count()),

            'anuladas' => Tab::make('Compras Anuladas')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('deleted_at'))
                ->badge(Purchase::query()->whereNotNull('deleted_at')->count())
                ->badgeColor('danger'),

            'todas' => Tab::make('Todas')
                ->badge(Purchase::query()->withoutGlobalScopes([SoftDeletingScope::class])->count()),
        ];
    }
}