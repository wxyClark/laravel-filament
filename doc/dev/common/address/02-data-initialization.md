# 地址数据初始化指南

> 从国家统计局获取并导入行政区划数据

---

## 一、数据源

### 1.1 官方数据源

- **来源**: 国家统计局
- **网址**: https://www.stats.gov.cn/sj/tjbz/tjyqhdmhcxhfdm/
- **更新频率**: 每年更新（通常在年底）
- **格式**: HTML 表格下载

### 1.2 数据内容

```
年份/省级代码/省级名称/地级代码/地级名称/县级代码/县级名称/乡级代码/乡级名称/村级代码/村级名称
```

---

## 二、数据导入流程

### 2.1 步骤 1: 准备数据文件

从国家统计局下载最新行政区划数据，转换为 JSON 格式:

```json
[
  {
    "code": "110000",
    "name": "北京市",
    "level": "province",
    "level_num": 2,
    "parent_id": null,
    "pinyin": "beijing"
  },
  {
    "code": "110100",
    "name": "北京市",
    "level": "city",
    "level_num": 3,
    "parent_id": 2,
    "pinyin": "beijing"
  },
  {
    "code": "110101",
    "name": "东城区",
    "level": "district",
    "level_num": 4,
    "parent_id": 4,
    "pinyin": "dongchengqu"
  }
]
```

### 2.2 步骤 2: 创建导入脚本

**文件位置**: `app/Console/Commands/ImportAddressesCommand.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportAddressesCommand extends Command
{
    protected $signature = 'addresses:import {file?}';
    protected $description = '导入行政区划数据';

    public function handle(AddressService $service): int
    {
        $filePath = $this->argument('file') ?? database_path('seeders/addresses.json');
        
        if (!File::exists($filePath)) {
            $this->error("文件不存在: {$filePath}");
            return Command::FAILURE;
        }

        $data = json_decode(File::get($filePath), true);
        
        $count = $service->importAddresses($data);
        
        $this->info("成功导入 {$count} 条地址数据");
        
        return Command::SUCCESS;
    }
}
```

**运行命令**:
```bash
php artisan addresses:import database/seeders/addresses.json
```

### 2.3 步骤 3: 验证数据

```bash
# 检查总数
php artisan tinker
>>> App\Models\Address::count();

# 检查层级分布
>>> App\Models\Address::groupBy('level')->count();
```

---

## 三、数据缓存

### 3.1 缓存配置

**文件位置**: `config/app.php`

```php
'address_cache_ttl' => env('ADDRESS_CACHE_TTL', 3600),
```

**.env**:
```
ADDRESS_CACHE_TTL=3600
```

### 3.2 清除缓存

```bash
# 清除所有地址缓存
php artisan tinker
>>> app(\App\Services\AddressService::class)->clearCache();
```

---

## 四、常见问题

### 4.1 数据不完整怎么办？

- 检查数据文件是否完整
- 检查 parent_id 是否正确关联
- 检查层级是否连续

### 4.2 编码问题？

- 确保数据文件使用 UTF-8 编码
- 数据库字符集使用 utf8mb4

### 4.3 性能问题？

- 增加 Redis 缓存
- 优化数据库索引
- 使用分页查询大量数据
