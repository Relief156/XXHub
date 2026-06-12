# xxHub - 表情包收集网站

一个简洁、轻量的表情包收集与分享网站，支持图片上传、浏览、搜索和管理功能。

## ✨ 功能特性

- 📦 **图片上传** - 支持 PNG、GIF、JPG、WebP 等格式，自动生成缩略图
- 🔍 **智能搜索** - 支持关键词搜索表情包标题和标签
- 📱 **响应式设计** - 完美适配桌面和移动设备
- 📋 **一键复制** - 点击图片即可复制到剪贴板
- ⬇️ **便捷下载** - 支持下载原始图片
- 🔒 **管理后台** - 完整的管理员功能（审核、删除、编辑）
- 🎲 **随机浏览** - 随机查看表情包功能
- 📊 **访问统计** - 记录浏览和下载次数

## 🛠️ 技术栈

- **后端**: PHP 7.4+
- **数据库**: SQLite / MySQL
- **前端**: Vanilla JavaScript + CSS3
- **服务器**: Nginx / Apache

## 📦 安装指南

### 环境要求

- PHP 7.4 或更高版本
- SQLite 3 或 MySQL 5.7+
- Nginx 或 Apache

### 快速开始

1. **克隆项目**

```bash
git clone https://github.com/your-username/xxhub.git
cd xxhub
```

2. **配置权限**

```bash
chmod -R 755 assets/uploads
chmod -R 755 data
chmod 666 config.json
```

3. **启动服务**

使用内置 PHP 服务器进行开发：

```bash
php -S localhost:8080
```

或配置 Nginx：

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/xxhub;
    index index.php;

    # 隐藏 .php 后缀
    location / {
        try_files $uri $uri/ $uri.php?$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 静态资源缓存
    location ~* \.(js|css|png|jpg|jpeg|gif|webp|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

4. **完成安装**

访问 `http://your-domain.com/install` 完成安装配置。

### 安装选项

| 选项 | 说明 | 默认值 |
|------|------|--------|
| 数据库类型 | SQLite 或 MySQL | SQLite |
| 上传大小限制 | 最大文件大小（字节） | 10MB |
| 允许的文件类型 | 逗号分隔的扩展名 | png,gif,jpg,jpeg,webp |

## 🚀 使用说明

### 前端功能

- **浏览表情包**: 首页展示所有已通过审核的表情包
- **搜索**: 在顶部搜索框输入关键词进行搜索
- **排序**: 支持按最新、最热、随机排序
- **复制图片**: 点击图片即可复制到剪贴板
- **下载图片**: 右键点击图片选择"保存图片"

### 管理后台

访问 `/admin/login` 登录管理后台：

- **审核管理**: 审核待审核的表情包
- **全部表情包**: 查看所有表情包，支持按状态筛选
- **审核日志**: 查看审核操作记录
- **网站设置**: 修改网站标题、别名、描述等

## 📡 API 接口

### 获取表情包列表

```
GET /api/list?page=1&sort=latest
```

| 参数 | 类型 | 说明 |
|------|------|------|
| page | int | 页码，默认 1 |
| sort | string | 排序方式：latest（最新）、hot（最热）、random（随机） |

### 搜索表情包

```
GET /api/search?q=关键词&page=1
```

### 获取随机图片

```
GET /api/random
GET /api/random?json=1  # 返回 JSON 格式
```

### 上传表情包

```
POST /api/upload
Content-Type: multipart/form-data

image: <图片文件>
title: <标题>
tags: <标签（可选）>
```

### 统计接口

```
POST /api/stats
Content-Type: application/json

{
    "id": <表情包ID>,
    "action": "view|download"
}
```

## 📁 项目结构

```
xxhub/
├── admin/                    # 管理后台
│   ├── api/                  # 管理 API
│   │   ├── auth.php          # 认证接口
│   │   ├── logo.php          # Logo 上传
│   │   ├── review.php        # 审核管理
│   │   └── settings.php      # 网站设置
│   ├── index.php             # 后台首页
│   └── login.php             # 登录页面
├── api/                      # 公共 API
│   ├── list.php              # 列表接口
│   ├── random.php            # 随机图片
│   ├── search.php            # 搜索接口
│   ├── stats.php             # 统计接口
│   └── upload.php            # 上传接口
├── assets/                   # 静态资源
│   ├── css/                  # 样式文件
│   ├── js/                   # 脚本文件
│   └── uploads/              # 上传目录
├── includes/                 # 核心模块
│   ├── auth.php              # 认证功能
│   ├── config.php            # 配置管理
│   ├── db.php                # 数据库连接
│   ├── functions.php         # 通用函数
│   └── audit.php             # 审核相关（已废弃）
├── .env.example              # 环境变量示例
├── .gitignore                # Git 忽略文件
├── api-guide.php             # API 文档页面
├── config.json               # 配置文件（运行时生成）
├── index.php                 # 首页
├── install.php               # 安装向导
├── nginx.conf                # Nginx 配置示例
└── upload.php                # 上传页面
```

## 🤝 贡献指南

欢迎提交 Issue 和 Pull Request！

### 开发流程

1. Fork 项目
2. 创建功能分支 `git checkout -b feature/xxx`
3. 提交代码 `git commit -am 'Add xxx feature'`
4. 推送到分支 `git push origin feature/xxx`
5. 创建 Pull Request

### 代码规范

- PHP 代码遵循 PSR-12 规范
- JavaScript 代码使用 ES6+ 语法
- CSS 代码使用 BEM 命名规范
- 所有提交信息使用英文描述

## 📄 许可证

MIT License - 详见 [LICENSE](LICENSE) 文件

## 📧 联系方式

如有问题或建议，欢迎通过以下方式联系：

- 提交 [Issue](https://github.com/your-username/xxhub/issues)
- 发送邮件至 your-email@example.com

---

**Made with ❤️ by xxHub Team**

*并不保留所有权利* 😉
