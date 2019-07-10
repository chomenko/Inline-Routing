<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting\DI;

use Chomenko\InlineRouting\Config;
use Chomenko\InlineRouting\Extensions\EntityTransformExtension;
use Chomenko\InlineRouting\Routing;
use Chomenko\InlineRouting\Services;
use Nette\Application\Routers\RouteList;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

class InlineRoutingExtension extends CompilerExtension
{

	private $default = [
		"tempDir" => NULL,
		"loader" => [
			"service" => Services\Loader::class,
			"loadDeclareClasses" => TRUE,
			"loadRobotLoader" => TRUE,
			"in" => []
		],
		"extensions" => [
			EntityTransformExtension::class,
		],
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$this->default["tempDir"] = $builder->parameters["tempDir"];
		$this->default["loader"]["in"][] = $builder->parameters["appDir"];
		$this->default = $this->getConfig($this->default);

		$builder->addDefinition($this->prefix('config'))
			->setFactory(Services\Config::class, [$this->default])
			->setAutowired(TRUE);

		$loader = $builder->addDefinition($this->prefix('loader'))
			->setFactory($this->default["loader"]["service"])
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('routing'))
			->setFactory(Routing::class, ["loader" => $loader])
			->addSetup('initialize')
			->setAutowired(TRUE);

		foreach ($this->default["extensions"] as $i => $extension) {
			$builder->addDefinition($this->prefix('extension.' . $i))
				->setFactory($extension)
				->setAutowired(TRUE);
		}

		$builder->addDefinition($this->prefix('router'))
			->setFactory(Services\Router::class)
			->setAutowired(FALSE);
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$builder->getDefinitionByType(RouteList::class)
			->addSetup('prepend', [$this->prefix('@router')]);
	}

	/**
	 * @param Configurator $configurator
	 */
	public static function register(Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('inlineRouting', new InlineRoutingExtension());
		};
	}

}
