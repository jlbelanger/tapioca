<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Article;
use Jlbelanger\Tapioca\Tests\Dummy\Database\Factories\TagFactory;
use Jlbelanger\Tapioca\Traits\Resource;

class Tag extends Model
{
	use HasFactory, Resource;

	protected $fillable = [
		'title',
		'parent_id',
	];

	protected static function newFactory() : Factory
	{
		return TagFactory::new();
	}

	public function rules(array $data) : array
	{
		return [
			'data.attributes.title' => [$this->requiredOnCreate()],
		];
	}

	public function multiRelationships() : array
	{
		return ['articles'];
	}

	public function singularRelationships() : array
	{
		return ['parent'];
	}

	public function articles() : HasMany
	{
		return $this->hasMany(Article::class);
	}

	public function parent() : BelongsTo
	{
		return $this->belongsTo(self::class, 'parent_id');
	}
}
