# Custom Functionality Plugin Deployment

This project automates the deployment of the Custom Functionality Plugin to multiple WordPress sites.

## Project Structure

- `.github/workflows/deploy.yml`: GitHub Actions workflow for deployment.
- `scripts/deploy.sh`: Shell script for deploying to target sites.
- `config/sites.txt`: List of target sites for deployment.
- `custom-functionality/`: Optional folder for the plugin source code.
- `README.md`: This documentation.
- `DEPLOYMENT.md`: Detailed guide for the deployment process.

## Usage

1. **Setup**: Ensure all target sites are listed in `config/sites.txt`.
2. **Trigger Deployment**: Create a new tag in the repository to start the deployment.
