<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Comment;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Tag;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\User;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\Database\Factories\ArticleFactory;
use Jlbelanger\LaravelJsonApi\Traits\Resource;

class Article extends Model
{
	use HasFactory, Resource;

	protected $fillable = [
		'title',
		'content',
		'word_count',
		'user_id',
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
	 * @return array
	 */
	protected function rules() : array
	{
		return [
			'attributes.title' => 'required',
			'relationships.user' => 'required',
		];
	}

	/**
	 * @return array
	 */
	public function multiRelationships() : array
	{
		return ['comments', 'tags'];
	}

	/**
	 * @return array
	 */
	public function singularRelationships() : array
	{
		return ['user'];
	}

	/**
	 * @return HasMany
	 */
	public function comments() : HasMany
	{
		return $this->hasMany(Comment::class);
	}

	/**
	 * @return BelongsToMany
	 */
	public function tags() : BelongsToMany
	{
		return $this->belongsToMany(Tag::class);
	}

	/**
	 * @return BelongsTo
	 */
	public function user() : BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
