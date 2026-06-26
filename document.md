# BMW Technology - Project Documentation

## Tổng quan dự án

Đây là hệ thống CMS (Content Management System) đa ngôn ngữ xây dựng trên Laravel + AngularJS.

---

## Kiến trúc hệ thống

```
┌─────────────────────────────────────────────────────────────┐
│                      Frontend (Web)                         │
│  AngularJS + Blade Templates + Multi-language (VI/EN/JA)    │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                     Backend (Laravel)                        │
│  REST API + Session Auth + Media Management                  │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      Database (MySQL)                        │
│  Multi-language JSON columns + Hierarchical Categories       │
└─────────────────────────────────────────────────────────────┘
```

---

## Cấu trúc thư mục chính

```
www-bmw/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # Controllers cho trang quản trị
│   │   ├── Rest/           # REST API controllers
│   │   └── Web/            # Controllers cho frontend
│   ├── Models/             # Eloquent models
│   └── Services/           # Business logic services
│
├── resources/
│   ├── js/
│   │   ├── admin/          # AngularJS cho admin
│   │   │   ├── common/     # Directives, services dùng chung
│   │   │   └── pages/      # Controllers theo trang
│   │   └── web/            # AngularJS cho frontend
│   └── views/
│       ├── admin/          # Blade templates admin
│       └── web/            # Blade templates frontend
│
├── database/migrations/    # Database migrations
└── routes/route.php        # All routes
```

---

## Database Schema

### Bảng chính

| Bảng                | Mô tả                                                |
| ------------------- | ---------------------------------------------------- |
| `users`             | Tài khoản quản trị                                   |
| `e_languages`       | Cấu hình ngôn ngữ (VI, EN, JA) với translations JSON |
| `e_post_categories` | Danh mục bài viết (hierarchical, multi-lang `names`) |
| `e_posts`           | Bài viết (multi-lang, multi-category, SEO)           |
| `e_media_folders`   | Thư mục media                                        |
| `e_media_files`     | Files media (images, docs, videos)                   |
| `e_media_logs`      | Lịch sử thao tác media                               |
| `e_media_settings`  | Cấu hình upload                                      |

### Đa ngôn ngữ (Multi-language)

Hệ thống hỗ trợ nhiều ngôn ngữ thông qua JSON columns:

```json
// Ví dụ cột `names` trong e_post_categories
{
  "vi": "Tin tức",
  "en": "News",
  "ja": "ニュース"
}

// Ví dụ cột `titles` trong e_posts
{
  "vi": "Bài viết mẫu",
  "en": "Sample Post",
  "ja": "サンプル記事"
}
```

**Quy tắc:** Bắt buộc nhập đủ nội dung cho TẤT CẢ ngôn ngữ active.

### Xử lý khi thay đổi ngôn ngữ

| Hành động             | Xử lý                                                          |
| --------------------- | -------------------------------------------------------------- |
| **Thêm ngôn ngữ mới** | Bài cũ fallback về ngôn ngữ mặc định, bài mới bắt buộc nhập đủ |
| **Xóa ngôn ngữ**      | Soft delete (`is_active=false`), giữ data trong JSON           |
| **Bật lại ngôn ngữ**  | Data cũ tự động hiển thị lại                                   |

```php
// Fallback logic trong Model
public function getTitle(string $lang): string
{
    if (!empty($this->titles[$lang])) {
        return $this->titles[$lang];
    }
    // Fallback về ngôn ngữ mặc định
    $default = ELanguage::getDefault();
    return $this->titles[$default->code] ?? '';
}
```

---

## Bảng e_posts (Chi tiết)

### Form layout (Create/Edit)

Form được chia làm **2 phần chính**:

```
┌─────────────────────────────────────────────────────┐
│    PHẦN 1: THÔNG TIN CHUNG (Không theo ngôn ngữ)    │
│  • Danh mục (multiple) + Danh mục chính             │
│  • Tác giả, Vị trí, Trạng thái                      │
│  • Bài nổi bật, Hiển thị mục lục                    │
│  • Ảnh chính, Album ảnh                             │
│  • Tags, Tệp đính kèm, Video                        │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│    [🇻🇳 VI] [🇺🇸 EN] [🇯🇵 JA] ← Tabs ngôn ngữ       │
│  ─────────────────────────────────────────────────  │
│  • Tiêu đề *                                        │
│  • Mô tả ngắn *                                     │
│  • Nội dung *                                       │
│  • SEO: Đường dẫn *, Tiêu đề, Mô tả, Từ khóa        │
│  • Phân tích SEO (real-time)                        │
└─────────────────────────────────────────────────────┘
```

> [!IMPORTANT]
> Bắt buộc nhập đầy đủ các trường có dấu `*` cho TẤT CẢ ngôn ngữ active.

### Cấu trúc cột

| Cột                  | Kiểu   | Mô tả                                                     |
| -------------------- | ------ | --------------------------------------------------------- |
| `titles`             | JSON   | Tiêu đề multi-lang `{"vi":"...", "en":"...", "ja":"..."}` |
| `slugs`              | JSON   | Đường dẫn SEO multi-lang                                  |
| `categories`         | JSON   | Mảng ID danh mục `[1, 2, 3]`                              |
| `main_category_id`   | FK     | Danh mục chính (dùng cho URL, breadcrumb)                 |
| `author_id`          | FK     | Tác giả (có thể khác created_by)                          |
| `main_image`         | string | URL ảnh đại diện                                          |
| `album_images`       | JSON   | Mảng URL ảnh album                                        |
| `short_descriptions` | JSON   | Mô tả ngắn multi-lang                                     |
| `contents`           | JSON   | Nội dung bài viết multi-lang (HTML)                       |
| `tags`               | JSON   | Thẻ bài viết (max 10, mỗi thẻ ≤45 ký tự)                  |
| `attachments`        | JSON   | Tệp đính kèm                                              |
| `video_urls`         | JSON   | URL video (Youtube, etc.)                                 |
| `position`           | int    | Vị trí hiển thị                                           |
| `view_count`         | int    | Lượt xem                                                  |
| `is_featured`        | bool   | Bài nổi bật                                               |
| `show_toc`           | bool   | Hiển thị mục lục                                          |
| `status`             | int    | 0=draft, 1=published                                      |
| `seo_titles`         | JSON   | Meta title multi-lang                                     |
| `seo_descriptions`   | JSON   | Meta description multi-lang                               |
| `seo_keywords`       | JSON   | Keywords multi-lang                                       |
| `related_posts`      | JSON   | Mảng ID bài viết liên quan                                |

### Ví dụ dữ liệu

```json
{
    "titles": { "vi": "BMW X5 2024", "en": "BMW X5 2024", "ja": "BMW X5 2024" },
    "categories": [1, 4, 7],
    "main_category_id": 1,
    "slugs": { "vi": "bmw-x5-2024", "en": "bmw-x5-2024", "ja": "bmw-x5-2024" },
    "contents": {
        "vi": "<p>Nội dung tiếng Việt...</p>",
        "en": "<p>English content...</p>",
        "ja": "<p>日本語コンテンツ...</p>"
    }
}
```

---

## Bảng e_post_categories

### Cấu trúc

| Cột         | Kiểu   | Mô tả                                                 |
| ----------- | ------ | ----------------------------------------------------- |
| `parent_id` | FK     | Danh mục cha (null = root)                            |
| `names`     | JSON   | Tên multi-lang `{"vi":"...", "en":"...", "ja":"..."}` |
| `slug`      | string | Đường dẫn SEO                                         |
| `position`  | int    | Vị trí sắp xếp                                        |
| `is_active` | bool   | Trạng thái hoạt động                                  |

### Quan hệ cha-con (Hierarchical)

```
📁 Dòng xe (parent_id: null)
   ├── Sedan (parent_id: 1)
   ├── SUV (parent_id: 1)
   └── Coupe (parent_id: 1)
```

---

## Bảng e_languages

### Cấu trúc

| Cột                 | Kiểu   | Mô tả                                 |
| ------------------- | ------ | ------------------------------------- |
| `code`              | string | Mã ngôn ngữ: `vi`, `en`, `ja`         |
| `name`              | string | Tên hiển thị: "Tiếng Việt", "English" |
| `flag_icon`         | string | Icon cờ quốc gia                      |
| `is_active`         | bool   | Ngôn ngữ có hoạt động                 |
| `is_default`        | bool   | Ngôn ngữ mặc định                     |
| `translations`      | JSON   | Bản dịch UI cho ngôn ngữ đó           |
| `translations_hash` | string | Hash để cache busting                 |

---

## REST API Endpoints

### Authentication

-   `POST /admin/login` - Đăng nhập
-   `POST /admin/logout` - Đăng xuất

### Posts

-   `GET /admin/v1/rest/posts` - Danh sách bài viết
-   `POST /admin/v1/rest/posts` - Tạo bài viết
-   `GET /admin/v1/rest/posts/{id}` - Chi tiết bài viết
-   `PUT /admin/v1/rest/posts/{id}` - Cập nhật bài viết
-   `DELETE /admin/v1/rest/posts/{id}` - Xóa bài viết

### Categories

-   `GET /admin/v1/rest/post-categories` - Danh sách
-   `GET /admin/v1/rest/post-categories/dropdown` - Dropdown options
-   `GET /admin/v1/rest/post-categories/menu-tree` - Tree cho menu

### Media

-   `GET /admin/v1/rest/media/folders/tree` - Cây thư mục
-   `GET /admin/v1/rest/media/folders` - Folders + files trong folder
-   `POST /admin/v1/rest/media/files` - Upload files
-   `DELETE /admin/v1/rest/media/files/{id}` - Xóa file

### Languages

-   `GET /admin/v1/rest/languages` - Danh sách ngôn ngữ
-   `GET /api/translations` - Translations cho frontend (public)

---

## Quy tắc chọn Media (Ảnh/File đính kèm)

> [!IMPORTANT] > **Tất cả việc chọn ảnh/file đính kèm PHẢI thông qua Media Picker directive.**
> Không cho phép chọn file trực tiếp từ bên ngoài hệ thống.

| Hành động                 | Xử lý                                       |
| ------------------------- | ------------------------------------------- |
| **Chọn ảnh đại diện**     | Mở Media Picker modal → chọn từ thư viện    |
| **Chọn album ảnh**        | Mở Media Picker (multiple) → chọn nhiều ảnh |
| **Chọn file đính kèm**    | Mở Media Picker → chọn file từ thư viện     |
| **Upload file mới**       | Trong Media Picker có nút Upload            |
| **CKEditor insert image** | Mở Media Picker thay vì upload dialog       |

### Lý do:

1. **Quản lý tập trung:** Tất cả files được lưu trong Media Library
2. **Tái sử dụng:** Một file có thể dùng cho nhiều bài viết
3. **Theo dõi:** Biết file nào đang được dùng ở đâu
4. **Bảo mật:** Kiểm soát loại file được upload

---

## AngularJS Modules

### Admin App (`adminApp`)

**Directives quan trọng:**

-   `ck-editor` - CKEditor 5 integration
-   `media-picker` - Modal chọn media từ thư viện
-   `vn-datetime` - Date/time picker với format VN

**Controllers pattern:**

```
resources/js/admin/pages/{feature}/
├── {feature}Ctrl.js        # List controller
├── {feature}CreateCtrl.js  # Create controller
└── {feature}EditCtrl.js    # Edit controller
```

---

## Quy ước đặt tên

| Loại                 | Convention         | Ví dụ                          |
| -------------------- | ------------------ | ------------------------------ |
| Database table       | `e_{tên_số_nhiều}` | `e_posts`, `e_languages`       |
| Model                | `E{TênSốNhiều}`    | `EPost`, `ELanguage`           |
| Controller (Admin)   | `{Tên}Controller`  | `PostController`               |
| Controller (REST)    | `{Tên}Controller`  | `PostsController`              |
| AngularJS Controller | `{Tên}Ctrl`        | `PostsCtrl`, `PostsCreateCtrl` |

---

## Chạy dự án

```bash
# Install dependencies
composer install
npm install

# Copy environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Build assets
npm run dev

# Start server
php artisan serve
```

---

## Tính năng đang phát triển

-   [ ] SEO Checker (phân tích bài viết theo chuẩn SEO)
-   [ ] CKEditor tích hợp Media Picker (chọn ảnh từ thư viện thay vì upload)
-   [ ] Multi-tenant SaaS Architecture (nhiều sites dùng chung backend)
