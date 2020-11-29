<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Article;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Tag;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\Database\Factories\ArticleTagFactory;
use Jlbelanger\LaravelJsonApi\Traits\Resource;

class ArticleTag extends Model
{
	use HasFactory, Resource;

	protected $table = 'article_tag';

	protected $fillable = [
		'article_id',
		'tag_id',
	];

	/**
	 * Creates a new factory instance for the model.
	 *
	 * @return Factory
	 */
	protected static function newFactory() : Factory
	{
		return ArticleTagFactory::new();
	}

	/**
	 * @return array
	 */
	protected function rules() : array
	{
		return [
			'relationships.article' => 'required',
			'relationships.tag' => 'required',
		];
	}

	/**
	 * @return array
	 */
	public function singularRelationships() : array
	{
		return ['article', 'tag'];
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
	public function tag() : BelongsTo
	{
		return $this->belongsTo(Tag::class);
	}
}
