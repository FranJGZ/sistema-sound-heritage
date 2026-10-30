<?php

namespace App\Filament\Admin\Resources\AuditResource\Pages;

use App\Filament\Admin\Resources\AuditResource;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use OwenIt\Auditing\Models\Audit;

class ListAudits extends ListRecords
{
    protected static string $resource = AuditResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos')
                ->badge(fn () => Audit::count()),

            'ventas' => Tab::make('Ventas')
                ->badge(fn () => Audit::where('auditable_type', Invoice::class)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('auditable_type', Invoice::class)),

            'clientes' => Tab::make('Clientes')
                ->badge(fn () => Audit::where('auditable_type', Customer::class)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('auditable_type', Customer::class)),

            'compras' => Tab::make('Compras')
                ->badge(fn () => Audit::where('auditable_type', Purchase::class)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('auditable_type', Purchase::class)),

            'productos' => Tab::make('Productos')
                ->badge(fn () => Audit::where('auditable_type', Product::class)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('auditable_type', Product::class)),

            'proveedores' => Tab::make('Proveedores')
                ->badge(fn () => Audit::where('auditable_type', Supplier::class)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('auditable_type', Supplier::class)),

            'empleados' => Tab::make('Empleados')
                ->badge(fn () => Audit::where('auditable_type', Employee::class)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('auditable_type', Employee::class)),

            'usuarios' => Tab::make('Usuarios y Roles')
                ->badge(fn () => Audit::where('auditable_type', User::class)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('auditable_type', User::class)),
        ];
    }
}
