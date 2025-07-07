#!/bin/bash

set -eox pipefail

composer install
npm i
npm run build
npx wp-scripts plugin-zip
