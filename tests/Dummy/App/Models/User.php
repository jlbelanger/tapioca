<?php

namespace Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\App\Models\Article;
use Jlbelanger\LaravelJsonApi\Tests\Dummy\Database\Factories\UserFactory;
use Jlbelanger\LaravelJsonApi\Traits\Resource;

class User extends Model
{
	use HasFactory, Resource;

	protected $fillable = [
		'username',
		'email',
	];

	/**
	 * Creates a new factory instance for the model.
	 *
	 * @return Factory
	 */
	protected static function newFactory() : Factory
	{
		return UserFactory::new();
	}

	/**
	 * @return array
	 */
	protected function rules() : array
	{
		return [
			'attributes.username' => 'required',
			'attributes.email' => 'required|email',
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
	 * @return HasMany
	 */
	public function articles() : HasMany
	{
		return $this->hasMany(Article::class);
	}
}
