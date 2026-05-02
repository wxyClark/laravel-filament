# 12. 静态分析

概要：在代码提交前发现潜在问题，提升代码质量与稳定性。

最佳实践要点
- 使用 PHPStan、Psalm 进行静态分析，设定合适的检出等级。
- 集成到 CI/CD 流水线中，阻止高风险变更进入主分支。
- 为核心模块编写类型注解、docblock，提升分析效果。

落地 Demo
```bash
composer require --dev phpstan/phpstan
vendor/bin/phpstan analyse src tests
```
