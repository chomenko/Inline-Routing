<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\InlineRouting\Inline;

use Doctrine\Common\Annotations\Annotation;
use Symfony\Component\Routing\Annotation\Route as BaseRoute;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Route extends BaseRoute
{

}
