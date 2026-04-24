# 安全规范：SQL 注入防护

## 用途说明
防止 SQL 注入攻击，确保数据库查询安全。

## 适用场景
- 用户输入查询
- 动态排序/筛选
- 原生 SQL 查询

## 标准内容块
```markdown
## SQL 注入防护规则

### ✅ 正确做法：参数绑定

#### Eloquent（自动绑定）
```php
// 简单查询
$users = User::where('email', $email)->get();

// 多条件查询
$users = User::where('email', $email)
    ->where('status', 'active')
    ->get();

// 动态条件
$query = User::query();
if ($request->has('status')) {
    $query->where('status', $request->status);
}
$users = $query->get();
```

#### Query Builder（自动绑定）
```php
$users = DB::table('users')
    ->where('email', '=', $email)
    ->where('status', '=', $status)
    ->get();

// 使用参数数组
$users = DB::table('users')
    ->where(function ($query) use ($email, $status) {
        $query->where('email', $email)
              ->where('status', $status);
    })
    ->get();
```

#### 原生查询（手动绑定）
```php
// 位置绑定
$users = DB::select(
    'SELECT * FROM users WHERE email = ? AND status = ?',
    [$email, $status]
);

// 命名绑定
$users = DB::select(
    'SELECT * FROM users WHERE email = :email AND status = :status',
    ['email' => $email, 'status' => $status]
);
```

### ❌ 禁止做法：字符串拼接
```php
// ❌ 危险！禁止使用
$users = DB::select("SELECT * FROM users WHERE email = '$email'");
$users = DB::select("SELECT * FROM users ORDER BY $column");
$users = DB::table('users')->whereRaw("email = '$email'")->get();
```

### 动态排序安全处理
```php
// ✅ 正确：白名单验证
class UserController extends Controller
{
    private const ALLOWED_SORT_COLUMNS = ['id', 'name', 'email', 'created_at'];
    private const ALLOWED_SORT_DIRECTIONS = ['asc', 'desc'];

    public function index(Request $request)
    {
        $column = in_array($request->sort, self::ALLOWED_SORT_COLUMNS) 
            ? $request->sort 
            : 'id';
        
        $direction = in_array($request->direction, self::ALLOWED_SORT_DIRECTIONS) 
            ? $request->direction 
            : 'desc';

        $users = User::orderBy($column, $direction)->paginate();

        return UserResource::collection($users);
    }
}
```

### Filament 筛选器安全
```php
// ✅ 正确：使用 Filament 内置筛选器（自动安全）
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\QueryBuilder;

public static function table(Table $table): Table
{
    return $table
        ->filters([
            SelectFilter::make('status')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'pending' => 'Pending',
                ])
                ->query(function (Builder $query, array $data) {
                    return $query->when($data['value'], function ($q, $value) {
                        $q->where('status', $value);
                    });
                }),
            
            QueryBuilder::make()
                ->constraints([
                    QueryBuilder\Constraints\TextConstraint::make('name'),
                    QueryBuilder\Constraints\DateConstraint::make('created_at'),
                ]),
        ]);
}
```

### 搜索安全处理
```php
// ✅ 正确：使用 LIKE 配合参数绑定
public function search(Request $request)
{
    $keyword = $request->input('q');
    
    $users = User::where(function ($query) use ($keyword) {
        $query->where('name', 'like', "%{$keyword}%")
              ->orWhere('email', 'like', "%{$keyword}%");
    })->get();

    return UserResource::collection($users);
}

// ❌ 错误：直接拼接 LIKE
$users = DB::select("SELECT * FROM users WHERE name LIKE '%{$keyword}%'");
```

### JSON 字段查询
```php
// ✅ 正确：使用 JSON 查询方法
$posts = Post::where('meta->author', $author)->get();
$posts = Post::whereJsonContains('tags', $tag)->get();

// ❌ 错误：直接拼接 JSON 路径
$posts = DB::select("SELECT * FROM posts WHERE meta->'$.author' = '$author'");
```

### 批量操作安全
```php
// ✅ 正确：使用whereIn配合数组
$users = User::whereIn('id', $ids)->get();

// ❌ 错误：手动拼接 IN 子句
$idsString = implode(',', $ids);
$users = DB::select("SELECT * FROM users WHERE id IN ({$idsString})");
```

### 安全审计日志
```php
// 记录可疑的查询尝试
public function suspiciousQueryDetected(Request $request, string $query): void
{
    Log::channel('security')->warning('可疑查询尝试', [
        'user_id' => auth()->id(),
        'ip' => $request->ip(),
        'query' => $query,
        'user_agent' => $request->userAgent(),
    ]);
}
```
```
```
