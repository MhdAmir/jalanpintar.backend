# 🚀 Quick Reference - Form Builder API

## Installation (5 Steps)

```bash
composer install
composer require league/csv
php artisan key:generate
php artisan jwt:secret
php artisan migrate
php artisan serve
```

## Essential Endpoints

### Public Access

```
GET    /api/public/forms/{slug}            # Get form
POST   /api/public/submissions              # Submit form
POST   /api/public/announcements/check      # Check status
```

### Auth

```
POST   /api/register                        # Register
POST   /api/login                           # Login
POST   /api/refresh                         # Refresh token
```

### Admin (Protected)

```
GET    /api/forms                           # List forms
POST   /api/forms                           # Create form
GET    /api/submissions                     # List submissions
POST   /api/announcements/import            # Import CSV
GET    /api/submissions/statistics          # Get stats
```

## Quick Test

### 1. Register Admin

```json
POST /api/register
{
  "name": "Admin",
  "email": "admin@test.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### 2. Create Form

```json
POST /api/forms (with Bearer token)
{
  "name": "Event Registration",
  "enable_payment": true,
  "sections": [{
    "title": "Info",
    "fields": [
      {"label": "Name", "type": "text", "required": true},
      {"label": "Email", "type": "email", "required": true}
    ]
  }]
}
```

### 3. Submit Form

```json
POST /api/public/submissions
{
  "form_slug": "event-registration",
  "data": {
    "field-uuid": "John Doe",
    "field-uuid": "john@test.com"
  }
}
```

### 4. Import Results

```csv
name,phone,status
John Doe,+628123456789,lolos
```

```bash
POST /api/announcements/import (with file)
```

## Field Types Available

-   text, email, phone, textarea, number
-   select, checkbox, radio, date, file

## Payment Statuses

-   unpaid, pending, paid, failed, refunded

## Announcement Statuses

-   lolos (passed)
-   tidak_lolos (failed)
-   pending

## CSV Headers (Indonesian Support)

English: name, phone, email, status, note  
Indonesian: nama, telepon, email, status, catatan

## Statistics Endpoints

```
GET /api/submissions/statistics?form_id={uuid}
GET /api/announcements/statistics?form_id={uuid}
```

## Documentation Files

-   `API_DOCUMENTATION.md` - Complete API reference
-   `PROJECT_README.md` - Installation & architecture
-   `IMPLEMENTATION_SUMMARY.md` - What was built

## Key Features

✅ Dynamic form builder  
✅ Payment & pricing tiers  
✅ Upsells  
✅ Affiliate system (10% commission)  
✅ CSV bulk import  
✅ Public result checking  
✅ JWT authentication

**API Base**: `http://localhost:8000/api`
