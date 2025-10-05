#!/bin/bash

# Load environment variables from .env
if [ ! -f .env ]; then
    echo "Error: .env file not found"
    exit 1
fi

# Source the .env file
export $(grep -v '^#' .env | xargs)

# Check required variables
if [ -z "$DEPLOY_USER" ] || [ -z "$DEPLOY_HOST" ] || [ -z "$DEPLOY_PATH" ]; then
    echo "Error: Missing required deploy variables in .env"
    echo "Required: DEPLOY_USER, DEPLOY_HOST, DEPLOY_PATH"
    exit 1
fi

echo "Building production assets..."
NODE_OPTIONS=--openssl-legacy-provider npm run production

if [ $? -ne 0 ]; then
    echo "Error: Build failed"
    exit 1
fi

echo "Deploying to $DEPLOY_USER@$DEPLOY_HOST:$DEPLOY_PATH..."

# Rsync public directory
rsync -avz --delete \
    --exclude='.htpasswd' \
    --exclude='.htpasswdnew' \
    --exclude='.dh-diag' \
    public/ $DEPLOY_USER@$DEPLOY_HOST:$DEPLOY_PATH/public/

if [ $? -ne 0 ]; then
    echo "Error: Deploy failed"
    exit 1
fi

echo "Deploy completed successfully!"
