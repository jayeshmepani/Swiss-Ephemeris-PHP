#!/bin/bash

# Swiss Ephemeris Library Compilation Script
# This script downloads and compiles the Swiss Ephemeris C source into a shared library

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
BUILD_DIR="$PROJECT_ROOT/build"
SRC_DIR="$BUILD_DIR/swisseph_src"

echo "======================================"
echo "Swiss Ephemeris Library Builder"
echo "======================================"
echo ""

# Create build directory
mkdir -p "$BUILD_DIR"
cd "$BUILD_DIR"

# Check if source already exists
if [ ! -d "$SRC_DIR" ]; then
    echo "Step 1: Downloading Swiss Ephemeris source..."
    git clone https://github.com/aloistr/swisseph.git swisseph_src
else
    echo "Step 1: Source already exists, skipping download..."
fi

cd "$SRC_DIR"

# Find the C source directory
if [ -d "src" ]; then
    C_SRC_DIR="$SRC_DIR/src"
elif [ -d "src_c" ]; then
    C_SRC_DIR="$SRC_DIR/src_c"
else
    # Source files might be in root
    C_SRC_DIR="$SRC_DIR"
fi

echo "Step 2: Compiling Swiss Ephemeris library..."
echo "Source directory: $C_SRC_DIR"

# Compile with gcc
echo "Compiling with GCC..."
gcc --version

# The upstream Makefile for libswe defines these sources:
# SWEOBJ = swedate.o swehouse.o swejpl.o swemmoon.o swemplan.o sweph.o \
#          swephlib.o swecl.o swehel.o
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

# Create shared library
echo "Creating shared library..."
gcc -shared -o libswe.so $O_FILES -lm

# Copy to project build directory
cp libswe.so "$BUILD_DIR/"

# Clean up object files
rm -f *.o

echo ""
echo "======================================"
echo "Compilation successful!"
echo "======================================"
echo ""
echo "Library created: $BUILD_DIR/libswe.so"
echo ""

chmod 755 "$BUILD_DIR/libswe.so"

echo "Done!"
