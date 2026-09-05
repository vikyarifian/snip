<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Snippet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'content',
        'department',
        'tags',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    /**
     * Scope a query to search snippets.
     */
    public function scopeSearch($query, ?string $search)
    {
        if (empty($search)) {
            return $query;
        }

        // Workaround for Laravel Eloquent query builder limitation where whereFullText does not support ordering by relevance score
        return $query->whereFullText(['title', 'content'], $search)
            ->selectRaw("*, MATCH(title, content) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance", [$search])
            ->orderByDesc('relevance');
    }

    /**
     * Scope a query to filter snippets by department.
     */
    public function scopeByDepartment($query, ?string $department)
    {
        if (empty($department)) {
            return $query;
        }

        return $query->where('department', $department);
    }

    /**
     * Scope a query to filter snippets by a specific tag.
     */
    public function scopeByTag($query, ?string $tag)
    {
        if (empty($tag)) {
            return $query;
        }

        return $query->whereJsonContains('tags', $tag);
    }
}
