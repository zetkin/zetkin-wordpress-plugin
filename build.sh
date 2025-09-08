#!/bin/bash

set -eox pipefail

composer install --no-dev
npm i
npm run build
npx wp-scripts plugin-zip
