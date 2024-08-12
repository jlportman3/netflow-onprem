#!/bin/ash

FILE_PATH="$1"

curl php:9000/api/process_data \
  -H "Accept: application/json" \
  -H "Content-type: application/json" \
  -d "{ \"file\": \"${FILE_PATH}\" }"
