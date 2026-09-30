#!/bin/bash
# Doble clic para ver el sitio compilado en http://localhost:4321
cd "$(dirname "$0")/dist" || exit 1
( sleep 1; open "http://localhost:4321" ) &
python3 -m http.server 4321
