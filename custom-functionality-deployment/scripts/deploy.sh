#!/bin/bash

# Konfiguration
PLUGIN_SLUG="custom-functionality"
PLUGIN_VERSION=$(git describe --tags)
PLUGIN_ZIP="${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip"
REMOTE_PATH="/var/www/html/wp-content/plugins"
SITES=$(cat ./config/sites.txt)

# Neues Plugin-ZIP erstellen
zip -r "$PLUGIN_ZIP" ./custom-functionality -x "*.git*" "*.DS_Store*" "*.zip"

# Deployment auf jede Site
for SITE in $SITES
do
  echo "Deploying to $SITE"
  ssh username@$SITE << EOF
    cd $REMOTE_PATH
    rm -rf $PLUGIN_SLUG
    unzip -o /path/to/deploy/$PLUGIN_ZIP -d .
    wp plugin install $PLUGIN_SLUG --activate
    wp cache flush
EOF
done

# Lokales Plugin-ZIP löschen
rm "$PLUGIN_ZIP"

echo "Deployment abgeschlossen."
