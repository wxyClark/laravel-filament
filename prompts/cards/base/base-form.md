# 表单通用卡片

> **卡片 ID**: `base-form`
> **优先级**: L0
> **依赖**: `base-naming`

---

## 字段类型映射

| 类型 | Filament 字段 | 配置示例 |
|------|-------------|---------|
| 文本 | `TextInput` | `->required()->maxLength(255)->unique(ignoreRecord: true)` |
| 邮箱 | `TextInput->email()` | `->email()->unique(ignoreRecord: true)` |
| 电话 | `TextInput->tel()` | `->tel()->regex('/^1[3-9]\d{9}$/')` |
| 数字 | `TextInput->numeric()` | `->numeric()->minValue(0)` |
| 价格 | `TextInput->money()` | `->money('CNY', decimalPlaces: 2)->required()` |
| 整数 | `IntegerInput` | `->minValue(1)->default(1)` |
| 选择 | `Select` | `->options([...])->required()` |
| 单选 | `RadioGroup` | `->options(Status::class)->required()` |
| 多选 | `Select->multiple()` | `->multiple()->searchable()->options([...])` |
| 布尔 | `Toggle` | `->default(true)->label('启用')` |
| 日期 | `DatePicker` | `->native(false)->required()` |
| 时间 | `TimePicker` | `->native(false)` |
| 日期时间 | `DateTimePicker` | `->native(false)` |
| 富文本 | `RichEditor` | `->toolbarItems(['bold','italic','link'])->columnSpanFull()` |
| 文本域 | `Textarea` | `->maxLength(2000)->columnSpanFull()` |
| 文件 | `FileUpload` | `->directory('uploads')->maxSize(5120)->image()` |
| 关联选择 | `Select->relationship()` | `->relationship('relation', 'display_field')->required()` |

## 表单分组模板

```php
public static function form(Form $form): Form
{
    return $form->schema([
        Section::make('基本信息')
            ->schema([
                {{basic_fields}}
            ]),
        Section::make('状态设置')
            ->schema([
                {{status_fields}}
            ]),
        Section::make('备注信息')
            ->schema([
                {{notes_fields}}
            ]),
    ]);
}
```

## 字段配置规则

1. 所有必填字段标注 `->required()`
2. 唯一字段标注 `->unique(ignoreRecord: true)`
3. 关联字段使用 `->relationship('relation', 'display_field')`
4. 金额字段使用 `->money('CNY', decimalPlaces: 2)`
5. 富文本/备注使用 `->columnSpanFull()`
6. 日期/时间选择器使用 `->native(false)` 使用 Filament 原生组件
