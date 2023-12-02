<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Article;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Tag;
use Jlbelanger\Tapioca\Tests\Dummy\Database\Factories\ArticleTagFactory;
use Jlbelanger\Tapioca\Traits\Resource;

class ArticleTag extends Model
{
	use HasFactory, Resource;

	protected $primaryKey = 'atid';

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
	public function rules() : array
	{
		return [
			'data.relationships.article' => [$this->requiredOnCreate()],
			'data.relationships.tag' => [$this->requiredOnCreate()],
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
