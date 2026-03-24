#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
BUILD_DIR="$PROJECT_ROOT/build"
SRC_DIR="$BUILD_DIR/swisseph_src"

ARCH="$(uname -m)"
case "$ARCH" in
  x86_64|amd64) ARCH="x64" ;;
  aarch64|arm64) ARCH="arm64" ;;
esac

OUT_DIR="$PROJECT_ROOT/libs/linux-$ARCH"
OUT_FILE="$OUT_DIR/libswe.so"

echo "======================================"
echo "Swiss Ephemeris Library Builder (Linux)"
echo "======================================"
echo ""

mkdir -p "$BUILD_DIR" "$OUT_DIR"
cd "$BUILD_DIR"

NEEDS_CLONE=1
if [ -d "$SRC_DIR" ]; then
  if git -C "$SRC_DIR" rev-parse --verify HEAD >/dev/null 2>&1; then
    NEEDS_CLONE=0
  else
    echo "Existing source checkout is invalid. Recreating..."
    rm -rf "$SRC_DIR"
  fi
fi

if [ "$NEEDS_CLONE" -eq 1 ]; then
  echo "Step 1: Downloading Swiss Ephemeris source..."
  echo "Source: https://github.com/aloistr/swisseph (latest commit)"
  git clone --depth 1 https://github.com/aloistr/swisseph.git swisseph_src
else
  echo "Step 1: Updating existing source..."
  rm -f "$SRC_DIR/.git/shallow.lock"
  git -C "$SRC_DIR" fetch --depth 1 origin master
  git -C "$SRC_DIR" reset --hard FETCH_HEAD
fi

cd "$SRC_DIR"

echo ""
echo "Current Swiss Ephemeris Version Info:"
git log -1 --format="  Commit: %h%n  Date:   %ad%n  Msg:    %s" --date=short
echo ""

if [ -d "src" ]; then
  C_SRC_DIR="$SRC_DIR/src"
elif [ -d "src_c" ]; then
  C_SRC_DIR="$SRC_DIR/src_c"
else
  C_SRC_DIR="$SRC_DIR"
fi

echo "Step 2: Compiling Swiss Ephemeris library..."
echo "Source directory: $C_SRC_DIR"

gcc --version

C_FILES_LIST="swedate.c swehouse.c swejpl.c swemmoon.c swemplan.c sweph.c swephlib.c swecl.c swehel.c"
O_FILES=""
for c_file in $C_FILES_LIST; do
  file="$C_SRC_DIR/$c_file"
  if [ -f "$file" ]; then
    basename="$c_file"
    oname="${basename%.c}.o"
    echo "Compiling $basename..."
    gcc -c -fPIC -O3 -o "$oname" "$file"
    O_FILES="$O_FILES $oname"
  else
    echo "Warning: File $file not found, skipping."
  fi
done

echo "Creating shared library..."
gcc -shared -o libswe.so $O_FILES -lm

cp libswe.so "$BUILD_DIR/"
cp libswe.so "$OUT_FILE"
chmod 755 "$BUILD_DIR/libswe.so" "$OUT_FILE"
rm -f *.o

echo ""
echo "======================================"
echo "Compilation successful!"
echo "======================================"
echo ""
echo "Library created:"
echo "  $BUILD_DIR/libswe.so"
echo "  $OUT_FILE"
echo ""
echo "Done!"
