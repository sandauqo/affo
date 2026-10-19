# Feast for Odin

## Run the tests with Docker

Install Docker with Docker Compose, then run this from the project directory:

```sh
docker compose up --build
```

This builds the PHP 8.4 test image and runs the PHPUnit suite. The project
directory is mounted into the container, so the tests use your current files.
The container exits when the tests finish.

To run the tests again without rebuilding the image:

```sh
docker compose run --rm php
```
