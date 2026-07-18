<?php

/**
 * 本地 CI 检查脚本
 *
 * 用法:
 *   php ci-tools/ci-check.php              # 语法 + Pint (当前分支相对 origin 的改动)
 *   php ci-tools/ci-check.php --all        # 整个项目
 *   php ci-tools/ci-check.php --stan       # 额外跑 PHPStan
 *   php ci-tools/ci-check.php --no-pint    # 跳过 Pint
 */
$projectDir = dirname(__DIR__);
$ciToolsDir = __DIR__;
$args = array_slice($argv, 1);

$runAll = in_array('--all', $args);
$runStan = in_array('--stan', $args);
$noPint = in_array('--no-pint', $args);

$hasError = false;

echo "========================================\n";
echo "  Laravel Filament CI 检查\n";
echo "========================================\n\n";

// 1. 语法检查
echo "[1/3] PHP 语法检查...\n";
$phpFiles = $runAll
    ? shell_exec("find {$projectDir}/app {$projectDir}/database {$projectDir}/routes -name '*.php' -not -path '*/vendor/*'")
    : shell_exec("cd {$projectDir} && git diff --name-only --cached HEAD | grep '\\.php$' || git diff --name-only HEAD | grep '\\.php$'");

$files = array_filter(explode("\n", trim($phpFiles)));
$syntaxErrors = [];

foreach ($files as $file) {
    $file = trim($file);
    if (empty($file) || ! file_exists($projectDir.'/'.$file)) {
        continue;
    }
    $output = shell_exec("php -l {$projectDir}/{$file} 2>&1");
    if (strpos($output, 'Parse error') !== false || strpos($output, 'Fatal error') !== false) {
        $syntaxErrors[] = $file;
        echo "  ❌ {$file}\n";
    }
}

if (empty($syntaxErrors)) {
    echo '  ✅ 语法检查通过 ('.count($files)." 个文件)\n\n";
} else {
    echo '  ❌ '.count($syntaxErrors)." 个文件有语法错误\n\n";
    $hasError = true;
}

// 2. Pint 代码风格检查
if (! $noPint) {
    echo "[2/3] Pint 代码风格检查...\n";
    chdir($projectDir);
    exec('./vendor/bin/pint --test 2>&1', $pintOutput, $pintCode);

    if ($pintCode === 0) {
        echo "  ✅ Pint 检查通过\n\n";
    } else {
        echo "  ❌ Pint 检查失败，请运行: ./vendor/bin/pint\n\n";
        $hasError = true;
    }
} else {
    echo "[2/3] Pint 检查已跳过\n\n";
}

// 3. PHPStan (可选)
if ($runStan) {
    echo "[3/3] PHPStan 静态分析...\n";
    $stanConfig = $ciToolsDir.'/phpstan.neon';
    exec("cd {$projectDir} && php -d memory_limit=256M ./vendor/bin/phpstan analyse -c {$stanConfig} --no-progress 2>&1", $stanOutput, $stanCode);

    if ($stanCode === 0) {
        echo "  ✅ PHPStan 检查通过\n\n";
    } else {
        echo "  ⚠️ PHPStan 有警告\n\n";
        // PHPStan 警告不阻塞
    }
} else {
    echo "[3/3] PHPStan 已跳过 (使用 --stan 启用)\n\n";
}

// 结果
echo "========================================\n";
if ($hasError) {
    echo "  ❌ CI 检查失败，请修复后重试\n";
    echo "  提示: git push --no-verify 可跳过检查\n";
    echo "========================================\n";
    exit(1);
} else {
    echo "  ✅ CI 检查通过\n";
    echo "========================================\n";
    exit(0);
}
