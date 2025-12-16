.PHONY: help setup install test analyse ecs ecs-fix clean build docker-up docker-down

# Default target
help:
	@echo "Available targets:"
	@echo "  make setup       - Set up the entire project (install dependencies)"
	@echo "  make install-dependencies     - Install composer dependencies"
	@echo "  make test        - Run tests using Docker"
	@echo "  make build       - Build Docker image"
	@echo "  make docker-up   - Start Docker container"
	@echo "  make docker-down - Stop Docker container"

# Set up the entire project
setup: build install-dependencies
	@echo "✓ Project setup complete!"

# Install composer dependencies
install-dependencies:
	@echo "Installing composer dependencies..."
	@docker-compose run --rm php-lib composer install
	@echo "✓ Dependencies installed!"

# Run tests
test:
	@echo "Running tests..."
	@docker-compose run --rm php-lib composer test
	@echo "✓ Tests completed!"

# Build Docker image
build:
	@echo "Building Docker image..."
	@docker-compose build
	@echo "✓ Docker image built!"

# Start Docker container
docker-up:
	@echo "Starting Docker container..."
	@docker-compose up -d
	@echo "✓ Docker container started!"

# Stop Docker container
docker-down:
	@echo "Stopping Docker container..."
	@docker-compose down
	@echo "✓ Docker container stopped!"

