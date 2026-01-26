# Payment Integration System Demo

This project serves as a technical demonstration of a secure, production-ready payment integration flow. It bridges the gap between client-side tokenization (Google Pay) and a resilient PHP backend, emphasizing security, idempotency, and asynchronous event handling.

---

## 🛠 Key Architectural Features

### 1. Secure Client-Side Tokenization
* **Google Pay API Integration**: Implements the latest Google Pay SDK to handle sensitive card data without it ever touching our servers (reducing PCI DSS scope).
* **Anti-Fraud Telemetry**: Captures `deviceFingerprint` and environmental metadata to assist backend risk engines in detecting automated or suspicious transactions.
* **Asynchronous UX**: Managed UI states to prevent double-clicks and provide real-time feedback during the tokenization handshake.

### 2. Resilient Backend (PHP 8.x)
* **Idempotency Protection**: Prevents duplicate charges by validating `order_id` status before initiating provider API calls.
* **Strict Typing & Error Handling**: Utilizes PHP's `declare(strict_types=1)` and custom Exception handling to manage provider-specific API errors (e.g., card declined vs. network timeout).
* **Provider SDK Integration**: Demonstration of secure credential management using environment variables (`getenv`).

### 3. Webhook Infrastructure & Security
* **Signature Verification**: Implements HMAC-SHA256 signature validation on incoming webhooks to prevent "replay attacks" or spoofed payment success notifications.
* **Event-Driven Logic**: Asynchronous handling of `payment_intent.succeeded` and `payment_failed` events to ensure database consistency even if the user closes their browser.

---

## 🚀 Technical Stack
* **Frontend**: JavaScript (ES6+), Google Pay API.
* **Backend**: PHP 8.2+ (Clean Architecture approach).
* **Security**: HMAC Signature Validation, Device Fingerprinting, Environment-based Secret Management.

---

## 📦 Installation & Setup
1. **Clone the repo**:  
   `git clone https://github.com/vik-op/Payment-Integration-System-Demo-Google-Pay-Backend-Security`
2. **Environment**:  
   Copy `.env.example` to `.env` and add your Gateway Secret Keys.
3. **Mocking Webhooks**:  
   Use `ngrok` to tunnel your local environment for testing webhook notifications.

---

## 🛡 Security Note
This repository is for demonstration purposes. **Always** use Sandbox/Test keys provided by your payment gateway during development. Never commit `.env` files or real merchant credentials to version control.
