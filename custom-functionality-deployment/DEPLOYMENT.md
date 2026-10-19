# Deployment Guide

## Prerequisites

- SSH access to all target sites.
- WP-CLI must be installed on all target sites.
- The sites must be listed in `config/sites.txt`.

## Deployment Process

1. Build the upload package from the repository root:

   ```bash
   ./scripts/build-package.sh
   ```

2. Upload `custom-functionality-deployment.zip` in WordPress under
   **Plugins → Add New → Upload Plugin**.
3. WordPress recognizes the existing plugin folder and performs an update.

The build script always creates exactly one root folder named
`custom-functionality-deployment` and rejects `.DS_Store`, AppleDouble and
`__MACOSX` entries. Do not create upload packages in Finder or with a generic
folder-compression command.

## Troubleshooting

If an upload does not complete, check the WordPress update screen and
`wp-content/debug.log`. A page that says "Das Plugin wurde erfolgreich
aktualisiert" confirms that the installation itself succeeded; then open the
installed-plugin list and check activation and version.
