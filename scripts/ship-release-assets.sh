#!/usr/bin/env bash
set -euo pipefail

root="$(cd "$(dirname "$0")/.." && pwd)"
cd "$root"

npm run build

if ! git diff --quiet -- public/build 2>/dev/null || [ -n "$(git ls-files --others --exclude-standard public/build)" ]; then
    echo ""
    echo "public/build/ changed. Include it in the same commit as your version bump:"
    echo "  git add public/build/"
    echo "  git commit -m \"Release vX.Y.Z: ...\""
    echo "  git push"
    echo ""
    echo "Production servers without npm should update via Admin → Settings → System & Version → GitHub update."
else
    echo "public/build/ unchanged after build."
fi
