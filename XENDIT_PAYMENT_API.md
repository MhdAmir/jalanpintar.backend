# Xendit Payment Integration API Documentation

## Overview

This API integrates Xendit payment gateway for form submissions that require payment. It automatically handles:
- Free tier (price = 0) → Auto-marked as **paid**
- Paid tier (price > 0) → Generate Xendit invoice → Wait for payment webhook

---

## Configuration

### 1. Environment Variables

Add to your `.env`:

```env
XENDIT_API_KEY=xnd_development_xxxxx
XENDIT_WEBHOOK_TOKEN=your_webhook_verification_token
XENDIT_SUCCESS_REDIRECT_URL=https://yourfrontend.com/payment/success
XENDIT_FAILURE_REDIRECT_URL=https://yourfrontend.com/payment/failed
FRONTEND_URL=https://yourfrontend.com
```

### 2. Get Xendit Credentials

1. **API Key**: https://dashboard.xendit.co/settings/developers#api-keys
2. **Webhook Token**: Generate random string (e.g., `openssl rand -hex 32`)

### 3. Setup Xendit Webhook

Go to Xendit Dashboard → Settings → Webhooks → Add webhook:

```
URL: https://yourapi.com/api/public/payments/webhook
Events: Invoice Paid, Invoice Expired
Verification Token: [Your XENDIT_WEBHOOK_TOKEN]
```

---

## API Endpoints

### 1. Submit Form (Public)

```http
POST /api/public/submissions
Content-Type: multipart/form-data
```

**Request Body:**

```json
{
  "form_slug": "pendaftaran-siswa-baru-2024-2025",
  "pricing_tier_id": "019a7ea2-364e-723b-afd7-104890cbff54",
  "data": {
    "nama_lengkap": "Ahmad Fauzi Ramadhan",
    "nik": "3273011234567890",
    "jenis_kelamin": "L",
    "tanggal_lahir": "2010-05-15",
    "email": "ahmad.fauzi@example.com",
    "telepon": "081234567890",
    "alamat": "Jl. Merdeka No. 123...",
    "provinsi": "jawa_barat",
    ...
  },
  "affiliate_code": "AHMAD2024",
  "data[foto]": <file>,
  "data[ktp]": <file>,
  "data[ijazah]": <file>
}
```

**Response (Free Tier):**

```json
{
  "success": true,
  "message": "Submission created successfully",
  "data": {
    "id": "019a7ea3-xxxx",
    "status": "completed",
    "payment_status": "paid",
    "total_amount": "0.00",
    ...
  }
}
```

**Response (Paid Tier):**

```json
{
  "success": true,
  "message": "Submission created successfully. Please complete payment.",
  "data": {
    "id": "019a7ea3-xxxx",
    "status": "pending_payment",
    "payment_status": "pending_payment",
    "total_amount": "99000.00",
    "payment": null,
    "next_action": {
      "type": "create_payment",
      "endpoint": "/api/submissions/{submission_id}/payment"
    }
  }
}
```

---

### 2. Create Payment Invoice

```http
POST /api/submissions/{submissionId}/payment
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**

```json
{
  "pricing_tier_id": "019a7ea2-364e-723b-afd7-104890cbff54"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Payment invoice created successfully",
  "data": {
    "payment": {
      "id": "019a7ea4-xxxx",
      "xendit_invoice_id": "64cdfexxxx",
      "xendit_invoice_url": "https://checkout.xendit.co/web/64cdfexxxx",
      "external_id": "SUBMISSION-019a7ea3-xxxx-1731517200",
      "amount": "99000.00",
      "currency": "IDR",
      "status": "pending",
      "expired_at": "2025-11-14T19:30:00.000000Z"
    },
    "invoice_url": "https://checkout.xendit.co/web/64cdfexxxx"
  }
}
```

**Frontend Action:**
Redirect user to `invoice_url` for payment.

---

### 3. Xendit Webhook (Called by Xendit)

```http
POST /api/public/payments/webhook
X-Callback-Token: {XENDIT_WEBHOOK_TOKEN}
Content-Type: application/json
```

**Webhook Payload (Invoice Paid):**

```json
{
  "id": "64cdfexxxx",
  "external_id": "SUBMISSION-019a7ea3-xxxx-1731517200",
  "user_id": "xxxxx",
  "status": "PAID",
  "merchant_name": "Your Business",
  "amount": 99000,
  "paid_amount": 99000,
  "bank_code": "BCA",
  "paid_at": "2025-11-13T18:30:00.000Z",
  "payer_email": "ahmad.fauzi@example.com",
  "description": "Pendaftaran Siswa Baru 2024/2025 - Premium",
  "payment_method": "BANK_TRANSFER",
  "payment_channel": "BCA"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Webhook processed successfully",
  "data": {
    "id": "019a7ea4-xxxx",
    "status": "paid",
    "paid_at": "2025-11-13T18:30:00.000000Z"
  }
}
```

**What happens:**
- Payment status → `paid`
- Submission status → `paid`
- Submission `paid_at` → updated

---

### 4. Get Payment Details

```http
GET /api/payments/{paymentId}
Authorization: Bearer {token}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "id": "019a7ea4-xxxx",
    "submission_id": "019a7ea3-xxxx",
    "xendit_invoice_url": "https://checkout.xendit.co/web/64cdfexxxx",
    "amount": "99000.00",
    "status": "paid",
    "payment_method": "BANK_TRANSFER",
    "payment_channel": "BCA",
    "paid_at": "2025-11-13T18:30:00.000000Z",
    "submission": { ... },
    "form": { ... },
    "pricing_tier": { ... }
  }
}
```

---

### 5. Get Payment by External ID (Public)

```http
GET /api/public/payments/external/{externalId}
```

**Use Case:** For success/failure redirect pages

**Response:**

```json
{
  "success": true,
  "data": {
    "id": "019a7ea4-xxxx",
    "status": "paid",
    "amount": "99000.00",
    "submission": { ... }
  }
}
```

---

## Frontend Flow

### Scenario 1: Free Tier (Amount = 0)

```javascript
// 1. Submit form
const response = await fetch('/api/public/submissions', {
  method: 'POST',
  body: formData
});

const result = await response.json();

if (result.data.payment_status === 'paid') {
  // ✅ Free tier - No payment needed
  router.push('/success');
}
```

### Scenario 2: Paid Tier

```javascript
// 1. Submit form
const submitResponse = await fetch('/api/public/submissions', {
  method: 'POST',
  body: formData
});

const submission = await submitResponse.json();

if (submission.data.status === 'pending_payment') {
  
  // 2. Create payment invoice
  const paymentResponse = await fetch(`/api/submissions/${submission.data.id}/payment`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      pricing_tier_id: selectedTierId
    })
  });
  
  const payment = await paymentResponse.json();
  
  // 3. Redirect to Xendit payment page
  window.location.href = payment.data.invoice_url;
}
```

### Success Page (After Payment)

```javascript
// URL: /payment/success?external_id=SUBMISSION-xxx-1731517200

const urlParams = new URLSearchParams(window.location.search);
const externalId = urlParams.get('external_id');

// Get payment status
const response = await fetch(`/api/public/payments/external/${externalId}`);
const payment = await response.json();

if (payment.data.status === 'paid') {
  // Show success message
  showSuccessMessage('Payment successful!');
} else {
  // Show pending message
  showPendingMessage('Payment is being processed...');
}
```

---

## Payment Status Flow

```
┌─────────────────┐
│ Submit Form     │
└────────┬────────┘
         │
         ├─── Free Tier (amount = 0)
         │    └──> status: "completed"
         │         payment_status: "paid"
         │
         └─── Paid Tier (amount > 0)
              └──> status: "pending_payment"
                   payment_status: "pending_payment"
                   │
                   ├─── Create Payment Invoice
                   │    └──> Xendit invoice URL
                   │         │
                   │         ├─── User pays
                   │         │    └──> Webhook: "PAID"
                   │         │         └──> status: "paid"
                   │         │              payment_status: "paid"
                   │         │
                   │         ├─── Expired (24h)
                   │         │    └──> Webhook: "EXPIRED"
                   │         │         └──> status: "draft"
                   │         │              payment_status: "expired"
                   │         │
                   │         └─── Payment Failed
                   │              └──> Webhook: "FAILED"
                   │                   └──> status: "draft"
                   │                        payment_status: "failed"
                   │
                   └─── Don't create payment
                        └──> status: "pending_payment"
```

---

## Testing

### Test with Xendit Test Mode

1. Use test API key: `xnd_development_xxxxx`
2. Test cards: https://developers.xendit.co/api-reference/#test-scenarios

```bash
# Create submission
curl -X POST http://localhost:8000/api/public/submissions \
  -F "form_slug=pendaftaran-siswa-baru-2024-2025" \
  -F "pricing_tier_id=019a7ea2-364e-723b-afd7-104890cbff54" \
  -F "data[nama_lengkap]=Test User" \
  -F "data[email]=test@example.com"

# Create payment
curl -X POST http://localhost:8000/api/submissions/{id}/payment \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"pricing_tier_id": "019a7ea2-364e-723b-afd7-104890cbff54"}'

# Simulate webhook
curl -X POST http://localhost:8000/api/public/payments/webhook \
  -H "X-Callback-Token: your_webhook_token" \
  -H "Content-Type: application/json" \
  -d '{
    "external_id": "SUBMISSION-xxx-1731517200",
    "status": "PAID",
    "amount": 99000,
    "payment_method": "BANK_TRANSFER",
    "payment_channel": "BCA"
  }'
```

---

## Database Schema

### payments table

```sql
CREATE TABLE payments (
  id UUID PRIMARY KEY,
  submission_id UUID FOREIGN KEY,
  form_id UUID FOREIGN KEY,
  pricing_tier_id UUID FOREIGN KEY,
  xendit_invoice_id VARCHAR UNIQUE,
  xendit_invoice_url VARCHAR,
  external_id VARCHAR UNIQUE,
  amount DECIMAL(15,2),
  currency VARCHAR(3) DEFAULT 'IDR',
  status ENUM('pending', 'paid', 'expired', 'failed'),
  payment_method VARCHAR,
  payment_channel VARCHAR,
  paid_at TIMESTAMP NULL,
  expired_at TIMESTAMP NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

---

## Security Notes

1. **Webhook Verification**: Always verify `X-Callback-Token` header
2. **HTTPS Only**: Use HTTPS in production for webhook URL
3. **API Key**: Never expose API key in frontend code
4. **External ID**: Use unpredictable external IDs (include timestamp/random)

---

## Support

- Xendit Docs: https://developers.xendit.co/
- Xendit Dashboard: https://dashboard.xendit.co/
- Support: support@xendit.co
