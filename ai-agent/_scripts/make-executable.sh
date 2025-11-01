#!/bin/bash
# Make all migration scripts executable

cd "$(dirname "$0")"

chmod +x bin/run-migration.sh
chmod +x bin/quick-migrate.sh
chmod +x bin/verify-schema.php

echo "✓ All scripts are now executable"
echo ""
echo "Available commands:"
echo "  php bin/verify-schema.php      - Check current schema (read-only)"
echo "  bash bin/quick-migrate.sh      - Execute migration (recommended)"
echo "  bash bin/run-migration.sh      - Interactive migration with prompts"
