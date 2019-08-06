<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting\Extensions;

use Chomenko\InlineRouting\Extension;
use Chomenko\InlineRouting\IAnnotationExtension;
use Chomenko\InlineRouting\Arguments;
use Chomenko\InlineRouting\Exceptions\RouteException;
use Chomenko\InlineRouting\Inline\EntityTransform;
use Chomenko\InlineRouting\Route;
use Chomenko\InlineRouting\Exceptions\Attribute;
use Chomenko\InlineRouting\Exceptions\BadRequestException;
use Doctrine\ORM\EntityManager;
use Nette\Application\UI\Presenter;

class EntityTransformExtension extends Extension
{

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	/**
	 * EntityTransformExtension constructor.
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	/**
	 * @param Presenter $presenter
	 * @param Route $route
	 * @param IAnnotationExtension $annotation
	 * @param array $parameters
	 * @param Arguments $arguments
	 * @param \ReflectionMethod $method
	 * @return mixed|void
	 * @throws BadRequestException
	 * @throws RouteException
	 */
	public function invoke(Presenter $presenter, Route $route, IAnnotationExtension $annotation, array $parameters, Arguments $arguments, \ReflectionMethod $method): void
	{
		if (!$annotation instanceof EntityTransform) {
			throw RouteException::entityTransformerMustInstance();
		}

		$parameter = $annotation->getParameter();

		if (!array_key_exists($parameter, $parameters)) {
			throw RouteException::entityTransformerRequireParameter($parameter);
		}

		$value = $parameters[$parameter];
		$entity = $this->entityManager->getRepository($annotation->getClass())->find($value);

		if (!$entity) {
			$parameter = $this->getMethodParameter($parameter, $method);
			if (!$parameter) {
				return;
			}
			if ($parameter->isOptional()) {
				return;
			}
			if ($parameter->hasType()) {
				if ($parameter->getType()->allowsNull() && $value === NULL) {
					return;
				}
			}
			$ex = new BadRequestException($annotation->getErrorMessage(["value" => $value]), $annotation->getErrorCode());
			$ex->setParameter(new Attribute($parameter->getName()));
			throw $ex;
		}
		$arguments->set($parameter, $entity);
	}

}
