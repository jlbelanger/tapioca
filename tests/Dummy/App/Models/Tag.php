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

	/**
	 * Creates a new factory instance for the model.
	 *
	 * @return Factory
	 */
	protected static function newFactory() : Factory
	{
		return TagFactory::new();
	}

	/**
	 * @param  array  $data
	 * @param  string $method
	 * @return array
	 */
	protected function rules(array $data, string $method) : array
	{
		return [
			'attributes.title' => $this->requiredOnCreate($method),
		];
	}

	/**
	 * @return array
	 */
	public function multiRelationships() : array
	{
		return ['articles'];
	}

	/**
	 * @return array
	 */
	public function singularRelationships() : array
	{
		return ['parent'];
	}

	/**
	 * @return HasMany
	 */
	public function articles() : HasMany
	{
		return $this->hasMany(Article::class);
	}

	/**
	 * @return BelongsTo
	 */
	public function parent() : BelongsTo
	{
		return $this->belongsTo(self::class, 'parent_id');
	}
}
