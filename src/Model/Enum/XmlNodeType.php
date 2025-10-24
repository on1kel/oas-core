<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Enum;

/**
 * Xml Node Type (OAS 3.2)
 *
 * Возможные значения:
 * - element
 * - attribute
 * - text
 * - cdata
 * - none
 *
 * @see https://spec.openapis.org/oas/v3.2.0.html#xml-object
 */
enum XmlNodeType: string
{
    case Element   = 'element';
    case Attribute = 'attribute';
    case Text      = 'text';
    case CData     = 'cdata';
    case None      = 'none';
}
