# 验证规范卡片

> **卡片 ID**: `base-validation`
> **优先级**: L0
> **依赖**: `base-naming`

---

## FormRequest 模板

```php
<?php

namespace App\Http\Requests\{{domain}};

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class {{entity}}StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            '{{field_1}}' => ['required', 'string', 'max:255', Rule::unique('{{table_name}}')->ignore($this->{{primary_key}})],
            '{{field_2}}' => ['nullable', 'string', 'max:1000'],
            '{{field_3}}' => ['required', 'integer', 'exists:{{relation_table}},id'],
            '{{field_4}}' => ['required', Rule::in(['value1', 'value2', 'value3'])],
            '{{field_5}}' => ['nullable', 'url'],
        ];
    }
    
    public function messages(): array
    {
        return [
            '{{field_1}}.required' => '{{field_label_1}}是必填项',
            '{{field_1}}.unique' => '{{field_label_1}}已存在',
            '{{field_3}}.exists' => '{{field_label_3}}不存在',
        ];
    }
}
```

## 验证规则速查

| 规则 | 说明 | 示例 |
|------|------|------|
| `required` | 必填 | `->required()` |
| `string` | 字符串 | `->string()->max(255)` |
| `integer` | 整数 | `->integer()` |
| `numeric` | 数字 | `->numeric()->min(0)` |
| `email` | 邮箱 | `->email()` |
| `url` | URL | `->url()` |
| `boolean` | 布尔 | `->boolean()` |
| `array` | 数组 | `->array()` |
| `unique` | 唯一 | `Rule::unique('table')->ignore($id)` |
| `exists` | 存在 | `Rule::exists('table', 'id')` |
| `in` | 枚举值 | `Rule::in(['a', 'b', 'c'])` |
| `date` | 日期 | `->date()` |
| `json` | JSON | `->json()` |
| `file` | 文件 | `->file()->max(5120)` |
| `image` | 图片 | `->image()->maxSize(2048)` |
