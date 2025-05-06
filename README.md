# Linkuy Connect Services

Linkuy Connect Services is a backend service platform that provides core functionality for the Linkuy Connect ecosystem. This repository contains the microservices and APIs that power the Linkuy Connect platform.

## Overview

This project serves as the backbone of the Linkuy Connect platform, handling various services including but not limited to:

- User authentication and authorization
- Data processing and management
- API integrations
- Business logic implementation

## Getting Started

### Prerequisites

- Docker
- Docker Compose

### Development Setup

1. Clone the repository:

```bash
git clone https://github.com/nicolasmoreira/linkuy_connect_services.git
cd linkuy_connect_services
```

2. Configure environment variables:
   Create a `.env` file in the root directory with the following content:

```env
POSTGRES_VERSION=16
POSTGRES_DB=app
POSTGRES_USER=app
POSTGRES_PASSWORD=!ChangeMe!

# Symfony
APP_ENV=dev
APP_SECRET=your_secret_here
DATABASE_URL="postgresql://${POSTGRES_USER}:${POSTGRES_PASSWORD}@database:5432/${POSTGRES_DB}?serverVersion=${POSTGRES_VERSION}&charset=utf8"
```

3. Start the development environment:

```bash
# Build Docker images
docker compose build --parallel

# Start containers
docker compose up -d
```

4. Access the application:

- Web application: http://localhost:8000
- PostgreSQL database: localhost:5432

### Development Workflow

- The application code is mounted as a volume, so changes are reflected immediately
- Symfony's built-in web server is used for development
- PostgreSQL is used as the database

## Project Structure

```
.
├── bin/                    # Executable files
├── config/                 # Configuration files
├── migrations/            # Database migrations
├── public/               # Public web directory
├── src/                  # Application source code
├── templates/            # Twig templates
└── vendor/              # Composer dependencies
```

## Development

The project is built using modern technologies and follows best practices for microservices architecture. Each service is designed to be independent, scalable, and maintainable.

### Docker Services

- **PHP**: PHP 8.2 CLI with Symfony server
- **PostgreSQL**: Database server

### Useful Commands

```bash
# View logs
docker compose logs -f

# Execute commands in PHP container
docker compose exec php [command]

# Stop containers
docker compose down

# Rebuild containers
docker compose up -d --build --parallel

# Run Symfony commands
docker compose exec php symfony [command]

# Run database migrations
docker compose exec php bin/console doctrine:migrations:migrate
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the Apache License 2.0 - see the [LICENSE](LICENSE) file for details.

Copyright 2024 Linkuy Connect

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
