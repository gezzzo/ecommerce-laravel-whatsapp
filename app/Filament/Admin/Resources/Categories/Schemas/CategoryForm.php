<?php

namespace App\Filament\Admin\Resources\Categories\Schemas;

use App\Support\ImageUploadHelper;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label(__('Slug'))
                    ->required()
                    ->maxLength(255),
                ImageUploadHelper::make('icon')
                    ->label(__('Icon'))
                    ->directory('categories'),
                TextInput::make('sort_order')
                    ->label(__('Sort Order'))
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
