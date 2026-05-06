<?php

namespace App\Filament\Admin\Resources\Products\Schemas;

use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use App\Support\ImageUploadHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Basic Information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                if ($state) {
                                    $slug = Str::slug($state);

                                    // Ensure uniqueness
                                    $originalSlug = $slug;
                                    $counter = 1;
                                    while (Product::withTrashed()->where('slug', $slug)->exists()) {
                                        $slug = $originalSlug.'-'.$counter;
                                        $counter++;
                                    }

                                    $set('slug', $slug);
                                }
                            }),
                        Select::make('category_id')
                            ->label(__('Category'))
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText(__('Auto-generated from the product name')),
                        RichEditor::make('description')
                            ->label(__('Description'))
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make(__('Media'))
                    ->schema([
                        ImageUploadHelper::make('thumbnail')
                            ->label(__('Thumbnail'))
                            ->required()
                            ->directory('products/thumbnails'),
                        ImageUploadHelper::make('image')
                            ->label(__('Image'))
                            ->required()
                            ->directory('products'),
                    ])
                    ->columns(1),
                Section::make(__('Settings'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true),
                        Toggle::make('is_featured')
                            ->label(__('Featured'))
                            ->default(false),
                        Toggle::make('has_variants')
                            ->label(__('Has Variants'))
                            ->default(false)
                            ->live(),
                    ])
                    ->columns(3),
                Section::make(__('Pricing'))
                    ->schema([
                        TextInput::make('selling_price')
                            ->label(__('Selling Price'))
                            ->required()
                            ->numeric()
                            ->prefix(__('MAD')),
                        TextInput::make('price_before_discount')
                            ->label(__('Price Before Discount'))
                            ->numeric()
                            ->prefix(__('MAD')),
                    ])
                    ->columns(2),

                // ── Initial Inventory (for products without variants) ──
                Section::make(__('Inventory'))
                    ->schema([
                        TextInput::make('initial_quantity')
                            ->label(__('Initial Quantity'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText(__('Initial stock quantity for this product'))
                            ->dehydrated(false),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get): bool => ! (bool) $get('has_variants')),

                // ── Variant Generation Section ──
                Section::make(__('Generate Color × Size Combinations'))
                    ->description(__('Select the colors and sizes you want, and all combinations will be generated automatically'))
                    ->icon('heroicon-o-sparkles')
                    ->schema([
                        TextInput::make('variant_default_quantity')
                            ->label(__('Default Quantity'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText(__('Initial stock for each generated variant')),

                        Select::make('variant_sizes')
                            ->label(__('Sizes'))
                            ->options(fn () => Size::pluck('name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Select::make('variant_size_type')
                            ->label(__('Size Type'))
                            ->options(fn () => Size::select('type')->distinct()->pluck('type', 'type'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                if ($state) {
                                    $sizeIds = Size::where('type', $state)->pluck('id')->toArray();
                                    $set('variant_sizes', $sizeIds);
                                }
                            }),
                        Select::make('variant_colors')
                            ->label(__('Colors'))
                            ->options(fn () => Color::pluck('name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Placeholder::make('combinations_preview')
                            ->hiddenLabel()
                            ->content(function (Get $get): HtmlString {
                                $colors = $get('variant_colors') ?? [];
                                $sizes = $get('variant_sizes') ?? [];
                                $colorCount = count($colors);
                                $sizeCount = count($sizes);

                                if ($colorCount === 0 && $sizeCount === 0) {
                                    return new HtmlString('<span style="color: #6b7280;">'.__('Select colors and/or sizes to preview combinations').'</span>');
                                }

                                $total = max($colorCount, 1) * max($sizeCount, 1);
                                $message = __('This will generate :colors color(s) × :sizes size(s) = :total combination(s)', [
                                    'colors' => $colorCount,
                                    'sizes' => $sizeCount,
                                    'total' => $total,
                                ]);

                                return new HtmlString(
                                    '<div style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 10px 16px; text-align: center;">'
                                    .'<span style="color: #ea580c; font-weight: 600;">'
                                    .e($message)
                                    .'</span></div>'
                                );
                            })
                            ->columnSpanFull()
                            ->live(),
                        Actions::make([
                            Action::make('generate_variants')
                                ->label(__('Generate'))
                                ->icon('heroicon-o-check')
                                ->color('success')
                                ->action(function (Get $get, Set $set): void {
                                    $colorIds = $get('variant_colors') ?? [];
                                    $sizeIds = $get('variant_sizes') ?? [];
                                    $sellingPrice = (float) ($get('selling_price') ?? 0);
                                    $priceBeforeDiscount = (float) ($get('price_before_discount') ?? 0);

                                    $variants = [];
                                    $existing = $get('variants') ?? [];

                                    // Build a set of existing color-size combos to avoid duplicates
                                    $existingCombos = [];
                                    foreach ($existing as $v) {
                                        $existingCombos[] = ($v['color_id'] ?? '').'-'.($v['size_id'] ?? '');
                                    }

                                    if (empty($colorIds) && empty($sizeIds)) {
                                        return;
                                    }

                                    $colorList = empty($colorIds) ? [null] : $colorIds;
                                    $sizeList = empty($sizeIds) ? [null] : $sizeIds;

                                    foreach ($colorList as $colorId) {
                                        foreach ($sizeList as $sizeId) {
                                            $combo = ($colorId ?? '').'-'.($sizeId ?? '');
                                            if (in_array($combo, $existingCombos)) {
                                                continue;
                                            }
                                            $variants[] = [
                                                'color_id' => $colorId,
                                                'size_id' => $sizeId,
                                                'initial_quantity' => (int) ($get('variant_default_quantity') ?? 0),
                                                'cost_price' => 0,
                                                'price_before_discount' => $priceBeforeDiscount,
                                                'selling_price' => $sellingPrice,
                                                'image' => null,
                                            ];
                                        }
                                    }

                                    $set('variants', array_merge($existing, $variants));
                                }),
                            Action::make('cancel_generation')
                                ->label(__('Cancel'))
                                ->icon('heroicon-o-x-mark')
                                ->color('gray')
                                ->action(function (Set $set): void {
                                    $set('variant_colors', []);
                                    $set('variant_sizes', []);
                                    $set('variant_size_type', null);
                                }),
                        ]),
                    ])
                    ->columns(4)
                    ->collapsible()
                    ->visible(fn (Get $get): bool => (bool) $get('has_variants'))
                    ->columnSpanFull(),

                // ── Product Variants Repeater ──
                Section::make(__('Product Variants'))
                    ->schema([
                        Repeater::make('variants')
                            ->label(__('Variants'))
                            ->relationship()
                            ->schema([
                                Select::make('color_id')
                                    ->label(__('Color'))
                                    ->relationship('color', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('size_id')
                                    ->label(__('Size'))
                                    ->relationship('size', 'name')
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('initial_quantity')
                                    ->label(__('Initial Qty'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->dehydrated(false),
                                TextInput::make('cost_price')
                                    ->label(__('Cost Price'))
                                    ->numeric()
                                    ->prefix(__('MAD'))
                                    ->default(0),
                                TextInput::make('price_before_discount')
                                    ->label(__('Price Before Discount'))
                                    ->numeric()
                                    ->prefix(__('MAD')),
                                TextInput::make('selling_price')
                                    ->label(__('Selling Price'))
                                    ->numeric()
                                    ->required()
                                    ->prefix(__('MAD')),
                                ImageUploadHelper::make('image')
                                    ->label(__('Image'))
                                    ->directory('variants'),
                            ])
                            ->columns(4)
                            ->defaultItems(0)
                            ->addActionLabel(__('+ Add Variant'))
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(function (array $state): ?string {
                                $parts = [];
                                if (! empty($state['color_id'])) {
                                    $color = Color::find($state['color_id']);
                                    if ($color) {
                                        $parts[] = $color->name;
                                    }
                                }
                                if (! empty($state['size_id'])) {
                                    $size = Size::find($state['size_id']);
                                    if ($size) {
                                        $parts[] = $size->name;
                                    }
                                }

                                return empty($parts) ? null : implode(' / ', $parts);
                            }),
                    ])
                    ->visible(fn (Get $get): bool => (bool) $get('has_variants'))
                    ->columnSpanFull(),
            ]);
    }
}
