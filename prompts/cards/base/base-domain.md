# 领域层规范卡片

> **卡片 ID**: `base-domain`
> **优先级**: L0
> **依赖**: `base-naming`, `base-structure`

---

## DTO 规范

```php
<?php

namespace App\Domains\{{domain}}\Data;

use Illuminate\Contracts\Support\Arrayable;

readonly class {{entity}}CreateData implements Arrayable
{
    public function __construct(
        public int ${{primary_field}},
        public string ${{field_2}},
        // ...
    ) {}
    
    public static function fromArray(array $data): self
    {
        return new self(
            {{primary_field}}: (int) $data['{{field_name_1}}'],
            {{field_2}}: $data['{{field_name_2}}'],
        );
    }
    
    public function toArray(): array
    {
        return [
            '{{field_name_1}}' => $this->{{primary_field}},
            '{{field_name_2}}' => $this->{{field_2}},
        ];
    }
}
```

## Service 规范

```php
<?php

namespace App\Domains\{{domain}}\Services;

use App\Domains\{{domain}}\Data\{{entity}}CreateData;
use {{model_path}};
use Illuminate\Support\Facades\DB;

class {{entity}}Service
{
    public function create({{entity}}CreateData $data): {{model_class}}
    {
        return DB::transaction(function () use ($data) {
            ${{entity_snake}} = {{model_class}}::create($data->toArray());
            // 业务逻辑
            return $${{entity_snake}};
        });
    }
    
    public function update({{model_class}} ${{entity_snake}}, array $data): {{model_class}}
    {
        return DB::transaction(function () use (${{entity_snake}}, $data) {
            $${{entity_snake}}->update($data);
            return $${{entity_snake}};
        });
    }
}
```

## 枚举规范

```php
<?php

namespace App\Domains\{{domain}}\Enums;

enum {{entity}}Status: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => '激活',
            self::INACTIVE => '未激活',
        };
    }
    
    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'danger',
        };
    }
}
```

## 事件规范

```php
<?php

namespace App\Domains\{{domain}}\Events;

use {{model_path}};
use Illuminate\Foundation\Events\Dispatchable;

class {{entity}}Created
{
    use Dispatchable;
    
    public function __construct(
        public {{model_class}} ${{entity_snake}}
    ) {}
}
```
