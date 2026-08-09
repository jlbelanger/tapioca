<?php

namespace Jlbelanger\Tapioca\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Jlbelanger\Tapioca\Tests\Dummy\App\Models\Tag;
use Jlbelanger\Tapioca\Tests\Dummy\Database\Factories\ArticleFactory;
use Jlbelanger\Tapioca\Traits\Resource;

class Article extends Model
{
	use HasFactory, Resource;

	protected $fillable = [
		'title',
		'content',
		'word_count',
	];

	protected static function newFactory() : Factory
	{
		return ArticleFactory::new();
	}

	public function rules(array $data) : array
	{
		return [
			'data.attributes.title' => [$this->requiredOnCreate()],
			'data.attributes.word_count' => ['integer'],
		];
	}

	public function multiRelationships() : array
	{
		return ['tags'];
	}

	public function tags() : BelongsToMany
	{
		return $this->belongsToMany(Tag::class);
	}
}
