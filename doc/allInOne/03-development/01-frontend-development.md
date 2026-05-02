# 🎨 前端开发规范

> **开发阶段** | **Vue 3 + Vite + TailwindCSS** | **组件化开发**

---

## 📋 概述

**技术栈：**
- 框架：Vue 3 (Composition API)
- 构建：Vite
- 样式：TailwindCSS
- 状态管理：Pinia
- 路由：Vue Router

---

## 🎯 目录结构

```
resources/js/
├── components/                 # 通用组件
│   ├── ui/                    # UI 基础组件
│   ├── form/                  # 表单组件
│   └── layout/                # 布局组件
│
├── composables/               # 组合式函数
│   ├── useAuth.ts
│   ├── useApi.ts
│   └── usePagination.ts
│
├── pages/                     # 页面组件
│   ├── auth/
│   ├── products/
│   └── orders/
│
├── stores/                    # 状态管理
│   ├── auth.ts
│   └── cart.ts
│
├── api/                       # API 调用
│   ├── auth.ts
│   ├── products.ts
│   └── orders.ts
│
├── router/                    # 路由配置
│   └── index.ts
│
├── utils/                     # 工具函数
│   ├── format.ts
│   └── validate.ts
│
└── types/                     # 类型定义
    └── index.ts
```

---

## 📐 组件设计规范

### 组件命名

```typescript
// ✅ 正确：PascalCase
ProductCard.vue
CartItem.vue
OrderList.vue

// ❌ 错误：其他命名方式
productCard.vue
cart-item.vue
```

### 组件结构

```vue
<template>
  <!-- 模板 -->
</template>

<script setup lang="ts">
// 导入
import { ref, computed } from 'vue'
import type { Product } from '@/types'

// Props
interface Props {
  product: Product
  showAction?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showAction: true
})

// Emits
const emit = defineEmits<{
  (e: 'add-to-cart', id: number): void
}>()

// 状态
const loading = ref(false)

// 计算属性
const formattedPrice = computed(() => {
  return `¥${props.product.price.toFixed(2)}`
})

// 方法
const handleAddToCart = () => {
  emit('add-to-cart', props.product.id)
}
</script>

<style scoped>
/* 样式 */
</style>
```

---

## 📝 API 调用规范

### API 封装

```typescript
// api/auth.ts
import { useApi } from '@/composables/useApi'

export const authApi = {
  login(data: LoginRequest) {
    return useApi().post('/auth/login', data)
  },
  
  logout() {
    return useApi().post('/auth/logout')
  },
  
  getUser() {
    return useApi().get('/auth/user')
  }
}
```

### 类型定义

```typescript
// types/index.ts
export interface Product {
  id: number
  name: string
  price: number
  status: 'active' | 'inactive'
  created_at: string
}

export interface ApiResponse<T> {
  success: boolean
  data: T
  message?: string
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    per_page: number
    total: number
  }
}
```

---

## 🎨 样式规范

### TailwindCSS 使用

```vue
<template>
  <!-- ✅ 使用 Tailwind 类名 -->
  <div class="flex items-center justify-between p-4 bg-white rounded-lg shadow">
    <h2 class="text-xl font-semibold text-gray-800">{{ title }}</h2>
    <button 
      class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600"
      @click="handleClick"
    >
      操作
    </button>
  </div>
</template>
```

### 响应式设计

```vue
<template>
  <!-- 移动端优先 -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <div v-for="item in items" :key="item.id">
      <!-- 内容 -->
    </div>
  </div>
</template>
```

---

## 📊 性能优化

### 代码分割

```typescript
// router/index.ts
const routes = [
  {
    path: '/products',
    component: () => import('@/pages/products/Index.vue'),
    meta: { title: '商品列表' }
  }
]
```

### 懒加载

```vue
<template>
  <Suspense>
    <template #default>
      <AsyncComponent />
    </template>
    <template #fallback>
      <LoadingSpinner />
    </template>
  </Suspense>
</template>
```

### 虚拟滚动

```vue
<template>
  <RecycleScroller
    :items="largeList"
    :item-size="50"
    key-field="id"
  >
    <template #default="{ item }">
      <div>{{ item.name }}</div>
    </template>
  </RecycleScroller>
</template>
```

---

## 📋 检查清单

- [ ] 组件命名是否规范？
- [ ] 是否使用 TypeScript？
- [ ] 是否使用 Composition API？
- [ ] 是否有类型定义？
- [ ] 是否有错误处理？
- [ ] 是否有加载状态？
- [ ] 是否有响应式设计？
- [ ] 是否有性能优化？

---

**版本**: v1.0 | **更新日期**: 2026-04-30
