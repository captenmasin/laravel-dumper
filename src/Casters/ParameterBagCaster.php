<?php

namespace Captenmasin\LaravelDumper\Casters;

use Captenmasin\LaravelDumper\Support\Key;
use Captenmasin\LaravelDumper\Support\Properties;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\VarDumper\Cloner\Stub;

class ParameterBagCaster extends Caster
{
	public static array $targets = [ParameterBag::class];
	
	/**
	 * @param ParameterBag $target
	 * @param \Captenmasin\LaravelDumper\Support\Properties $properties
	 * @param \Symfony\Component\VarDumper\Cloner\Stub $stub
	 * @param bool $is_nested
	 * @param int $filter
	 * @return array
	 */
	public function cast($target, Properties $properties, Stub $stub, bool $is_nested, int $filter = 0): array
	{
		return collect($target->all())
			->mapWithKeys(fn($value, $key) => [Key::virtual($key) => $value])
			->all();
	}
}
