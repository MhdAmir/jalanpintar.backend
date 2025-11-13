# Form Builder MVP API Documentation

## Overview

API yang disederhanakan untuk MVP dimana frontend bisa create/update form beserta sections, fields, pricing tiers, dan upsells dalam **SATU ENDPOINT** saja.

---

## Authentication

Semua endpoint form management memerlukan JWT authentication.

Header:

```
Authorization: Bearer {your_jwt_token}
```

---

## 1. Create Form (All-in-One)

Membuat form lengkap beserta sections, fields, pricing tiers, dan upsells dalam satu request.

**Endpoint:**

```
POST /api/forms
```

**Request Body:**

```json
{
    "title": "Formulir Pendaftaran Bootcamp React 2025",
    "description": "Daftar bootcamp intensif React JS batch 10",
    "slug": "bootcamp-react-2025",
    "category_id": "019a7cf5-f5c7-730f-b5d5-af2c9dc45678",
    "cover_image": "https://example.com/cover.jpg",
    "is_active": true,
    "enable_payment": true,
    "enable_affiliate": true,
    "max_submissions": 100,
    "start_date": "2025-01-01",
    "end_date": "2025-12-31",
    "settings": {
        "theme": "modern",
        "send_email": true
    },

    "sections": [
        {
            "title": "Data Pribadi",
            "description": "Isi data diri Anda dengan lengkap",
            "order": 1,
            "fields": [
                {
                    "label": "Nama Lengkap",
                    "name": "full_name",
                    "type": "text",
                    "placeholder": "Masukkan nama lengkap",
                    "help_text": "Sesuai KTP",
                    "is_required": true,
                    "order": 1
                },
                {
                    "label": "Email",
                    "name": "email",
                    "type": "email",
                    "placeholder": "example@email.com",
                    "is_required": true,
                    "order": 2,
                    "validation_rules": {
                        "email": true
                    }
                },
                {
                    "label": "Nomor WhatsApp",
                    "name": "phone",
                    "type": "phone",
                    "placeholder": "08123456789",
                    "is_required": true,
                    "order": 3
                }
            ]
        },
        {
            "title": "Pengalaman",
            "description": "Ceritakan pengalaman coding Anda",
            "order": 2,
            "fields": [
                {
                    "label": "Level Pengalaman",
                    "name": "experience_level",
                    "type": "select",
                    "is_required": true,
                    "order": 1,
                    "options": [
                        { "value": "beginner", "label": "Pemula" },
                        { "value": "intermediate", "label": "Menengah" },
                        { "value": "advanced", "label": "Mahir" }
                    ]
                },
                {
                    "label": "Motivasi",
                    "name": "motivation",
                    "type": "textarea",
                    "placeholder": "Tulis motivasi Anda...",
                    "is_required": false,
                    "order": 2
                }
            ]
        }
    ],

    "pricing_tiers": [
        {
            "name": "Early Bird",
            "description": "Harga khusus pendaftar awal",
            "price": 2500000,
            "currency": "IDR",
            "is_default": true,
            "is_active": true,
            "order": 1
        },
        {
            "name": "Regular",
            "description": "Harga normal",
            "price": 3500000,
            "currency": "IDR",
            "is_default": false,
            "is_active": true,
            "order": 2
        }
    ],

    "upsells": [
        {
            "name": "Mentoring 1-on-1",
            "description": "Sesi mentoring privat 4x pertemuan",
            "price": 1000000,
            "is_active": true,
            "order": 1
        },
        {
            "name": "Career Support",
            "description": "Bantuan CV review dan mock interview",
            "price": 500000,
            "is_active": true,
            "order": 2
        }
    ]
}
```

**Success Response (201):**

```json
{
  "success": true,
  "message": "Form created successfully",
  "data": {
    "id": "019a7cf6-510c-7348-aeac-af87ad233346",
    "title": "Formulir Pendaftaran Bootcamp React 2025",
    "description": "Daftar bootcamp intensif React JS batch 10",
    "slug": "bootcamp-react-2025",
    "cover_image": "https://example.com/cover.jpg",
    "is_active": true,
    "enable_payment": true,
    "enable_affiliate": true,
    "category": {
      "id": "019a7cf5-f5c7-730f-b5d5-af2c9dc45678",
      "name": "Pendidikan"
    },
    "sections": [
      {
        "id": "019a7cf6-510c-7348-aeac-af87ad233347",
        "title": "Data Pribadi",
        "description": "Isi data diri Anda dengan lengkap",
        "order": 1,
        "fields": [
          {
            "id": "019a7cf6-5879-7251-9793-ba9b150acbab",
            "label": "Nama Lengkap",
            "name": "full_name",
            "type": "text",
            "placeholder": "Masukkan nama lengkap",
            "help_text": "Sesuai KTP",
            "is_required": true,
            "order": 1
          }
        ]
      }
    ],
    "pricing_tiers": [...],
    "upsells": [...],
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z"
  }
}
```

---

## 2. Update Form (All-in-One)

Update form lengkap beserta sections, fields, pricing tiers, dan upsells dalam satu request.

**Endpoint:**

```
PUT /api/forms/{formId}
```

**Request Body:**

```json
{
    "title": "Formulir Pendaftaran Bootcamp React 2025 - UPDATED",
    "description": "Daftar bootcamp intensif React JS batch 10",
    "is_active": true,

    "sections": [
        {
            "id": "019a7cf6-510c-7348-aeac-af87ad233347",
            "title": "Data Pribadi - Updated",
            "description": "Isi data diri Anda dengan lengkap",
            "order": 1,
            "fields": [
                {
                    "id": "019a7cf6-5879-7251-9793-ba9b150acbab",
                    "label": "Nama Lengkap - Updated",
                    "name": "full_name",
                    "type": "text",
                    "is_required": true,
                    "order": 1
                },
                {
                    "label": "Alamat",
                    "name": "address",
                    "type": "textarea",
                    "is_required": false,
                    "order": 2
                }
            ]
        },
        {
            "title": "Section Baru",
            "description": "Section yang baru ditambahkan",
            "order": 2,
            "fields": [
                {
                    "label": "Field Baru",
                    "name": "new_field",
                    "type": "text",
                    "is_required": false,
                    "order": 1
                }
            ]
        }
    ],

    "pricing_tiers": [
        {
            "id": "019a7cf6-5c23-71b2-ab93-568669f93f6d",
            "name": "Early Bird - Updated",
            "price": 2000000,
            "currency": "IDR",
            "is_default": true,
            "is_active": true,
            "order": 1
        }
    ],

    "upsells": [
        {
            "id": "019a7cf7-5879-7251-9793-ba9b150acbab",
            "name": "Mentoring 1-on-1 - Updated",
            "price": 800000,
            "is_active": true,
            "order": 1
        }
    ]
}
```

**Behavior Update:**

-   **Dengan ID**: Update existing item
-   **Tanpa ID**: Create new item
-   **Tidak disebutkan dalam array**: Delete item tersebut

**Example Scenarios:**

### Scenario 1: Update Existing Section

```json
{
    "sections": [
        {
            "id": "existing-section-id",
            "title": "Updated Title"
        }
    ]
}
```

✅ Section akan di-update

### Scenario 2: Add New Section

```json
{
    "sections": [
        {
            "title": "New Section"
        }
    ]
}
```

✅ Section baru akan dibuat

### Scenario 3: Delete Section

Jika section dengan ID tertentu tidak disertakan dalam array, maka akan di-delete.

**Before:**

-   Section A (id: 1)
-   Section B (id: 2)
-   Section C (id: 3)

**Request:**

```json
{
    "sections": [
        { "id": "1", "title": "Section A" },
        { "id": "2", "title": "Section B" }
    ]
}
```

**Result:**

-   Section A ✅ Updated
-   Section B ✅ Updated
-   Section C ❌ Deleted

---

## 3. Get Form Detail

Mendapatkan detail form lengkap dengan sections, fields, pricing tiers, dan upsells.

**Endpoint:**

```
GET /api/forms/{formId}
```

**Success Response (200):**

```json
{
    "success": true,
    "data": {
        "id": "019a7cf6-510c-7348-aeac-af87ad233346",
        "title": "Formulir Pendaftaran Bootcamp React 2025",
        "slug": "bootcamp-react-2025",
        "description": "Daftar bootcamp intensif React JS batch 10",
        "cover_image": "https://example.com/cover.jpg",
        "is_active": true,
        "enable_payment": true,
        "enable_affiliate": true,
        "max_submissions": 100,
        "start_date": "2025-01-01T00:00:00.000000Z",
        "end_date": "2025-12-31T00:00:00.000000Z",
        "settings": {
            "theme": "modern",
            "send_email": true
        },
        "category": {
            "id": "019a7cf5-f5c7-730f-b5d5-af2c9dc45678",
            "name": "Pendidikan",
            "icon": "📚",
            "color": "#4F46E5"
        },
        "sections": [
            {
                "id": "019a7cf6-510c-7348-aeac-af87ad233347",
                "title": "Data Pribadi",
                "description": "Isi data diri Anda dengan lengkap",
                "order": 1,
                "fields": [
                    {
                        "id": "019a7cf6-5879-7251-9793-ba9b150acbab",
                        "label": "Nama Lengkap",
                        "name": "full_name",
                        "type": "text",
                        "placeholder": "Masukkan nama lengkap",
                        "help_text": "Sesuai KTP",
                        "is_required": true,
                        "order": 1,
                        "options": null,
                        "validation_rules": null
                    }
                ]
            }
        ],
        "pricing_tiers": [
            {
                "id": "019a7cf6-5c23-71b2-ab93-568669f93f6d",
                "name": "Early Bird",
                "description": "Harga khusus pendaftar awal",
                "price": 2500000,
                "currency": "IDR",
                "is_default": true,
                "is_active": true,
                "order": 1
            }
        ],
        "upsells": [
            {
                "id": "019a7cf7-5879-7251-9793-ba9b150acbab",
                "name": "Mentoring 1-on-1",
                "description": "Sesi mentoring privat 4x pertemuan",
                "price": 1000000,
                "is_active": true,
                "order": 1
            }
        ],
        "affiliate_rewards": [],
        "created_at": "2025-01-15T10:30:00.000000Z",
        "updated_at": "2025-01-15T10:30:00.000000Z",
        "published_at": null
    }
}
```

---

## 4. List All Forms

Mendapatkan list semua forms dengan pagination.

**Endpoint:**

```
GET /api/forms
```

**Query Parameters:**

-   `category_id` (optional): Filter by category
-   `is_active` (optional): Filter by active status (true/false)
-   `search` (optional): Search by title
-   `per_page` (optional): Items per page (default: 15)

**Example:**

```
GET /api/forms?category_id=019a7cf5-f5c7-730f-b5d5-af2c9dc45678&is_active=true&per_page=10
```

**Success Response (200):**

```json
{
  "success": true,
  "data": [
    {
      "id": "019a7cf6-510c-7348-aeac-af87ad233346",
      "title": "Formulir Pendaftaran Bootcamp React 2025",
      "slug": "bootcamp-react-2025",
      "is_active": true,
      "category": {
        "id": "019a7cf5-f5c7-730f-b5d5-af2c9dc45678",
        "name": "Pendidikan"
      },
      "sections": [...],
      "submissions_count": 45
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

---

## 5. Delete Form

Menghapus form beserta semua sections, fields, pricing tiers, dan upsells.

**Endpoint:**

```
DELETE /api/forms/{formId}
```

**Success Response (200):**

```json
{
    "success": true,
    "message": "Form deleted successfully"
}
```

---

## Field Types

Field types yang tersedia:

| Type       | Description        | Example           |
| ---------- | ------------------ | ----------------- |
| `text`     | Single line text   | Nama, Judul       |
| `email`    | Email address      | user@example.com  |
| `phone`    | Phone number       | 08123456789       |
| `textarea` | Multi-line text    | Deskripsi, Alamat |
| `number`   | Numeric input      | Umur, Jumlah      |
| `select`   | Dropdown selection | Pilih kota        |
| `checkbox` | Multiple selection | Hobi, Skills      |
| `radio`    | Single selection   | Gender, Status    |
| `date`     | Date picker        | Tanggal lahir     |
| `file`     | File upload        | Upload KTP        |

---

## Validation Rules

### Form Level

-   `title`: required, max 255 characters
-   `slug`: unique, max 255 characters
-   `category_id`: must exist in categories table
-   `max_submissions`: minimum 1
-   `end_date`: must be after start_date

### Section Level

-   `title`: required, max 255 characters
-   `order`: minimum 0

### Field Level

-   `label`: required, max 255 characters
-   `type`: must be one of the field types
-   `order`: minimum 0

### Pricing Tier Level

-   `name`: required, max 255 characters
-   `price`: required, minimum 0

### Upsell Level

-   `name`: required, max 255 characters
-   `price`: required, minimum 0

---

## Frontend Implementation Examples

### React Example (Create Form)

```jsx
import { useState } from "react";
import axios from "axios";

function CreateFormPage() {
    const [formData, setFormData] = useState({
        title: "",
        description: "",
        sections: [
            {
                title: "",
                fields: [
                    {
                        label: "",
                        type: "text",
                        is_required: false,
                    },
                ],
            },
        ],
    });

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            const response = await axios.post("/api/forms", formData, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });

            console.log("Form created:", response.data);
            // Redirect atau show success message
        } catch (error) {
            console.error("Error creating form:", error.response.data);
        }
    };

    const addSection = () => {
        setFormData({
            ...formData,
            sections: [
                ...formData.sections,
                {
                    title: "",
                    fields: [],
                },
            ],
        });
    };

    const addField = (sectionIndex) => {
        const newSections = [...formData.sections];
        newSections[sectionIndex].fields.push({
            label: "",
            type: "text",
            is_required: false,
        });
        setFormData({ ...formData, sections: newSections });
    };

    return (
        <form onSubmit={handleSubmit}>
            <input
                type="text"
                value={formData.title}
                onChange={(e) =>
                    setFormData({ ...formData, title: e.target.value })
                }
                placeholder="Form Title"
            />

            {formData.sections.map((section, sectionIndex) => (
                <div key={sectionIndex}>
                    <input
                        type="text"
                        value={section.title}
                        onChange={(e) => {
                            const newSections = [...formData.sections];
                            newSections[sectionIndex].title = e.target.value;
                            setFormData({ ...formData, sections: newSections });
                        }}
                        placeholder="Section Title"
                    />

                    {section.fields.map((field, fieldIndex) => (
                        <div key={fieldIndex}>
                            <input
                                type="text"
                                value={field.label}
                                onChange={(e) => {
                                    const newSections = [...formData.sections];
                                    newSections[sectionIndex].fields[
                                        fieldIndex
                                    ].label = e.target.value;
                                    setFormData({
                                        ...formData,
                                        sections: newSections,
                                    });
                                }}
                                placeholder="Field Label"
                            />

                            <select
                                value={field.type}
                                onChange={(e) => {
                                    const newSections = [...formData.sections];
                                    newSections[sectionIndex].fields[
                                        fieldIndex
                                    ].type = e.target.value;
                                    setFormData({
                                        ...formData,
                                        sections: newSections,
                                    });
                                }}
                            >
                                <option value="text">Text</option>
                                <option value="email">Email</option>
                                <option value="phone">Phone</option>
                                <option value="textarea">Textarea</option>
                                <option value="number">Number</option>
                                <option value="select">Select</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="radio">Radio</option>
                                <option value="date">Date</option>
                                <option value="file">File</option>
                            </select>
                        </div>
                    ))}

                    <button
                        type="button"
                        onClick={() => addField(sectionIndex)}
                    >
                        Add Field
                    </button>
                </div>
            ))}

            <button type="button" onClick={addSection}>
                Add Section
            </button>

            <button type="submit">Create Form</button>
        </form>
    );
}
```

### React Example (Update Form)

```jsx
import { useState, useEffect } from "react";
import axios from "axios";
import { useParams } from "react-router-dom";

function EditFormPage() {
    const { formId } = useParams();
    const [formData, setFormData] = useState(null);

    useEffect(() => {
        fetchForm();
    }, [formId]);

    const fetchForm = async () => {
        try {
            const response = await axios.get(`/api/forms/${formId}`, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });
            setFormData(response.data.data);
        } catch (error) {
            console.error("Error fetching form:", error);
        }
    };

    const handleUpdate = async (e) => {
        e.preventDefault();

        try {
            const response = await axios.put(`/api/forms/${formId}`, formData, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });

            console.log("Form updated:", response.data);
        } catch (error) {
            console.error("Error updating form:", error.response.data);
        }
    };

    const deleteSection = (sectionIndex) => {
        const newSections = formData.sections.filter(
            (_, index) => index !== sectionIndex
        );
        setFormData({ ...formData, sections: newSections });
    };

    if (!formData) return <div>Loading...</div>;

    return (
        <form onSubmit={handleUpdate}>
            <input
                type="text"
                value={formData.title}
                onChange={(e) =>
                    setFormData({ ...formData, title: e.target.value })
                }
            />

            {formData.sections.map((section, index) => (
                <div key={section.id || index}>
                    <input
                        type="text"
                        value={section.title}
                        onChange={(e) => {
                            const newSections = [...formData.sections];
                            newSections[index].title = e.target.value;
                            setFormData({ ...formData, sections: newSections });
                        }}
                    />
                    <button type="button" onClick={() => deleteSection(index)}>
                        Delete Section
                    </button>
                </div>
            ))}

            <button type="submit">Update Form</button>
        </form>
    );
}
```

---

## Error Responses

### 400 Bad Request

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "title": ["The title field is required."],
        "sections.0.title": ["The sections.0.title field is required."],
        "sections.0.fields.0.label": [
            "The sections.0.fields.0.label field is required."
        ]
    }
}
```

### 401 Unauthorized

```json
{
    "message": "Unauthenticated."
}
```

### 404 Not Found

```json
{
    "message": "Form not found."
}
```

---

## Summary

### ✅ Keuntungan MVP Approach:

1. **Single Endpoint**: Frontend hanya perlu hit 1 endpoint untuk create/update form lengkap
2. **Atomic Operations**: Semua perubahan dalam 1 transaction, jadi consistent
3. **Smart Update**: Otomatis create/update/delete berdasarkan ID
4. **Simplified State Management**: Frontend tidak perlu manage multiple API calls
5. **Better UX**: User tidak perlu save berkali-kali

### 🚀 Workflow:

1. **Create Form**: POST `/api/forms` dengan semua data
2. **Update Form**: PUT `/api/forms/{id}` dengan semua data (ID untuk update, tanpa ID untuk create, tidak disebutkan akan delete)
3. **Get Form**: GET `/api/forms/{id}` untuk load data ke form editor
4. **Delete Form**: DELETE `/api/forms/{id}` untuk hapus form

### 📋 Field Structure:

```json
{
  "sections": [
    {
      "id": "uuid", // Jika update
      "title": "string",
      "fields": [
        {
          "id": "uuid", // Jika update
          "label": "string",
          "type": "text|email|phone|etc",
          "is_required": boolean
        }
      ]
    }
  ]
}
```

Dengan approach ini, frontend development akan jauh lebih simple dan cepat! 🎉
