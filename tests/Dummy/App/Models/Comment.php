<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Article;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\User;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\Database\Factories\CommentFactory;
use Jlbelanger\LaravelJsonApi\Traits\Resource;

class Comment extends Model
{
	use HasFactory, Resource;

	protected $fillable = [
		'content',
	];

	/**
	 * Creates a new factory instance for the model.
	 *
	 * @return Factory
	 */
	protected static function newFactory() : Factory
	{
		return CommentFactory::new();
	}

	/**
	 * @return array
	 */
	protected function rules() : array
	{
		return [
			'attributes.content' => 'required',
			'relationships.article' => 'required',
			'relationships.user' => 'required',
		];
	}

	/**
	 * @return array
	 */
	public function singularRelationships() : array
	{
		return ['article', 'user'];
	}

	/**
	 * @return BelongsTo
	 */
	public function article() : BelongsTo
	{
		return $this->belongsTo(Article::class);
	}

	/**
	 * @return BelongsTo
	 */
	public function user() : BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
