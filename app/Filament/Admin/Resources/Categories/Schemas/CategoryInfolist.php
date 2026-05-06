<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('Name')),
                TextEntry::make('slug')
                    ->label(__('Slug')),
                TextEntry::make('icon')
                    ->label(__('Icon'))
                    ->placeholder('-'),
                TextEntry::make('sort_order')
                    ->label(__('Sort Order'))
                    ->numeric(),
                TextEntry::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
