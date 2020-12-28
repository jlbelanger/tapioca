<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Tag;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\Database\Factories\ArticleFactory;
use Jlbelanger\LaravelJsonApi\Traits\Resource;

class Article extends Model
{
	use HasFactory, Resource;

	protected $fillable = [
		'title',
		'content',
		'word_count',
	];

	/**
	 * Creates a new factory instance for the model.
	 *
	 * @return Factory
	 */
	protected static function newFactory() : Factory
	{
		return ArticleFactory::new();
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
		return ['tags'];
	}

	/**
	 * @return BelongsToMany
	 */
	public function tags() : BelongsToMany
	{
		return $this->belongsToMany(Tag::class);
	}
}
