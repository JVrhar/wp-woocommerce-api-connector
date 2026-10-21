# WooCommerce External API Connector 🛍️

An enterprise-ready WordPress plugin that automatically synchronizes WooCommerce order data with external CRMs or third-party logistics endpoints via secure REST API calls.

---

## 🌟 Key Capabilities

- **Automated Event Triggers:** Listens to `woocommerce_order_status_completed` hooks to fire API dispatches asynchronously.
- **Robust HTTP Handling:** Utilizes `wp_remote_post` instead of raw cURL for maximum stability and compatibility across server environments.
- **Data Protection & Validation:** Sanitizes and formats WooCommerce order objects into secure JSON structures.
- **Error Handling & Meta Storage:** Checks for API response status codes and logs failures to system log files while tracking sync state in order metadata.

---

## 🛠️ Tech Stack

- **PHP:** 8.x (OOP Paradigm)
- **CMS:** WordPress & WooCommerce Core APIs
- **Protocol:** HTTP REST / JSON Payload
