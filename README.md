# Mini Ticket Tracker

A minimal full-stack ticket tracking application 

## Tech Stack

- **Backend:** PHP 8.4 — vanilla PHP REST API
- **Database:** PostgreSQL
- **Frontend:** React with Vite
- **Testing:** PHPUnit-based API integration tests
- **Local development:** Docker Compose

## Features

- Create tickets with a title, optional description, status, and priority.
- List tickets with optional status filtering and pagination.
- Retrieve an individual ticket by ID.
- Update ticket status and priority.
- View ticket counts grouped by status.
- Validate incoming data and return appropriate HTTP responses.

## Project Structure

```text
mini-ticket-tracker/
├── frontend/                  # React frontend and Vite configuration
├── server/
│   ├── database/            # Database schema
│   ├── public/              # API entry point
│   ├── src/                 # Database access, routing and controllers
│   └── tests/               # API integration tests
├── compose.yaml             # Local development services
└── README.md
```

## Getting Started

### Prerequisites

- Docker and Docker Compose
- Node.js and npm

### 1. Start the backend and database

From the project root:

```bash
docker compose up --build
```

The API is available at `http://localhost:8000`.

The database schema is initialized when PostgreSQL creates its data directory for the first time.

### 2. Start the frontend

In a separate terminal:

```bash
cd frontend
npm install
npm run dev
```

Open the local URL printed by Vite. The development server proxies `/api` requests to the PHP API.

### 3. Run the tests

With the API and database running, execute the PHPUnit integration tests from the `server/` directory:

```bash
vendor/bin/phpunit tests/TicketApiTest.php
```

The tests exercise ticket creation, ticket updates, and the statistics endpoint through HTTP requests.

## API Endpoints

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/tickets` | List tickets |
| GET | `/api/tickets/{id}` | Retrieve a ticket |
| POST | `/api/tickets` | Create a ticket |
| PATCH | `/api/tickets/{id}` | Update ticket status or priority |
| GET | `/api/tickets/stats` | Retrieve ticket counts by status |

The ticket list supports status filtering and pagination.

## Decisions & Trade-offs

### 1. Vanilla PHP instead of a backend framework

I chose vanilla PHP to keep a small application focused on the fundamentals: HTTP request handling, routing, input validation, SQL queries, and response formatting. A framework such as Laravel would provide useful abstractions, but its setup and conventions were not necessary for the scope of this exercise. The trade-off is that routing, validation, and error handling require more explicit implementation and maintenance.

### 2. PostgreSQL with a relational schema

PostgreSQL provides structured storage, reliable transactions, and constraints for enforcing valid ticket statuses, priorities, and required fields. Database-level constraints complement API validation, helping protect data integrity

### 3. React for the frontend

React provides reusable UI components and state management for the ticket list, creation form, and statistics. 

### 4. Local component state instead of Redux

The frontend uses React state and effects because the application size and the straightforward data flow. If the application grew to include more complex shared state or workflows, a dedicated state-management solution could become neccessary.


### 5. No authentication or authorization

Authentication and role-based permissions were excluded to keep the implementation focused on the ticket-management workflow and API fundamentals. This is a deliberate scope limitation, not a production-ready security posture. A deployed production version would require authentication, authorization, and appropriate access controls.

### 6. API integration tests instead of a separate test database

The PHPUnit tests make real HTTP requests to the running API and verify responses for ticket creation, updates, and statistics. This checks the interaction between the HTTP layer, application code, and database rather than testing isolated functions alone.

For this small exercise, the tests use the configured development environment instead of a separately provisioned test database. The trade-off is that test records persist and test results can depend on the existing database state. A more mature setup would isolate test data in a dedicated database and clean up or reset state between tests.