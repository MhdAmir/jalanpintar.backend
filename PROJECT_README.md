# Form Builder Platform - Complete Backend System

## 📋 Project Overview

A complete, production-ready Form Builder platform built with **Laravel 11** and **JWT Authentication**. This system supports dynamic form creation, payment integration, affiliate marketing, and result announcements.

## ✨ Features

### Core Features

-   ✅ **Dynamic Form Builder** - Create forms with multiple sections and fields
-   ✅ **10+ Field Types** - Text, email, phone, textarea, number, select, checkbox, radio, date, file
-   ✅ **Category Management** - Organize forms by categories
-   ✅ **Form Duplication** - Clone existing forms with all settings

### Payment & Pricing

-   ✅ **Pricing Tiers** - Multiple pricing options per form
-   ✅ **Upsells** - Add-on products to increase revenue
-   ✅ **Payment Tracking** - Track payment status and references
-   ✅ **Revenue Reports** - Complete financial statistics

### Affiliate System

-   ✅ **Affiliate Codes** - Unique codes for partners
-   ✅ **Commission Tracking** - Automatic commission calculation
-   ✅ **Referral Analytics** - Track affiliate performance
-   ✅ **Payout Management** - Manage affiliate payouts

### Submissions & Announcements

-   ✅ **Public Submissions** - Allow public form submissions
-   ✅ **Submission Management** - View and manage all submissions
-   ✅ **Announcement System** - Publish results (Lolos/Tidak Lolos)
-   ✅ **CSV Import** - Bulk import announcements
-   ✅ **Public Status Check** - Users can check their results

### Security & Auth

-   ✅ **JWT Authentication** - Secure token-based auth
-   ✅ **Refresh Tokens** - Long-lived refresh tokens in HTTP-only cookies
-   ✅ **Role-based Access** - Protected admin routes
-   ✅ **Input Validation** - Comprehensive validation rules

## 🏗️ Architecture

### Clean Architecture Pattern

```
app/
├── Http/
│   ├── Controllers/      # Handle HTTP requests
│   ├── Requests/         # Validation logic
│   └── Resources/        # Response transformers
├── Models/               # Eloquent models
└── Services/             # Business logic layer
```

### Service Layer

-   **FormService** - Form creation, duplication, public access
-   **SubmissionService** - Form submission, payment calculation, affiliate processing
-   **AnnouncementService** - CSV import, status checking, statistics

## 📁 Project Structure

```
smartpath.form/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── FormController.php
│   │   │   ├── SubmissionController.php
│   │   │   └── AnnouncementController.php
│   │   ├── Requests/
│   │   │   ├── LoginRequest.php
│   │   │   ├── RegisterRequest.php
│   │   │   ├── StoreCategoryRequest.php
│   │   │   ├── StoreFormRequest.php
│   │   │   ├── PublicSubmissionRequest.php
│   │   │   ├── StoreAnnouncementRequest.php
│   │   │   └── ImportAnnouncementRequest.php
│   │   └── Resources/
│   │       ├── CategoryResource.php
│   │       ├── FormResource.php
│   │       ├── SubmissionResource.php
│   │       └── AnnouncementResource.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Category.php
│   │   ├── Form.php
│   │   ├── Section.php
│   │   ├── Field.php
│   │   ├── PricingTier.php
│   │   ├── Upsell.php
│   │   ├── AffiliateReward.php
│   │   ├── Submission.php
│   │   ├── Announcement.php
│   │   └── RefreshToken.php
│   ├── Services/
│   │   ├── FormService.php
│   │   ├── SubmissionService.php
│   │   ├── AnnouncementService.php
│   │   └── RefreshTokenStore.php
│   └── Helpers/
│       └── Cookies.php
├── database/
│   └── migrations/
│       ├── 2024_01_01_000004_create_categories_table.php
│       ├── 2024_01_01_000005_create_forms_table.php
│       ├── 2024_01_01_000006_create_sections_table.php
│       ├── 2024_01_01_000007_create_fields_table.php
│       ├── 2024_01_01_000008_create_pricing_tiers_table.php
│       ├── 2024_01_01_000009_create_upsells_table.php
│       ├── 2024_01_01_000010_create_affiliate_rewards_table.php
│       ├── 2024_01_01_000011_create_submissions_table.php
│       └── 2024_01_01_000012_create_announcements_table.php
├── routes/
│   └── api.php
├── API_DOCUMENTATION.md
└── README.md
```

## 🗄️ Database Schema

### Core Tables

-   **categories** - Form categories
-   **forms** - Form definitions
-   **sections** - Form sections (ordered)
-   **fields** - Form fields with validation rules

### Payment Tables

-   **pricing_tiers** - Pricing options per form
-   **upsells** - Additional products/services

### Affiliate Table

-   **affiliate_rewards** - Affiliate tracking and commissions

### Submission Tables

-   **submissions** - Form submissions with payment data
-   **announcements** - Results/announcements (Lolos/Tidak Lolos)

### Auth Tables

-   **users** - Admin users
-   **refresh_tokens** - JWT refresh tokens

## 🚀 Installation

### 1. Clone & Install

```bash
git clone <repository>
cd smartpath.form
composer install
```

### 2. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

### 3. Database Configuration

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartpath_form
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=<generated-secret>
JWT_TTL=60
JWT_REFRESH_TTL=20160
```

### 4. Install Additional Dependencies

```bash
composer require league/csv
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Start Server

```bash
php artisan serve
```

API will be available at: `http://localhost:8000/api`

## 📚 API Endpoints

### Public Endpoints

```
POST   /api/register                    - Register admin user
POST   /api/login                       - Login
POST   /api/refresh                     - Refresh token
GET    /api/public/forms/{slug}         - Get form for submission
POST   /api/public/submissions          - Submit form
POST   /api/public/announcements/check  - Check announcement status
```

### Protected Endpoints (Require JWT)

```
# Auth
POST   /api/logout
GET    /api/user
PUT    /api/user

# Categories
GET    /api/categories
POST   /api/categories
GET    /api/categories/{id}
PUT    /api/categories/{id}
DELETE /api/categories/{id}

# Forms
GET    /api/forms
POST   /api/forms
GET    /api/forms/{id}
PUT    /api/forms/{id}
DELETE /api/forms/{id}
POST   /api/forms/{id}/duplicate

# Submissions
GET    /api/submissions
GET    /api/submissions/{id}
PATCH  /api/submissions/{id}/payment-status
GET    /api/submissions/statistics

# Announcements
GET    /api/announcements
POST   /api/announcements
GET    /api/announcements/{id}
PUT    /api/announcements/{id}
DELETE /api/announcements/{id}
POST   /api/announcements/import
GET    /api/announcements/statistics
```

See [API_DOCUMENTATION.md](./API_DOCUMENTATION.md) for detailed request/response examples.

## 🔐 Authentication Flow

### 1. Register/Login

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin User",
    "email": "admin@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

Response includes:

-   `access_token` (short-lived, 60 minutes)
-   `refresh_token` (HTTP-only cookie, 14 days)

### 2. Use Access Token

```bash
curl http://localhost:8000/api/forms \
  -H "Authorization: Bearer <access_token>"
```

### 3. Refresh When Expired

```bash
curl -X POST http://localhost:8000/api/refresh \
  --cookie "refresh_token=<token>"
```

## 📊 Example Workflows

### Create Complete Form

```json
POST /api/forms
{
  "name": "Event Registration 2024",
  "slug": "event-registration-2024",
  "enable_payment": true,
  "enable_affiliate": true,
  "sections": [
    {
      "title": "Personal Info",
      "order": 0,
      "fields": [
        {
          "label": "Full Name",
          "type": "text",
          "required": true,
          "order": 0
        },
        {
          "label": "Email",
          "type": "email",
          "required": true,
          "order": 1
        },
        {
          "label": "Phone",
          "type": "phone",
          "required": true,
          "order": 2
        }
      ]
    }
  ]
}
```

### Public Submission

```json
POST /api/public/submissions
{
  "form_slug": "event-registration-2024",
  "data": {
    "field-uuid-1": "John Doe",
    "field-uuid-2": "john@example.com",
    "field-uuid-3": "+628123456789"
  },
  "pricing_tier_id": "uuid",
  "upsells_selected": ["uuid1"],
  "affiliate_code": "PARTNER2024"
}
```

### Import Announcements

```csv
name,phone,email,status,note
John Doe,+628123456789,john@example.com,lolos,Congratulations
Jane Smith,+628987654321,jane@example.com,tidak_lolos,Better luck
```

```bash
curl -X POST http://localhost:8000/api/announcements/import \
  -H "Authorization: Bearer <token>" \
  -F "form_id=uuid" \
  -F "file=@announcements.csv"
```

## 🧪 Testing

### Test Public Submission

```bash
# 1. Get form structure
curl http://localhost:8000/api/public/forms/event-registration-2024

# 2. Submit form
curl -X POST http://localhost:8000/api/public/submissions \
  -H "Content-Type: application/json" \
  -d @submission.json

# 3. Check announcement
curl -X POST http://localhost:8000/api/public/announcements/check \
  -H "Content-Type: application/json" \
  -d '{"form_slug": "event-registration-2024", "phone": "+628123456789"}'
```

## 📈 Statistics & Reports

### Submission Statistics

```bash
GET /api/submissions/statistics?form_id={uuid}
```

Returns:

-   Total submissions
-   Payment stats (paid, unpaid, pending, failed)
-   Total revenue
-   Affiliate referrals & commissions

### Announcement Statistics

```bash
GET /api/announcements/statistics?form_id={uuid}
```

Returns:

-   Total announcements
-   Count by status (lolos, tidak_lolos, pending)

## 🛠️ Best Practices

### 1. Validation

-   All requests validated via FormRequest classes
-   Dynamic field validation based on form structure
-   Type-specific validation (email, phone, number, etc.)

### 2. Security

-   JWT with refresh token rotation
-   HTTP-only cookies for refresh tokens
-   CORS configuration
-   Input sanitization

### 3. Database

-   UUID primary keys
-   Proper foreign key constraints
-   Soft deletes for forms
-   Indexed columns for performance

### 4. Code Organization

-   Service layer for business logic
-   Resources for consistent API responses
-   Eloquent relationships properly defined
-   Query scopes for common filters

## 🐛 Troubleshooting

### Issue: JWT Secret Not Found

```bash
php artisan jwt:secret
```

### Issue: Migration Fails

```bash
php artisan migrate:fresh
```

### Issue: CSV Import Fails

```bash
composer require league/csv
```

### Issue: CORS Error

Update `config/cors.php`:

```php
'paths' => ['api/*'],
'allowed_origins' => ['*'],
```

## 📝 TODO / Future Enhancements

-   [ ] Add file upload support for file type fields
-   [ ] Implement email notifications for announcements
-   [ ] Add webhook support for payment gateways
-   [ ] Implement form templates
-   [ ] Add form analytics (views, conversion rate)
-   [ ] Implement conditional field logic
-   [ ] Add multi-language support
-   [ ] Create admin dashboard frontend
-   [ ] Add export submissions to Excel/PDF
-   [ ] Implement form versioning

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📄 License

This project is licensed under the MIT License.

## 👨‍💻 Developer

Built with ❤️ using Laravel 11

## 📞 Support

For questions or issues:

-   Create an issue in the repository
-   Email: support@example.com

---

**Stack**: Laravel 11 | PHP 8.2+ | MySQL | JWT Auth  
**Version**: 1.0.0  
**Last Updated**: 2024
