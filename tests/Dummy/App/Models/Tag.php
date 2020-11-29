<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Article;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\Database\Factories\TagFactory;
use Jlbelanger\LaravelJsonApi\Traits\Resource;

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
	 * @return array
	 */
	protected function rules() : array
	{
		return [
			'attributes.title' => 'required',
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
