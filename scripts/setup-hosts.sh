#!/bin/bash
# setup-hosts.sh
# 配置本地域名映射

echo "🔧 配置本地域名映射..."

# 检查是否有 sudo 权限
if ! sudo -n true 2>/dev/null; then
    echo "⚠️  需要 sudo 权限来修改 /etc/hosts 文件"
    echo "请运行: sudo bash $0"
    exit 1
fi

# 备份 hosts 文件
sudo cp /etc/hosts /etc/hosts.backup.$(date +%Y%m%d)

# 添加域名映射
echo "127.0.0.1 shop.example.com admin.example.com hr.example.com super.example.com" | sudo tee -a /etc/hosts

echo "✅ 域名映射配置完成！"
echo ""
echo "📋 域名映射："
echo "   shop.example.com → 商城前端"
echo "   admin.example.com → 管理后台"
echo "   hr.example.com → 薪福通"
echo "   super.example.com → 权限管理"
