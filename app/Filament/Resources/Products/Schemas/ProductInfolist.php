<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use App\Services\ProductReadinessService;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.schemas.components.product-cockpit')
                    ->viewData(fn (Product $record, ProductReadinessService $readinessService): array => [
                        'readiness' => $readinessService->assess($record),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
