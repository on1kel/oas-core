# on1kel/oas-core

**OpenAPI core для PHP**: неизменяемые (immutable) модели OAS 3.x, быстрый парсер с поддержкой `$ref`, сериализация/денормализация, обход дерева (visitor/walker), профили версий и базовые правила валидации.

> Пакет предоставляет ядро, общее для разных версий спецификации OpenAPI 3.x. Поддержка различий версии (например, 3.1 vs 3.2) вынесена в **профили** (`SpecProfile`).

---


## Возможности

- 🧩 **Модели OpenAPI**: типизированные value-объекты (например, `On1kel\\OAS\\Core\\Model\\OpenApiDocument`, `Info`, `PathItem`, `Components` и т.д.).
- 📥 **Парсинг**: преобразование массива/JSON в объектную модель, включая **разрешение `$ref`** (внутренних и внешних).
- 📤 **Сериализация**: обратная операция — модель → массив/JSON.
- 🧭 **Определение версии**: извлечение `openapi: "MAJOR.MINOR[.patch]"` и выбор соответствующего профиля.
- 🚶 **Обход дерева**: `Traverse\\Walker` + `NodeVisitor` с JSON Pointer путями каждого узла.
- ✅ **Валидация**: базовый валидатор с набором правил (например, `UniqueOperationIdRule`, `ReferenceNoSiblingsRule`, и др.).
- 🔌 **PSR-совместимость**: использует `psr/http-client` и `psr/http-factory` для загрузки внешних `$ref`.
- 🧱 **Иммутабельность**: модели неизменяемы.

---


## Установка

```bash
composer require on1kel/oas-core
```

**Требования**: PHP ^8.2, расширения `ext-json`, `ext-ctype`. Зависимости времени выполнения: `psr/http-client`, `psr/http-factory`.

Лицензия: MIT.

---

## Быстрый старт

### 1) Профили версий

Ядро опирается на интерфейс профиля `On1kel\\OAS\\Contract\\Profile\\SpecProfile`. Реализации профилей (например, для OAS 3.1/3.2) могут поставляться отдельными пакетами вашего проекта. Ниже — **минимальный пример профиля** для демонстрации (под нужды проекта реализуйте полноценную версию):

```php
<?php
declare(strict_types=1);

use On1kel\OAS\Contract\Profile\SpecProfile;
use On1kel\OAS\Contract\Profile\FeatureSet;
use On1kel\OAS\Contract\Profile\Enum\Strictness;
use On1kel\OAS\Contract\Validation\NodeValidator;

// Простейшая реализация профиля OAS "3.1" (для примера).
final class OAS31Profile implements SpecProfile
{
    public function id(): string { return '3.1'; }

    public function allowExternalRefs(): bool { return true; }

    public function strictness(): Strictness { return Strictness::Strict; }

    public function normalizeKey(string $nodeType, string $key): string
    {
        // При необходимости приводите ключи к каноническому виду.
        return $key;
    }

    public function features(): FeatureSet
    {
        // Верните набор фич-флагов, если используете.
        return new FeatureSet();
    }

    /** @return array<int, NodeValidator> */
    public function extraValidators(): array
    {
        // Дополнительные валидаторы, специфичные для профиля.
        return [];
    }
}
```

Зарегистрируйте профиль и выберите его по содержимому документа:

```php
use On1kel\OAS\Version\ProfileRegistry;
use On1kel\OAS\Version\VersionDetector;

$profiles = (new ProfileRegistry())
    ->register(new OAS31Profile());           // можно регистрировать несколько профилей
$detector = new VersionDetector($profiles);

$raw = json_decode(file_get_contents('openapi.json'), true, 512, JSON_THROW_ON_ERROR);
$profile = $detector->detect($raw);           // вернёт зарегистрированный профиль по полю "openapi"
```

### 2) Разрешение `$ref` и парсинг

```php
use On1kel\OAS\Parsing\DocumentParser;
use On1kel\OAS\Ref\DefaultRefFetcher;
use On1kel\OAS\Ref\RefResolver;
use On1kel\OAS\Version\ParseOptions;

// PSR-клиент и фабрики (реализацию предоставьте сами: Guzzle, Symfony HTTP Client и т.п.)
$httpClient = /* Psr\Http\Client\ClientInterface */;
$requestFactory = /* Psr\Http\Message\RequestFactoryInterface */;
$streamFactory = /* Psr\Http\Message\StreamFactoryInterface */;

$fetcher   = new DefaultRefFetcher($httpClient, $requestFactory, $streamFactory);
$resolver  = new RefResolver($fetcher);
$parser    = new DocumentParser($resolver);

$options = (new ParseOptions())
    ->withStrictness($profile->strictness())  // Strict | Lenient
    ->withResolveExternalRefs(true)           // разрешать внешние $ref
    ->withMaxRefDepth(64);                    // защита от циклов/глубины

$baseUri  = 'file://' . realpath('openapi.json'); // базовый URI документа
$document = $parser->parse($raw, $profile, $options, $baseUri);
// $document — экземпляр On1kel\OAS\Model\OpenApiDocument
```

### 3) Сериализация модели → массив/JSON

```php
use On1kel\OAS\Serialize\DefaultSerializer;
use On1kel\OAS\Serialize\DefaultNormalizer;
use On1kel\OAS\Serialize\DefaultDenormalizer;
use On1kel\OAS\Serialize\TypeRegistry;

// Реестр фабрик типов для денормализации (если потребуется собирать модель «с нуля»)
$registry     = new TypeRegistry();
// $registry->register('OpenApiDocument', fn(array $data, $profile) => /* вернуть new OpenApiDocument(...) */);

$serializer   = new DefaultSerializer(
    normalizers: [new DefaultNormalizer()],   // можно добавить свои нормализаторы
    denormalizer: new DefaultDenormalizer($registry)
);

$array = $serializer->toArray($document, $profile);
$json  = $serializer->toJson($document, $profile, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Обратные операции (если у вас есть массив/JSON и реалистичная регистрация типов в $registry):
// $doc2 = $serializer->fromArray($array, $profile);
// $doc3 = $serializer->fromJson($jsonString, $profile);
```

### 4) Обход дерева (Walker + Visitor)

```php
use On1kel\OAS\Traverse\Walker;
use On1kel\OAS\Contract\Traverse\NodeVisitor;

final class PrintVisitor implements NodeVisitor
{
    public function enter(string $pointer, object $node): void
    {
        echo "→ {$pointer} : " . get_class($node) . PHP_EOL;
    }

    public function leave(string $pointer, object $node): void {}
}

$walker = new Walker([new PrintVisitor()]);
$walker->walk($document); // Пройдётся по всем узлам, pointer — JSON Pointer узла
```

### 5) Валидация

```php
use On1kel\OAS\Validation\CompositeValidator;
use On1kel\OAS\Validation\Rule\UniqueOperationIdRule;
use On1kel\OAS\Validation\Rule\ReferenceNoSiblingsRule;
// ... другие правила в пространстве имён On1kel\OAS\Validation\Rule

$validator = new CompositeValidator([
    new UniqueOperationIdRule(),
    new ReferenceNoSiblingsRule(),
    // добавляйте нужные правила
]);

$violations = $validator->validate($document, $profile, $profile->strictness(), $baseUri);
foreach ($violations as $v) {
    // Объект/структура нарушения зависит от реализации правил; обработайте под свои нужды.
}
```

---

## Архитектура (обзор)

- **Модели** (`On1kel\OAS\Model\*`): строгие неизменяемые классы, отражающие структуру OpenAPI.
- **Парсинг** (`Parsing\DocumentParser`): преобразует массив → модели; разрешает `$ref` через `RefResolver`.
- **Ссылки** (`Ref\*`): `DefaultRefFetcher` грузит внешние ресурсы по PSR-клиенту; `RefResolver` управляет стеком, глубиной и циклами.
- **Сериализация** (`Serialize\*`): `DefaultNormalizer` и `DefaultSerializer` преобразуют модели обратно.
- **Профили** (`Contract\Profile\*`, `Version\*`): `SpecProfile`, `ProfileRegistry`, `VersionDetector`, `ParseOptions`.
- **Обход** (`Traverse\Walker`, `Contract\Traverse\NodeVisitor`): DFS-проход с JSON Pointer.
- **Валидация** (`Validation\*`): композиция правил, зависящих от профиля и строгого/мягкого режима (`Strictness`).

---

## Скрипты разработчика

Доступны composer-скрипты:

- `composer test` — PHPUnit ^11.2  
- `composer phpstan` — статический анализ на максимальном уровне  
- `composer cs:check` / `composer cs:fix` — PHP CS Fixer  
- `composer infection` — мутационное тестирование (порог MSI настраивается скриптом)

---

## Пример: чтение из файла и полный цикл

```php
<?php
require __DIR__ . '/vendor/autoload.php';

// 1) Сырые данные
$raw = json_decode(file_get_contents('openapi.json'), true, 512, JSON_THROW_ON_ERROR);

// 2) Профиль и определение версии
$profiles = (new On1kel\OAS\Version\ProfileRegistry())
    ->register(new OAS31Profile());
$profile  = (new On1kel\OAS\Version\VersionDetector($profiles))->detect($raw);

// 3) Парсинг с $ref
$http    = /* PSR-18 клиент */;
$reqFac  = /* PSR-17 RequestFactory */;
$strFac  = /* PSR-17 StreamFactory */;
$parser  = new On1kel\OAS\Parsing\DocumentParser(
    new On1kel\OAS\Ref\RefResolver(
        new On1kel\OAS\Ref\DefaultRefFetcher($http, $reqFac, $strFac)
    )
);
$opts    = (new On1kel\OAS\Version\ParseOptions())
    ->withResolveExternalRefs(true)
    ->withMaxRefDepth(64);
$baseUri = 'file://' . realpath('openapi.json');

$doc = $parser->parse($raw, $profile, $opts, $baseUri);

// 4) Сериализация и обход
$serializer = new On1kel\OAS\Serialize\DefaultSerializer([new On1kel\OAS\Serialize\DefaultNormalizer()], new On1kel\OAS\Serialize\DefaultDenormalizer(new On1kel\OAS\Serialize\TypeRegistry()));
echo $serializer->toJson($doc, $profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;

$walker = new On1kel\OAS\Traverse\Walker([new class implements On1kel\OAS\Contract\Traverse\NodeVisitor {
    public function enter(string $p, object $n): void { echo $p, PHP_EOL; }
    public function leave(string $p, object $n): void {}
}]);
$walker->walk($doc);
```

---

## Полезные классы (быстрые ссылки)

- `On1kel\OAS\Model\OpenApiDocument` — корневой объект.
- `On1kel\OAS\Parsing\DocumentParser` — парсинг и `$ref`.
- `On1kel\OAS\Ref\RefResolver`, `On1kel\OAS\Ref\DefaultRefFetcher` — разрешение ссылок, загрузка по URI.
- `On1kel\OAS\Serialize\DefaultSerializer`, `DefaultNormalizer`, `DefaultDenormalizer`, `TypeRegistry` — сериализация/денормализация.
- `On1kel\OAS\Version\ProfileRegistry`, `VersionDetector`, `ParseOptions` — профили и выбор версии.
- `On1kel\OAS\Contract\Traverse\NodeVisitor`, `Traverse\Walker` — обход дерева.
- `On1kel\OAS\Validation\CompositeValidator` и правила в `Validation\Rule\*` — валидация.

---

## Ограничения и заметки

- Реализации `SpecProfile` умышленно отделены от ядра: подключайте собственные профили или сторонние пакеты под нужные версии OAS.
- Для внешних `$ref` требуется доступный PSR-18 клиент и PSR-17 фабрики.
- Защита от бесконечных циклов `$ref`: глубина управляется через `ParseOptions::withMaxRefDepth(...)` и исключения `Ref\Exception\*`.

---

## Лицензия

MIT © on1kel
