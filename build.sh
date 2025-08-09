#!/bin/bash

# Build script for Jolix SEO plugin
# Creates a clean zip file for distribution

PLUGIN_NAME="jolix-seo"
VERSION=$(grep "Version:" jolix-seo.php | sed 's/.*Version: //' | sed 's/ .*//')
BUILD_DIR="build"
FILENAME="${PLUGIN_NAME}-${VERSION}.zip"

echo "Building ${PLUGIN_NAME} v${VERSION}..."

# Create build directory
rm -rf $BUILD_DIR
mkdir -p $BUILD_DIR/$PLUGIN_NAME

# Copy plugin files (excluding development files)
rsync -av \
  --exclude='.git*' \
  --exclude='node_modules' \
  --exclude='package*.json' \
  --exclude='build.sh' \
  --exclude='build/' \
  --exclude='CLAUDE.md' \
  --exclude='.vscode/' \
  --exclude='.idea/' \
  --exclude='.DS_Store' \
  --exclude='*.zip' \
  . $BUILD_DIR/$PLUGIN_NAME/

# Create zip file
cd $BUILD_DIR
zip -r ../$FILENAME $PLUGIN_NAME/
cd ..

# Cleanup
rm -rf $BUILD_DIR

echo "Created: $FILENAME"
echo "Size: $(du -h $FILENAME | cut -f1)"