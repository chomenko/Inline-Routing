<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting\Services;

use Nette\Application\IPresenter;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Nette\Loaders\RobotLoader;

class Loader implements ILoader
{

	const CACHE_NAMESPACE = "InlineRouting.Presenters";

	/**
	 * @var Cache
	 */
	private $cache;

	/**
	 * @var AnnotationReader
	 */
	private $annotationReader;

	/**
	 * @var RobotLoader
	 */
	private $robotLoader;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var array
	 */
	private $presenters = [];

	/**
	 * Loader constructor.
	 * @param Config $config
	 * @param IStorage $storage
	 * @throws AnnotationException
	 */
	public function __construct(Config $config, IStorage $storage)
	{
		$this->cache = new Cache($storage, self::CACHE_NAMESPACE);
		$this->annotationReader = new AnnotationReader();
		$this->robotLoader = new RobotLoader();
		$this->robotLoader->setTempDirectory($config->getTempDir() . "/Inline.Loader");
		$this->robotLoader->setCacheStorage($storage);
		$this->robotLoader->setAutoRefresh(FALSE);

		foreach ($config->getLoadPaths() as $path) {
			$this->robotLoader->addDirectory($path);
		}

		$this->config = $config;
	}

	/**
	 * @throws \ReflectionException
	 * @throws \Throwable
	 */
	public function initialize(): void
	{
		$presenters = $this->cache->load("presenters", []);
		if (!$presenters) {
			$presenters = [];
			if ($this->config->isEnableLoadDeclareClasses()) {
				$presenters = $this->loadDeclareClasses($presenters);
			}
			if ($this->config->isEnableLoadRobotLoader()) {
				$presenters = $this->loadRobotLoader($presenters);
			}
			$presenters = $this->cache->save("presenters", $presenters);
		}

		$this->presenters = $presenters;
	}

	/**
	 * @param array $presenters
	 * @return array
	 * @throws \ReflectionException
	 */
	protected function loadRobotLoader(array $presenters = []): array
	{
		$this->robotLoader->register();
		foreach ($this->robotLoader->getIndexedClasses() as $className => $file) {
			if ($this->hasAccessClass($className)) {
				$presenters[$className] = $className;
			}
		}
		return $presenters;
	}

	/**
	 * @param array $presenters
	 * @return array
	 * @throws \ReflectionException
	 */
	protected function loadDeclareClasses(array $presenters = []): array
	{
		foreach (get_declared_classes() as $class) {
			if ($this->hasAccessClass($class)) {
				$presenters[$class] = $class;
			}
		}
		return $presenters;
	}

	/**
	 * @param string $class
	 * @return bool
	 * @throws \ReflectionException
	 */
	protected function hasAccessClass(string $class): bool
	{
		try {
			if (!in_array(IPresenter::class, class_implements($class))) {
				return FALSE;
			}

			$reflect = new \ReflectionClass($class);

			if ($reflect->isAbstract()) {
				return FALSE;
			}
			if ($reflect->isInterface()) {
				return FALSE;
			}
		} catch (\ReflectionException $e) {
			if ($e->getCode() == -1) {
				return FALSE;
			}
			throw $e;
		}
		return TRUE;
	}

	/**
	 * @return array
	 */
	public function getPresenters(): array
	{
		return $this->presenters;
	}

}
