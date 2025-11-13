# Test MVP API Endpoints

## Test Create Form with All Data

```bash
# Login first
POST http://localhost:8000/api/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}

# Copy the token, then use it for next requests
```

```bash
# Create Form with Sections, Fields, Pricing Tiers, and Upsells
POST http://localhost:8000/api/forms
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "title": "Test Bootcamp MVP",
  "description": "Testing MVP endpoint",
  "slug": "test-bootcamp-mvp",
  "is_active": true,
  "enable_payment": true,
  "enable_affiliate": false,

  "sections": [
    {
      "title": "Data Pribadi",
      "description": "Isi data diri",
      "order": 1,
      "fields": [
        {
          "label": "Nama Lengkap",
          "name": "full_name",
          "type": "text",
          "placeholder": "Masukkan nama",
          "is_required": true,
          "order": 1
        },
        {
          "label": "Email",
          "name": "email",
          "type": "email",
          "placeholder": "email@example.com",
          "is_required": true,
          "order": 2
        }
      ]
    },
    {
      "title": "Pengalaman",
      "order": 2,
      "fields": [
        {
          "label": "Level",
          "name": "level",
          "type": "select",
          "is_required": true,
          "order": 1,
          "options": [
            {"value": "beginner", "label": "Pemula"},
            {"value": "intermediate", "label": "Menengah"}
          ]
        }
      ]
    }
  ],

  "pricing_tiers": [
    {
      "name": "Early Bird",
      "price": 2000000,
      "currency": "IDR",
      "is_default": true,
      "order": 1
    },
    {
      "name": "Regular",
      "price": 3000000,
      "currency": "IDR",
      "order": 2
    }
  ],

  "upsells": [
    {
      "name": "Mentoring",
      "price": 500000,
      "order": 1
    }
  ]
}
```

## Test Update Form

```bash
# Get form first to see the IDs
GET http://localhost:8000/api/forms/{formId}
Authorization: Bearer YOUR_TOKEN_HERE

# Update with IDs (to update existing) and without IDs (to create new)
PUT http://localhost:8000/api/forms/{formId}
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "title": "Updated Title",
  "sections": [
    {
      "id": "{existing-section-id}",
      "title": "Updated Section Title",
      "fields": [
        {
          "id": "{existing-field-id}",
          "label": "Updated Field Label",
          "type": "text",
          "is_required": true,
          "order": 1
        },
        {
          "label": "New Field",
          "name": "new_field",
          "type": "email",
          "is_required": false,
          "order": 2
        }
      ]
    },
    {
      "title": "New Section",
      "order": 2,
      "fields": [
        {
          "label": "Another Field",
          "name": "another",
          "type": "text",
          "order": 1
        }
      ]
    }
  ]
}
```

## Expected Behaviors

### Create:

-   ✅ Form created with all related data in one transaction
-   ✅ Sections created with order
-   ✅ Fields created under sections
-   ✅ Pricing tiers created
-   ✅ Upsells created
-   ✅ Returns complete form with IDs

### Update:

-   ✅ Items with ID → Update
-   ✅ Items without ID → Create new
-   ✅ Items not in array → Delete
-   ✅ Transaction rollback if error

### Get:

-   ✅ Returns complete form structure
-   ✅ All relations loaded
-   ✅ Ordered by order column
