<?php

declare(strict_types=1);

namespace On1kel\OAS\Core\Model\Collections\Map;

use On1kel\OAS\Core\Model\PathItem;
use On1kel\OAS\Core\Model\Reference;

/**
 * Карта вебхуков: name => (PathItem | Reference)
 * Соответствует OAS 3.1/3.2: webhooks: Map<string, Path Item Object | Reference Object>
 * На уровне карты x-* не поддерживаются (расширения — у документа или внутри PathItem).
 */
final class WebhookMap extends BaseMap
{
    protected function validateItem(mixed $value): bool
    {
        return $value instanceof PathItem || $value instanceof Reference;
    }

    protected function typeLabel(): string
    {
        return 'PathItem|Reference';
    }
}
