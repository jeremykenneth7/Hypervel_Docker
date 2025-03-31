# Hypervel

---

## Prerequisites

Before you begin, ensure you have the following installed on your system:

- [Docker Desktop](https://www.docker.com/products/docker-desktop)
- [Docker Compose](https://docs.docker.com/compose/install/)

---

## Getting Started

### 1. Clone the Repository

Clone this repository to your local machine:

```bash
git clone https://github.com/your-repo/hypervel.git
cd hypervel

```

### 2. Set Up Environment Variables

```bash
cp .env.example .env
```

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=hypervel
DB_USERNAME=root
DB_PASSWORD=root

### 3. Build and Start the Containers

```bash
docker-compose up --build -d
```
### 4. Install Dependencies

```bash
docker exec -it hypervel_app composer install
```

### 5. Generate Application Key

```bash
docker exec -it hypervel_app php artisan key:generate
```

### 6.  Run Database Migrations

```bash
docker exec -it hypervel_app php artisan migrate
```

### 7.  Access the Application

```bash
http://localhost:8000
```

Stopping the Application
To stop the application, run:
- `docker-compose down`

#   H y p e r v e l _ D o c k e r  
 