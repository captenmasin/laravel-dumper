<?php

namespace Captenmasin\LaravelDumper\Tests;

use Captenmasin\LaravelDumper\Support\Key;
use Captenmasin\LaravelDumper\Support\Properties;
use Illuminate\Support\Collection;
use Symfony\Component\VarDumper\Caster\CutStub;

class PropertiesTest extends TestCase
{
	protected Properties $parameters;
	
	protected function setUp(): void
	{
		parent::setUp();
		
		$this->parameters = new Properties([
			Key::protected('protected') => 1,
			Key::virtual('virtual') => 1,
			Key::dynamic('dynamic') => 1,
			'prefix_b' => 1,
			'prefix_a' => 1,
			'b_suffix' => 1,
			'a_suffix' => 1,
			'other' => 1,
		]);
	}
	
	public function test_reordering_parameters(): void
	{
		$rules = [
			'prefix_*',
			'dynamic',
			'virtual',
			'*',
			'protected',
			'*_suffix',
		];
		
		$reordered = $this->parameters->reorder($rules)->all();
		
		$this->assertEquals([
			'prefix_b' => 1,
			'prefix_a' => 1,
			Key::dynamic('dynamic') => 1,
			Key::virtual('virtual') => 1,
			'other' => 1,
			Key::protected('protected') => 1,
			'b_suffix' => 1,
			'a_suffix' => 1,
		], $reordered);
	}
	
	public function test_only_keeping_specific_parameters(): void
	{
		$subset = $this->parameters->only(['dynamic', '*_suffix'])->all();
		
		$this->assertEquals([
			Key::dynamic('dynamic') => 1,
			'b_suffix' => 1,
			'a_suffix' => 1,
		], $subset);
	}
	
	public function test_excluding_specific_parameters(): void
	{
		$subset = $this->parameters->except(['dynamic', '*_suffix'])->all();
		
		$this->assertEquals([
			Key::protected('protected') => 1,
			Key::virtual('virtual') => 1,
			'prefix_b' => 1,
			'prefix_a' => 1,
			'other' => 1,
		], $subset);
	}
	
	public function test_has_method(): void
	{
		$this->assertTrue($this->parameters->has('protected'));
		$this->assertTrue($this->parameters->has(Key::protected('protected')));
		$this->assertTrue($this->parameters->has(Key::protected('protected'), 'prefix_b'));
		$this->assertTrue($this->parameters->has([Key::protected('protected'), 'prefix_b']));
		
		$this->assertFalse($this->parameters->has(Key::virtual('protected')));
		$this->assertFalse($this->parameters->has(Key::protected('protected'), 'foo'));
		$this->assertFalse($this->parameters->has([Key::protected('protected'), 'foo']));
		
		$this->assertTrue($this->parameters->hasAny(Key::protected('protected'), 'foo'));
		$this->assertTrue($this->parameters->hasAny([Key::protected('protected'), 'foo']));
	}
	
	public function test_get_methods(): void
	{
		$this->assertEquals(1, $this->parameters->getProtected('protected'));
		$this->assertNull($this->parameters->getProtected('virtual'));
		
		$this->assertEquals(1, $this->parameters->getVirtual('virtual'));
		$this->assertNull($this->parameters->getVirtual('protected'));
		
		$this->assertEquals(1, $this->parameters->getDynamic('dynamic'));
		$this->assertNull($this->parameters->getDynamic('protected'));
		
		$this->assertEquals(1, $this->parameters->get('protected'));
		$this->assertEquals(1, $this->parameters->get('virtual'));
		$this->assertEquals(1, $this->parameters->get('dynamic'));
		$this->assertEquals(1, $this->parameters->get('prefix_b'));
		$this->assertNull($this->parameters->get('missing param'));
	}
	
	public function test_cut_methods(): void
	{
		$this->assertEquals(1, $this->parameters->cutProtected('protected')->value);
		$this->assertNull($this->parameters->cutProtected('virtual')->value);
		
		$this->assertEquals(1, $this->parameters->cutVirtual('virtual')->value);
		$this->assertNull($this->parameters->cutVirtual('protected')->value);
		
		$this->assertEquals(1, $this->parameters->cutDynamic('dynamic')->value);
		$this->assertNull($this->parameters->cutDynamic('protected')->value);
		
		$this->assertEquals(1, $this->parameters->cut('protected')->value);
		$this->assertEquals(1, $this->parameters->cut('virtual')->value);
		$this->assertEquals(1, $this->parameters->cut('dynamic')->value);
		$this->assertEquals(1, $this->parameters->cut('prefix_b')->value);
		$this->assertNull($this->parameters->cut('missing param')->value);
	}
	
	public function test_copy_methods(): void
	{
		$destination = new Properties();
		
		$destination->copyProtected('protected', $this->parameters);
		$destination->copyVirtual('virtual', $this->parameters);
		$destination->copyDynamic('dynamic', $this->parameters);
		$destination->copy('prefix_b', $this->parameters);
		
		$this->assertEquals(1, $destination->get('protected'));
		$this->assertEquals(1, $destination->get('virtual'));
		$this->assertEquals(1, $destination->get('dynamic'));
		$this->assertEquals(1, $destination->get('prefix_b'));
	}
	
	public function test_copy_and_cut_methods(): void
	{
		$destination = new Properties();
		
		$destination->copyAndCutProtected('protected', $this->parameters);
		$destination->copyAndCutVirtual('virtual', $this->parameters);
		$destination->copyAndCutDynamic('dynamic', $this->parameters);
		
		$this->assertInstanceOf(CutStub::class, $destination->get('protected'));
		$this->assertInstanceOf(CutStub::class, $destination->get('virtual'));
		$this->assertInstanceOf(CutStub::class, $destination->get('dynamic'));
		
		$this->assertEquals(1, $destination->get('protected')->value);
		$this->assertEquals(1, $destination->get('virtual')->value);
		$this->assertEquals(1, $destination->get('dynamic')->value);
	}
	
	public function test_default_filtering(): void
	{
		$properties = new Properties([
			'null' => null,
			'empty array' => [],
			'empty collection' => new Collection(),
			'false' => false,
			'value' => 1,
		]);
		
		$this->assertEquals(['false' => false, 'value' => 1], $properties->filter()->all());
	}
}
