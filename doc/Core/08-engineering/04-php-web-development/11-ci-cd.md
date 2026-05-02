# 11. CI/CD（持续集成/持续部署）

概要：通过自动化流水线实现快速、可重复的发布与回滚。

最佳实践要点
- 自动化测试、静态分析、单元/集成/端到端测试的完整执行链。
- 走端到端的安全检查、依赖更新检查、代码风格检查。
- 环境分阶段部署（dev/stage/prod），并提供回滚策略。
- 镜像化构建、版本化、可追溯性。

落地 Demo
```yaml
name: PHP Workflow
on: [ push ]
jobs:
  test-and-build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - run: composer install --prefer-dist --no-interaction
      - run: vendor/bin/phpunit
      - run: vendor/bin/phpstan analyse -l 2 src tests
      - name: Build Docker image
        run: |-
          docker build -t my-app:latest .
          docker push my-registry/my-app:latest
```
