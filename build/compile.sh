#!/bin/bash

#!/usr/bin/env bash
set -euo pipefail

# Backward-compatible wrapper for Linux builds.
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
bash "$SCRIPT_DIR/compile-linux.sh"
