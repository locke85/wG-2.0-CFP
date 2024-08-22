# Deployment Guide

## Prerequisites

- SSH access to all target sites.
- WP-CLI must be installed on all target sites.
- The sites must be listed in `config/sites.txt`.

## Deployment Process

1. **Tag Creation**: A deployment is triggered by creating a new tag in the repository.
2. **GitHub Actions**: The GitHub Actions workflow file (`.github/workflows/deploy.yml`) handles the rest.
3. **SSH Setup**: The `setup_ssh.sh` script sets up SSH access.
4. **Deployment Script**: `deploy.sh` is executed to deploy the plugin to all sites.

## Troubleshooting

If issues arise, check the logs in GitHub Actions and ensure the SSH configuration is correct.
