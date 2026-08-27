#!/bin/bash
# Batch scrape multi kategori bisnis Bandung → google_maps_leads DB
cd /c/laragon/www/google-maps-leads || exit 1

QUERIES=(
  "Cafe di Bandung"
  "Restoran di Bandung"
  "Barbershop di Bandung"
  "Apotek di Bandung"
  "Toko Bangunan di Bandung"
  "Salon di Bandung"
)

for q in "${QUERIES[@]}"; do
  echo "========== SCRAPE: $q =========="
  python -u scraper.py --query "$q" --limit 20 2>&1 | tail -5
  echo "========== DONE: $q =========="
  sleep 5
done
echo "ALL QUERIES DONE"
