# Reorder API Documentation

## Overview

API endpoints untuk mengubah urutan (reorder) items seperti sections, fields, pricing tiers, dan upsells.

---

## Authentication

Semua endpoint reorder memerlukan JWT authentication.

Header:

```
Authorization: Bearer {your_jwt_token}
```

---

## 1. Reorder Sections

Mengubah urutan sections dalam sebuah form.

**Endpoint:**

```
POST /api/sections/reorder
```

**Request Body:**

```json
{
    "items": [
        {
            "id": "019a7cf6-510c-7348-aeac-af87ad233346",
            "order": 1
        },
        {
            "id": "019a7cf6-510c-7348-aeac-af87ad233347",
            "order": 2
        },
        {
            "id": "019a7cf6-510c-7348-aeac-af87ad233348",
            "order": 3
        }
    ]
}
```

**Validation Rules:**

-   `items`: required, array, minimum 1 item
-   `items.*.id`: required, UUID format, must exist in sections table
-   `items.*.order`: required, integer, minimum 0

**Success Response (200):**

```json
{
    "success": true,
    "message": "Sections reordered successfully"
}
```

**Error Response (422):**

```json
{
    "message": "The items.0.id field is required.",
    "errors": {
        "items.0.id": ["The items.0.id field is required."]
    }
}
```

---

## 2. Reorder Fields

Mengubah urutan fields dalam sebuah section.

**Endpoint:**

```
POST /api/fields/reorder
```

**Request Body:**

```json
{
    "items": [
        {
            "id": "019a7cf6-5879-7251-9793-ba9b150acbab",
            "order": 1
        },
        {
            "id": "019a7cf6-5879-7251-9793-ba9b150acbac",
            "order": 2
        },
        {
            "id": "019a7cf6-5879-7251-9793-ba9b150acbad",
            "order": 3
        }
    ]
}
```

**Validation Rules:**

-   `items`: required, array, minimum 1 item
-   `items.*.id`: required, UUID format, must exist in fields table
-   `items.*.order`: required, integer, minimum 0

**Success Response (200):**

```json
{
    "success": true,
    "message": "Fields reordered successfully"
}
```

---

## 3. Reorder Pricing Tiers

Mengubah urutan pricing tiers dalam sebuah form.

**Endpoint:**

```
POST /api/pricing-tiers/reorder
```

**Request Body:**

```json
{
    "items": [
        {
            "id": "019a7cf6-5c23-71b2-ab93-568669f93f6d",
            "order": 1
        },
        {
            "id": "019a7cf6-5c23-71b2-ab93-568669f93f6e",
            "order": 2
        },
        {
            "id": "019a7cf6-5c23-71b2-ab93-568669f93f6f",
            "order": 3
        }
    ]
}
```

**Validation Rules:**

-   `items`: required, array, minimum 1 item
-   `items.*.id`: required, UUID format, must exist in pricing_tiers table
-   `items.*.order`: required, integer, minimum 0

**Success Response (200):**

```json
{
    "success": true,
    "message": "Pricing tiers reordered successfully"
}
```

---

## 4. Reorder Upsells

Mengubah urutan upsells dalam sebuah form.

**Endpoint:**

```
POST /api/upsells/reorder
```

**Request Body:**

```json
{
    "items": [
        {
            "id": "019a7cf7-5879-7251-9793-ba9b150acbab",
            "order": 1
        },
        {
            "id": "019a7cf7-5879-7251-9793-ba9b150acbac",
            "order": 2
        },
        {
            "id": "019a7cf7-5879-7251-9793-ba9b150acbad",
            "order": 3
        }
    ]
}
```

**Validation Rules:**

-   `items`: required, array, minimum 1 item
-   `items.*.id`: required, UUID format, must exist in upsells table
-   `items.*.order`: required, integer, minimum 0

**Success Response (200):**

```json
{
    "success": true,
    "message": "Upsells reordered successfully"
}
```

---

## Usage Examples

### Using cURL

**Reorder Sections:**

```bash
curl -X POST "http://localhost:8000/api/sections/reorder" \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"id": "019a7cf6-510c-7348-aeac-af87ad233346", "order": 2},
      {"id": "019a7cf6-510c-7348-aeac-af87ad233347", "order": 1},
      {"id": "019a7cf6-510c-7348-aeac-af87ad233348", "order": 3}
    ]
  }'
```

**Reorder Fields:**

```bash
curl -X POST "http://localhost:8000/api/fields/reorder" \
  -H "Authorization: Bearer your_jwt_token" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"id": "019a7cf6-5879-7251-9793-ba9b150acbab", "order": 3},
      {"id": "019a7cf6-5879-7251-9793-ba9b150acbac", "order": 1},
      {"id": "019a7cf6-5879-7251-9793-ba9b150acbad", "order": 2}
    ]
  }'
```

### Using JavaScript (Fetch API)

```javascript
// Reorder Sections
async function reorderSections(items) {
    const response = await fetch("http://localhost:8000/api/sections/reorder", {
        method: "POST",
        headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
        },
        body: JSON.stringify({ items }),
    });

    return await response.json();
}

// Example usage
const newOrder = [
    { id: "019a7cf6-510c-7348-aeac-af87ad233346", order: 2 },
    { id: "019a7cf6-510c-7348-aeac-af87ad233347", order: 1 },
    { id: "019a7cf6-510c-7348-aeac-af87ad233348", order: 3 },
];

reorderSections(newOrder).then((result) => {
    console.log(result);
});
```

### Using Axios

```javascript
import axios from "axios";

// Reorder Fields
const reorderFields = async (items) => {
    try {
        const response = await axios.post(
            "http://localhost:8000/api/fields/reorder",
            { items },
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    "Content-Type": "application/json",
                },
            }
        );
        return response.data;
    } catch (error) {
        console.error("Error reordering fields:", error.response.data);
        throw error;
    }
};

// Example usage
const newFieldOrder = [
    { id: "019a7cf6-5879-7251-9793-ba9b150acbab", order: 1 },
    { id: "019a7cf6-5879-7251-9793-ba9b150acbac", order: 2 },
];

reorderFields(newFieldOrder);
```

---

## Frontend Implementation Example

### React Drag & Drop with react-beautiful-dnd

```jsx
import { DragDropContext, Droppable, Draggable } from "react-beautiful-dnd";
import { useState } from "react";

function SectionList({ sections }) {
    const [items, setItems] = useState(sections);

    const handleDragEnd = async (result) => {
        if (!result.destination) return;

        const reorderedItems = Array.from(items);
        const [removed] = reorderedItems.splice(result.source.index, 1);
        reorderedItems.splice(result.destination.index, 0, removed);

        // Update local state
        setItems(reorderedItems);

        // Prepare data for API
        const updates = reorderedItems.map((item, index) => ({
            id: item.id,
            order: index + 1,
        }));

        // Send to API
        try {
            await fetch("/api/sections/reorder", {
                method: "POST",
                headers: {
                    Authorization: `Bearer ${token}`,
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ items: updates }),
            });
        } catch (error) {
            console.error("Failed to reorder:", error);
            // Revert local state on error
            setItems(sections);
        }
    };

    return (
        <DragDropContext onDragEnd={handleDragEnd}>
            <Droppable droppableId="sections">
                {(provided) => (
                    <div {...provided.droppableProps} ref={provided.innerRef}>
                        {items.map((section, index) => (
                            <Draggable
                                key={section.id}
                                draggableId={section.id}
                                index={index}
                            >
                                {(provided) => (
                                    <div
                                        ref={provided.innerRef}
                                        {...provided.draggableProps}
                                        {...provided.dragHandleProps}
                                    >
                                        {section.title}
                                    </div>
                                )}
                            </Draggable>
                        ))}
                        {provided.placeholder}
                    </div>
                )}
            </Droppable>
        </DragDropContext>
    );
}
```

---

## Notes

### Best Practices

1. **Send Complete Order**: Kirim semua items dengan order yang baru, bukan hanya yang berubah
2. **Optimistic Updates**: Update UI dulu, baru kirim ke server untuk UX yang lebih baik
3. **Error Handling**: Siapkan rollback jika API gagal
4. **Validation**: Pastikan order tidak ada yang duplicate

### Performance Considerations

1. **Batch Updates**: API ini sudah menggunakan batch update untuk efisiensi
2. **Debouncing**: Untuk drag & drop, pertimbangkan debounce untuk menghindari terlalu banyak request
3. **Transaction**: Semua updates dalam satu request untuk memastikan data consistency

### Order Numbering

-   Order dimulai dari 0 atau 1 (terserah preferensi)
-   Yang penting adalah **relatif order** antar items
-   Database akan menyimpan exactly seperti yang dikirim
-   Frontend bertanggung jawab untuk generate order yang benar

---

## Error Responses

### 401 Unauthorized

```json
{
    "message": "Unauthenticated."
}
```

**Cause**: Token JWT tidak valid atau expired

### 422 Validation Error

```json
{
    "message": "The items.0.id field must be a valid UUID.",
    "errors": {
        "items.0.id": ["The items.0.id field must be a valid UUID."]
    }
}
```

**Cause**: Data tidak sesuai validation rules

### 404 Not Found

```json
{
    "message": "The selected items.0.id is invalid.",
    "errors": {
        "items.0.id": ["The selected items.0.id is invalid."]
    }
}
```

**Cause**: ID yang dikirim tidak ditemukan di database

---

## Testing with Postman

1. **Login** terlebih dahulu untuk mendapatkan token
2. Copy token ke Authorization header
3. Pilih method POST
4. Set URL endpoint (misal: `/api/sections/reorder`)
5. Di Body, pilih raw dan JSON
6. Paste example request body
7. Click Send

**Example Postman Collection:**

```json
{
    "info": {
        "name": "Form Builder - Reorder APIs"
    },
    "item": [
        {
            "name": "Reorder Sections",
            "request": {
                "method": "POST",
                "url": "{{baseUrl}}/api/sections/reorder",
                "header": [
                    {
                        "key": "Authorization",
                        "value": "Bearer {{token}}"
                    }
                ],
                "body": {
                    "mode": "raw",
                    "raw": "{\n  \"items\": [\n    {\"id\": \"{{sectionId1}}\", \"order\": 1},\n    {\"id\": \"{{sectionId2}}\", \"order\": 2}\n  ]\n}"
                }
            }
        }
    ]
}
```

---

## Summary

✅ **4 Reorder Endpoints**:

-   `POST /api/sections/reorder` - Reorder sections dalam form
-   `POST /api/fields/reorder` - Reorder fields dalam section
-   `POST /api/pricing-tiers/reorder` - Reorder pricing tiers
-   `POST /api/upsells/reorder` - Reorder upsells

✅ **Request Format**:

```json
{
    "items": [
        { "id": "uuid", "order": 1 },
        { "id": "uuid", "order": 2 }
    ]
}
```

✅ **Authentication**: JWT Token required

✅ **Response**: Simple success/error message

✅ **Use Cases**:

-   Drag & drop functionality
-   Manual order adjustment
-   Bulk reordering
-   Form builder interface
