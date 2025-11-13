# Form Submissions API Documentation

## Get Submissions by Form ID

Endpoint untuk mendapatkan semua submissions dari sebuah form tertentu, dengan fitur filter, search, dan statistik.

### Endpoint
```
GET /api/forms/{form-id}/submissions
```

### Authentication
Requires JWT token (Admin only)

### Headers
```
Authorization: Bearer {your-jwt-token}
Content-Type: application/json
```

### URL Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| form-id | UUID | Yes | ID dari form yang ingin dilihat submissionsnya |

### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| per_page | integer | No | Jumlah data per halaman (default: 15) |
| page | integer | No | Halaman yang ingin ditampilkan (default: 1) |
| tier | string | No | Filter by tier: `free` atau `paid` |
| status | string | No | Filter by status: `pending` atau `paid` |
| search | string | No | Search by name, email, phone, or submission number |

### Response Format

#### Success Response (200 OK)
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid-submission-1",
      "submission_number": "SUB-20251114-001",
      "form": {
        "id": "uuid-form-1",
        "title": "Lomba Karya Tulis Ilmiah 2024",
        "slug": "lomba-karya-tulis-ilmiah-2024"
      },
      "pricing_tier": {
        "id": "uuid-tier-1",
        "name": "Gratis",
        "price": 0,
        "currency": "IDR"
      },
      "status": "approved",
      "payment_status": "paid",
      "total_amount": 0,
      "contact_name": "John Doe",
      "contact_email": "john@example.com",
      "contact_phone": "+6281234567890",
      "data": {
        "nama_lengkap": "John Doe",
        "email": "john@example.com",
        "telepon": "081234567890",
        "judul_karya": "Inovasi Teknologi Pendidikan"
      },
      "affiliate_code": null,
      "affiliate_commission": 0,
      "paid_at": "2025-11-14T05:26:45.000000Z",
      "created_at": "2025-11-14T05:26:45.000000Z",
      "updated_at": "2025-11-14T05:26:45.000000Z"
    },
    {
      "id": "uuid-submission-2",
      "submission_number": "SUB-20251114-002",
      "form": {
        "id": "uuid-form-1",
        "title": "Lomba Karya Tulis Ilmiah 2024",
        "slug": "lomba-karya-tulis-ilmiah-2024"
      },
      "pricing_tier": {
        "id": "uuid-tier-2",
        "name": "Premium",
        "price": 50000,
        "currency": "IDR"
      },
      "status": "pending",
      "payment_status": "pending",
      "total_amount": 50000,
      "contact_name": "Jane Smith",
      "contact_email": "jane@example.com",
      "contact_phone": "+6281234567891",
      "payment": {
        "id": "uuid-payment-1",
        "xendit_invoice_url": "https://checkout.xendit.co/web/...",
        "amount": 50000,
        "status": "pending",
        "expired_at": "2025-11-15T05:26:45.000000Z"
      },
      "created_at": "2025-11-14T06:30:00.000000Z",
      "updated_at": "2025-11-14T06:30:00.000000Z"
    }
  ],
  "statistics": {
    "total_submissions": 2,
    "free_tier": 1,
    "paid": 0,
    "total_revenue": 0
  },
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 2
  }
}
```

### Statistics Explanation
| Field | Description |
|-------|-------------|
| total_submissions | Total semua submissions untuk form ini |
| free_tier | Jumlah submissions dengan tier gratis (amount = 0) yang sudah paid |
| paid | Jumlah submissions berbayar (amount > 0) yang sudah paid |
| total_revenue | Total revenue dari submissions yang sudah paid (dalam IDR) |

## Usage Examples

### 1. Get All Submissions for a Form
```bash
curl -X GET "https://orangered-echidna-524962.hostingersite.com/api/forms/abc-123-uuid/submissions" \
  -H "Authorization: Bearer your-jwt-token" \
  -H "Content-Type: application/json"
```

### 2. Filter Free Tier Only
```bash
curl -X GET "https://orangered-echidna-524962.hostingersite.com/api/forms/abc-123-uuid/submissions?tier=free" \
  -H "Authorization: Bearer your-jwt-token"
```

### 3. Filter Paid Tier Only
```bash
curl -X GET "https://orangered-echidna-524962.hostingersite.com/api/forms/abc-123-uuid/submissions?tier=paid" \
  -H "Authorization: Bearer your-jwt-token"
```

### 4. Filter by Status (Pending)
```bash
curl -X GET "https://orangered-echidna-524962.hostingersite.com/api/forms/abc-123-uuid/submissions?status=pending" \
  -H "Authorization: Bearer your-jwt-token"
```

### 5. Filter by Status (Paid)
```bash
curl -X GET "https://orangered-echidna-524962.hostingersite.com/api/forms/abc-123-uuid/submissions?status=paid" \
  -H "Authorization: Bearer your-jwt-token"
```

### 6. Search by Name or Email
```bash
curl -X GET "https://orangered-echidna-524962.hostingersite.com/api/forms/abc-123-uuid/submissions?search=john" \
  -H "Authorization: Bearer your-jwt-token"
```

### 7. Pagination with 20 Items per Page
```bash
curl -X GET "https://orangered-echidna-524962.hostingersite.com/api/forms/abc-123-uuid/submissions?per_page=20&page=1" \
  -H "Authorization: Bearer your-jwt-token"
```

### 8. Combine Multiple Filters
```bash
curl -X GET "https://orangered-echidna-524962.hostingersite.com/api/forms/abc-123-uuid/submissions?tier=paid&status=pending&search=john&per_page=10" \
  -H "Authorization: Bearer your-jwt-token"
```

## Frontend Implementation (React/Next.js)

### Using Fetch API
```javascript
async function getFormSubmissions(formId, filters = {}) {
  const params = new URLSearchParams(filters);
  
  const response = await fetch(
    `https://orangered-echidna-524962.hostingersite.com/api/forms/${formId}/submissions?${params}`,
    {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    }
  );
  
  return await response.json();
}

// Usage examples:
// Get all submissions
const allData = await getFormSubmissions('abc-123-uuid');

// Get only free tier
const freeData = await getFormSubmissions('abc-123-uuid', { tier: 'free' });

// Get paid and pending
const pendingPaid = await getFormSubmissions('abc-123-uuid', { 
  tier: 'paid', 
  status: 'pending' 
});

// Search with pagination
const searchResults = await getFormSubmissions('abc-123-uuid', { 
  search: 'john',
  per_page: 20,
  page: 1
});
```

### Using Axios
```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'https://orangered-echidna-524962.hostingersite.com/api',
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('token')}`,
    'Content-Type': 'application/json'
  }
});

// Get form submissions
async function getFormSubmissions(formId, params = {}) {
  try {
    const response = await api.get(`/forms/${formId}/submissions`, { params });
    return response.data;
  } catch (error) {
    console.error('Error fetching submissions:', error);
    throw error;
  }
}

// Usage:
const data = await getFormSubmissions('abc-123-uuid', {
  tier: 'free',
  status: 'paid',
  search: 'john',
  per_page: 15,
  page: 1
});

console.log('Submissions:', data.data);
console.log('Statistics:', data.statistics);
console.log('Pagination:', data.meta);
```

### React Component Example
```jsx
import { useState, useEffect } from 'react';

function FormSubmissions({ formId }) {
  const [submissions, setSubmissions] = useState([]);
  const [statistics, setStatistics] = useState({});
  const [filters, setFilters] = useState({
    tier: '',
    status: '',
    search: '',
    page: 1,
    per_page: 15
  });
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    fetchSubmissions();
  }, [formId, filters]);

  async function fetchSubmissions() {
    setLoading(true);
    try {
      const response = await getFormSubmissions(formId, filters);
      setSubmissions(response.data);
      setStatistics(response.statistics);
    } catch (error) {
      console.error('Error:', error);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div>
      <h1>Form Responses</h1>
      
      {/* Statistics */}
      <div className="stats">
        <div>Total Submissions: {statistics.total_submissions}</div>
        <div>Free Tier: {statistics.free_tier}</div>
        <div>Paid: {statistics.paid}</div>
        <div>Total Revenue: Rp {statistics.total_revenue?.toLocaleString()}</div>
      </div>

      {/* Filters */}
      <div className="filters">
        <select 
          value={filters.tier} 
          onChange={(e) => setFilters({...filters, tier: e.target.value})}
        >
          <option value="">Semua Tier</option>
          <option value="free">Free</option>
          <option value="paid">Paid</option>
        </select>

        <select 
          value={filters.status} 
          onChange={(e) => setFilters({...filters, status: e.target.value})}
        >
          <option value="">Semua Status</option>
          <option value="pending">Pending</option>
          <option value="paid">Paid</option>
        </select>

        <input
          type="text"
          placeholder="Search..."
          value={filters.search}
          onChange={(e) => setFilters({...filters, search: e.target.value})}
        />
      </div>

      {/* Submissions List */}
      {loading ? (
        <div>Loading...</div>
      ) : (
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Tier</th>
              <th>Status</th>
              <th>Amount</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            {submissions.map((submission) => (
              <tr key={submission.id}>
                <td>{submission.contact_name}</td>
                <td>{submission.contact_email}</td>
                <td>{submission.pricing_tier?.name}</td>
                <td>{submission.payment_status}</td>
                <td>Rp {submission.total_amount?.toLocaleString()}</td>
                <td>{new Date(submission.created_at).toLocaleDateString()}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}
```

## Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "This action is unauthorized."
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Form not found."
}
```

## Notes

1. **Authentication Required**: Endpoint ini hanya bisa diakses oleh admin dengan JWT token
2. **Pagination**: Default 15 items per page, bisa diubah dengan parameter `per_page`
3. **Statistics**: Dihitung real-time berdasarkan data submission di database
4. **Free Tier**: Submission dengan `total_amount = 0` dan `payment_status = 'paid'`
5. **Paid Tier**: Submission dengan `total_amount > 0` dan `payment_status = 'paid'`
6. **Revenue**: Hanya menghitung dari submissions yang sudah paid

## Related Endpoints

- `GET /api/forms/{id}` - Get form details
- `GET /api/submissions/{id}` - Get single submission details
- `GET /api/submissions/statistics` - Get global statistics
- `POST /api/public/submissions` - Submit a form (public)
