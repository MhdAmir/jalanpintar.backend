# Form Builder API Documentation

## Overview

Complete REST API for dynamic form builder platform with payment, affiliate, and announcement features.

**Base URL**: `http://your-domain.com/api`

---

## Authentication

### Register

```http
POST /register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response**:

```json
{
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
        "id": "uuid",
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

_Refresh token stored in HTTP-only cookie_

### Login

```http
POST /login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

### Refresh Token

```http
POST /refresh
Cookie: refresh_token=<token>
```

### Logout

```http
POST /logout
Authorization: Bearer <access_token>
Cookie: refresh_token=<token>
```

---

## Categories

### List Categories

```http
GET /categories
Authorization: Bearer <access_token>
```

**Response**:

```json
{
    "success": true,
    "data": [
        {
            "id": "uuid",
            "name": "Registration Forms",
            "slug": "registration-forms",
            "description": "Forms for event registration",
            "forms_count": 5,
            "created_at": "2024-01-01 10:00:00"
        }
    ]
}
```

### Create Category

```http
POST /categories
Authorization: Bearer <access_token>
Content-Type: application/json

{
  "name": "Registration Forms",
  "slug": "registration-forms",
  "description": "Forms for event registration"
}
```

### Update Category

```http
PUT /categories/{id}
Authorization: Bearer <access_token>

{
  "name": "Updated Name"
}
```

### Delete Category

```http
DELETE /categories/{id}
Authorization: Bearer <access_token>
```

---

## Forms

### List Forms

```http
GET /forms?category_id={uuid}&is_active=true&search=event&per_page=15
Authorization: Bearer <access_token>
```

**Query Parameters**:

-   `category_id` (optional): Filter by category
-   `is_active` (optional): Filter active/inactive forms
-   `search` (optional): Search by name
-   `per_page` (optional): Items per page (default: 15)

**Response**:

```json
{
    "success": true,
    "data": [
        {
            "id": "uuid",
            "name": "Event Registration 2024",
            "description": "Register for our annual event",
            "slug": "event-registration-2024",
            "is_active": true,
            "enable_payment": true,
            "enable_affiliate": true,
            "category": {
                "id": "uuid",
                "name": "Registration Forms"
            },
            "submissions_count": 150,
            "created_at": "2024-01-01 10:00:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

### Create Form (with Sections & Fields)

```http
POST /forms
Authorization: Bearer <access_token>
Content-Type: application/json

{
  "name": "Event Registration 2024",
  "description": "Register for our annual event",
  "slug": "event-registration-2024",
  "is_active": true,
  "enable_payment": true,
  "enable_affiliate": true,
  "category_id": "uuid",
  "sections": [
    {
      "title": "Personal Information",
      "description": "Please provide your details",
      "order": 0,
      "fields": [
        {
          "label": "Full Name",
          "type": "text",
          "required": true,
          "placeholder": "Enter your full name",
          "order": 0
        },
        {
          "label": "Email Address",
          "type": "email",
          "required": true,
          "placeholder": "your@email.com",
          "order": 1
        },
        {
          "label": "Phone Number",
          "type": "phone",
          "required": true,
          "placeholder": "+62812345678",
          "order": 2
        },
        {
          "label": "Age",
          "type": "number",
          "required": false,
          "order": 3
        },
        {
          "label": "T-Shirt Size",
          "type": "select",
          "required": true,
          "options": ["S", "M", "L", "XL", "XXL"],
          "order": 4
        },
        {
          "label": "Dietary Preferences",
          "type": "checkbox",
          "required": false,
          "options": ["Vegetarian", "Vegan", "Halal", "No Preference"],
          "order": 5
        }
      ]
    },
    {
      "title": "Additional Information",
      "order": 1,
      "fields": [
        {
          "label": "How did you hear about us?",
          "type": "textarea",
          "required": false,
          "order": 0
        }
      ]
    }
  ]
}
```

### Get Form Details

```http
GET /forms/{id}
Authorization: Bearer <access_token>
```

**Response** includes: category, sections, fields, pricing_tiers, upsells, affiliate_rewards

### Update Form

```http
PUT /forms/{id}
Authorization: Bearer <access_token>

{
  "name": "Updated Form Name",
  "is_active": false
}
```

### Duplicate Form

```http
POST /forms/{id}/duplicate
Authorization: Bearer <access_token>
```

### Delete Form

```http
DELETE /forms/{id}
Authorization: Bearer <access_token>
```

### Get Public Form (for submission)

```http
GET /public/forms/{slug}
```

**Response**: Returns form with sections, fields, pricing tiers, and upsells

---

## Submissions

### List Submissions

```http
GET /submissions?form_id={uuid}&payment_status=paid&search=John&per_page=15
Authorization: Bearer <access_token>
```

**Query Parameters**:

-   `form_id`: Filter by form
-   `payment_status`: unpaid, pending, paid, failed, refunded
-   `affiliate_code`: Filter by affiliate code
-   `search`: Search submission number or contact info

### Submit Form (Public Endpoint)

```http
POST /public/submissions
Content-Type: application/json

{
  "form_slug": "event-registration-2024",
  "data": {
    "field-uuid-1": "John Doe",
    "field-uuid-2": "john@example.com",
    "field-uuid-3": "+628123456789",
    "field-uuid-4": 25,
    "field-uuid-5": "L",
    "field-uuid-6": ["Vegetarian", "Halal"]
  },
  "pricing_tier_id": "uuid",
  "upsells_selected": ["uuid1", "uuid2"],
  "affiliate_code": "PARTNER2024",
  "payment_method": "credit_card"
}
```

**Response**:

```json
{
    "success": true,
    "message": "Form submitted successfully",
    "data": {
        "id": "uuid",
        "submission_number": "SUB-2024-00001",
        "payment_status": "unpaid",
        "amount": "250000.00",
        "total_amount": "350000.00",
        "affiliate_commission": "35000.00",
        "contact_name": "John Doe",
        "contact_email": "john@example.com",
        "contact_phone": "+628123456789",
        "created_at": "2024-01-01 10:00:00"
    }
}
```

### Get Submission Details

```http
GET /submissions/{id}
Authorization: Bearer <access_token>
```

### Update Payment Status

```http
PATCH /submissions/{id}/payment-status
Authorization: Bearer <access_token>
Content-Type: application/json

{
  "payment_status": "paid",
  "payment_reference": "PAY-2024-001",
  "payment_method": "bank_transfer"
}
```

### Get Statistics

```http
GET /submissions/statistics?form_id={uuid}
Authorization: Bearer <access_token>
```

**Response**:

```json
{
    "success": true,
    "data": {
        "total_submissions": 150,
        "payment_stats": {
            "paid": 120,
            "unpaid": 20,
            "pending": 8,
            "failed": 2
        },
        "total_revenue": "45000000.00",
        "affiliate_referrals": 35,
        "total_commissions": "3500000.00"
    }
}
```

---

## Announcements

### List Announcements

```http
GET /announcements?form_id={uuid}&status=lolos&search=John&per_page=15
Authorization: Bearer <access_token>
```

**Query Parameters**:

-   `form_id`: Filter by form
-   `status`: lolos, tidak_lolos, pending
-   `search`: Search by name, phone, or email

### Create Announcement

```http
POST /announcements
Authorization: Bearer <access_token>
Content-Type: application/json

{
  "form_id": "uuid",
  "submission_id": "uuid",
  "name": "John Doe",
  "phone": "+628123456789",
  "email": "john@example.com",
  "status": "lolos",
  "note": "Congratulations! You have been selected."
}
```

### Update Announcement

```http
PUT /announcements/{id}
Authorization: Bearer <access_token>

{
  "status": "tidak_lolos",
  "note": "Sorry, better luck next time"
}
```

### Delete Announcement

```http
DELETE /announcements/{id}
Authorization: Bearer <access_token>
```

### Import Announcements from CSV

```http
POST /announcements/import
Authorization: Bearer <access_token>
Content-Type: multipart/form-data

{
  "form_id": "uuid",
  "file": <csv_file>
}
```

**CSV Format**:

```csv
name,phone,email,status,note
John Doe,+628123456789,john@example.com,lolos,Congratulations
Jane Smith,+628987654321,jane@example.com,tidak_lolos,Better luck next time
```

**Alternative CSV headers** (Indonesian):

```csv
nama,telepon,email,status,catatan
John Doe,+628123456789,john@example.com,lolos,Selamat
```

**Response**:

```json
{
    "success": true,
    "message": "Import completed successfully",
    "data": {
        "imported": 98,
        "errors": [
            "Row 5: Missing required field: phone",
            "Row 12: Invalid status value: maybe"
        ]
    }
}
```

### Check Announcement Status (Public)

```http
POST /public/announcements/check
Content-Type: application/json

{
  "form_slug": "event-registration-2024",
  "phone": "+628123456789"
}
```

**Response**:

```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "name": "John Doe",
        "phone": "+628123456789",
        "email": "john@example.com",
        "status": "lolos",
        "note": "Congratulations! You have been selected.",
        "announced_at": "2024-01-15 14:30:00"
    }
}
```

### Get Announcement Statistics

```http
GET /announcements/statistics?form_id={uuid}
Authorization: Bearer <access_token>
```

**Response**:

```json
{
    "success": true,
    "data": {
        "total": 150,
        "lolos": 45,
        "tidak_lolos": 95,
        "pending": 10
    }
}
```

---

## Pricing Tiers

### Create Pricing Tier

```http
POST /pricing-tiers
Authorization: Bearer <access_token>
Content-Type: application/json

{
  "form_id": "uuid",
  "tier_name": "Early Bird",
  "price": 150000,
  "features": [
    "Conference Access",
    "Lunch",
    "Coffee Break",
    "Certificate"
  ],
  "is_default": true,
  "is_active": true,
  "order": 0
}
```

---

## Upsells

### Create Upsell

```http
POST /upsells
Authorization: Bearer <access_token>
Content-Type: application/json

{
  "form_id": "uuid",
  "enabled": true,
  "upsell_title": "VIP Workshop Access",
  "upsell_price": 500000,
  "upsell_description": "Get exclusive access to VIP workshops",
  "button_text": "Add VIP Access",
  "order": 0
}
```

---

## Affiliate Rewards

### Create Affiliate

```http
POST /affiliate-rewards
Authorization: Bearer <access_token>
Content-Type: application/json

{
  "form_id": "uuid",
  "affiliate_name": "Partner Company",
  "affiliate_code": "PARTNER2024",
  "email": "partner@company.com",
  "commission_percentage": 10.5,
  "is_active": true
}
```

---

## Error Responses

All endpoints return consistent error format:

```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

**HTTP Status Codes**:

-   `200`: Success
-   `201`: Created
-   `400`: Bad Request
-   `401`: Unauthorized
-   `404`: Not Found
-   `422`: Validation Error
-   `500`: Server Error

---

## Complete Workflow Example

### 1. Admin Creates Form

```bash
POST /forms
# Creates form with sections and fields
```

### 2. Admin Sets Up Payment Tiers

```bash
POST /pricing-tiers
# Add Early Bird tier: Rp 150,000
# Add Regular tier: Rp 250,000
# Add VIP tier: Rp 500,000
```

### 3. Admin Sets Up Upsells

```bash
POST /upsells
# Add workshop upsell: +Rp 100,000
# Add merchandise upsell: +Rp 50,000
```

### 4. Admin Creates Affiliate

```bash
POST /affiliate-rewards
# Code: PARTNER2024
# Commission: 10%
```

### 5. Public User Submits Form

```bash
POST /public/submissions
{
  "form_slug": "event-registration-2024",
  "data": { ... },
  "pricing_tier_id": "early-bird-uuid",
  "upsells_selected": ["workshop-uuid"],
  "affiliate_code": "PARTNER2024"
}
# Calculates: Rp 150,000 + Rp 100,000 = Rp 250,000
# Affiliate earns: Rp 25,000 (10%)
```

### 6. Admin Updates Payment

```bash
PATCH /submissions/{id}/payment-status
{
  "payment_status": "paid",
  "payment_reference": "BANK-TRF-001"
}
```

### 7. Admin Imports Announcement Results

```bash
POST /announcements/import
# Upload CSV with names, phones, status (lolos/tidak_lolos)
```

### 8. Public User Checks Result

```bash
POST /public/announcements/check
{
  "form_slug": "event-registration-2024",
  "phone": "+628123456789"
}
# Returns: status "lolos" or "tidak_lolos"
```

---

## Database Schema Summary

```
categories
├── id (uuid)
├── name
├── slug
└── description

forms
├── id (uuid)
├── name
├── slug
├── category_id → categories.id
├── enable_payment
└── enable_affiliate

sections
├── id (uuid)
├── form_id → forms.id
├── title
└── order

fields
├── id (uuid)
├── section_id → sections.id
├── label
├── type
├── required
├── options (JSON)
└── order

pricing_tiers
├── id (uuid)
├── form_id → forms.id
├── tier_name
├── price
├── features (JSON)
└── is_default

upsells
├── id (uuid)
├── form_id → forms.id
├── upsell_title
├── upsell_price
└── enabled

affiliate_rewards
├── id (uuid)
├── form_id → forms.id
├── affiliate_code
├── commission_percentage
├── total_earned
└── total_referrals

submissions
├── id (uuid)
├── form_id → forms.id
├── submission_number
├── data (JSON)
├── pricing_tier_id
├── total_amount
├── affiliate_code
├── affiliate_commission
└── payment_status

announcements
├── id (uuid)
├── form_id → forms.id
├── submission_id → submissions.id
├── name
├── phone
├── status (lolos/tidak_lolos)
└── note
```

---

## Installation & Setup

1. **Install dependencies**:

```bash
composer install
```

2. **Install CSV package** (for import):

```bash
composer require league/csv
```

3. **Run migrations**:

```bash
php artisan migrate
```

4. **Generate JWT secret**:

```bash
php artisan jwt:secret
```

5. **Configure `.env`**:

```env
JWT_SECRET=your-secret-key
JWT_TTL=60
JWT_REFRESH_TTL=20160
```

6. **Run the server**:

```bash
php artisan serve
```

---

## Best Practices

1. **Always validate affiliate codes** before creating submissions
2. **Use transactions** for operations involving multiple models
3. **Implement rate limiting** on public endpoints
4. **Store payment references** for audit trails
5. **Use queue jobs** for CSV imports on large files
6. **Implement webhook** for payment gateway callbacks
7. **Add file upload** support for file type fields
8. **Implement email notifications** for announcements
9. **Add caching** for frequently accessed forms
10. **Use soft deletes** to preserve historical data

---

## Security Recommendations

1. Enable CORS properly
2. Implement CSRF protection
3. Use HTTPS in production
4. Sanitize user input
5. Implement rate limiting
6. Add request logging
7. Validate file uploads
8. Implement IP whitelisting for admin routes

---

**API Version**: 1.0  
**Last Updated**: 2024  
**Support**: your-email@domain.com
