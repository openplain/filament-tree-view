<?php

namespace Openplain\FilamentTreeView\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Openplain\FilamentTreeView\Concerns\HasTreeStructure;
use Openplain\FilamentTreeView\Tests\Factories\CategoryFactory;

class Category extends Model
{
    use HasFactory;
    use HasTreeStructure;

    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
