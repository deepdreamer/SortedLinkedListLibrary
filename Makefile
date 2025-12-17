.PHONY: help setup install test test-unit test-performance test-all analyse ecs ecs-fix clean build docker-up docker-down

# Default target
help:
	@echo "Available targets:"
	@echo "  make setup            - Set up the entire project (install dependencies)"
	@echo "  make install-dependencies - Install composer dependencies"
	@echo "  make test             - Run unit tests in parallel (uses all CPU cores)"
	@echo "  make test-unit        - Run unit tests in parallel"
	@echo "  make test-performance - Run performance tests in parallel"
	@echo "  make test-all         - Run all tests (unit + performance)"
	@echo "  make build            - Build Docker image"
	@echo "  make docker-up        - Start Docker container"
	@echo "  make docker-down      - Stop Docker container"

# Set up the entire project
setup: build install-dependencies
	@echo "✓ Project setup complete!"

# Install composer dependencies
install-dependencies:
	@echo "Installing composer dependencies..."
	@docker-compose run --rm php-lib composer install
	@echo "✓ Dependencies installed!"

# Run unit tests (default, parallel execution)
test:
	@echo "Running unit tests in parallel..."
	@docker-compose run --rm php-lib composer test-unit
	@echo "✓ Unit tests completed!"

# Run unit tests only (parallel execution)
test-unit:
	@echo "Running unit tests in parallel..."
	@docker-compose run --rm php-lib composer test-unit
	@echo "✓ Unit tests completed!"

# Run performance tests only (using paratest for parallel execution)
test-performance:
	@echo "Running performance tests in parallel (this may take a while)..."
	@docker-compose run --rm php-lib composer test-performance
	@echo "✓ Performance tests completed!"

# Run all tests (unit + performance, parallel execution)
test-all:
	@echo "Running all tests in parallel..."
	@docker-compose run --rm php-lib composer test-all
	@echo "✓ All tests completed!"

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

