<?php

namespace App\Filament\Admin\Resources\SponsorResource\Pages;

use App\Filament\Admin\Resources\SponsorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSponsor extends EditRecord
{
    protected static string $resource = SponsorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
