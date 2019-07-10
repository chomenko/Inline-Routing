<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting;

interface IAnnotationExtension
{

	/**
	 * @return string
	 */
	public function getExtensionService(): string;

}
