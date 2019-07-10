<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting\Services;

interface ILoader
{

	/**
	 * return void
	 */
	public function initialize(): void;

	/**
	 * @return array
	 */
	public function getPresenters(): array;

}
