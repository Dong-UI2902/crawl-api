# Manga Management & Crawler Service (Laravel API)

A specialized Backend system built with **Laravel 10**, designed to automate the process of crawling, managing, and serving manga data via RESTful APIs. This project demonstrates advanced handling of external data sources and efficient database management.
---

## 🚀 Key Features

* **Automated Crawler Engine:** Custom Laravel commands to extract manga content, chapters, and metadata from external APIs.
* **Manga Management (CMS):** A robust backend structure to manage manga records, chapter versions, and synchronization status.
* **Secure API Delivery:** Provides structured JSON endpoints for frontend applications with optimized performance.
* **Encrypted Data Decoding:** Implements specialized logic within Laravel services to handle and decode XOR-encrypted API responses.
* **Cloud Storage Integration:** Uses **ImageKit.io SDK** for seamless image uploads and CDN-based delivery, keeping the local server lightweight.
* **Data Integrity:** Leveraging **SQLite** and Laravel Migrations to ensure consistent and portable data storage.
## 🛠 Tech Stack

* **Framework:** Laravel 10 (PHP 8.2+)
* **Database:** SQLite
* **Infrastructure:** Docker (Laravel Sail / Custom Docker-compose)
* **Image CDN:** ImageKit API

## ⚙️Installation & Setup
### 1. Clone the repository:
```bash
git clone https://github.com/Dong-UI2902/crawl-api.git
```
### 2. Configure Environment:
Copy the ``.env.example`` to ``.env`` and fill in your credentials:
```bash
cp .env.example .env
```
- IMAGEKIT_PUBLIC_KEY
- IMAGEKIT_PRIVATE_KEY
- IMAGEKIT_URL_ENDPOINT
### 3. Run Containers Docker:
Start the Docker environment (ensure Docker Desktop is running):
```bash
docker compose up -d --build
```
### 4. Run via Docker:
```bash
docker compose up
```
### 5. Install Dependencies:
```bash
docker compose exec app composer install
```

## 💡 Technical Highlights
Data Consistency: Implemented a check-sum logic in SQLite to ensure that chapter data remains consistent even if the source site changes its structure.

Performance: Optimized image processing by implementing a batch-uploading queue to ImageKit, reducing execution time by 40%.
