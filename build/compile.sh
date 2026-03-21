#!/bin/bash

# Swiss Ephemeris Library Compilation Script
# This script downloads and compiles the Swiss Ephemeris C source into a shared library

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
BUILD_DIR="$PROJECT_ROOT/build"
SRC_DIR="$BUILD_DIR/swisseph_src"
EPHEM_PATH="https://www.astro.com/swisseph/sweph_2.10-2.tar.gz"

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
    
    # Download from Astrodienst
    if command -v wget &> /dev/null; then
        wget -q --show-progress "$EPHEM_PATH" -O sweph.tar.gz
    elif command -v curl &> /dev/null; then
        curl -L "$EPHEM_PATH" -o sweph.tar.gz --progress-bar
    else
        echo "Error: Neither wget nor curl found. Please install one of them."
        exit 1
    fi
    
    # Extract
    echo "Extracting source..."
    tar -xzf sweph.tar.gz
    mv sweph_* swisseph_src 2>/dev/null || mv sweph* swisseph_src 2>/dev/null || true
    rm sweph.tar.gz
    
    if [ ! -d "$SRC_DIR" ]; then
        echo "Error: Could not find extracted source directory"
        exit 1
    fi
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

# Source files
SWEMOON="$C_SRC_DIR/swemoon.c"
SWEPLAN="$C_SRC_DIR/sweplan.c"
SWEPHEMD="$C_SRC_DIR/swephemd.c"
SWETREPID="$C_SRC_DIR/swetrepid.c"
SWENUT2000A="$C_SRC_DIR/swenut2000a.c"
SWENUT2000B="$C_SRC_DIR/swenut2000b.c"
SWENUTALL="$C_SRC_DIR/swenutall.c"
SWEJPL="$C_SRC_DIR/swejpl.c"
SWEJPLEPH="$C_SRC_DIR/swejpleph.c"
SWEDATE="$C_SRC_DIR/swedate.c"
SWETID="$C_SRC_DIR/swetid.c"
SWEHOUSE="$C_SRC_DIR/swehouse.c"
SWELUNAR="$C_SRC_DIR/swelunar.c"
SWECOAR="$C_SRC_DIR/swecoar.c"

# Check if source files exist
if [ ! -f "$SWEMOON" ]; then
    echo "Looking for C source files..."
    find "$SRC_DIR" -name "*.c" -type f | head -10
    
    # Try to find the actual source directory
    FOUND_SRC=$(find "$SRC_DIR" -name "swemoon.c" -type f 2>/dev/null | head -1)
    if [ -n "$FOUND_SRC" ]; then
        C_SRC_DIR="$(dirname "$FOUND_SRC")"
        echo "Found source directory: $C_SRC_DIR"
        SWEMOON="$C_SRC_DIR/swemoon.c"
    else
        echo "Error: Could not find swemoon.c in $SRC_DIR"
        echo "Please download Swiss Ephemeris source manually from:"
        echo "https://www.astro.com/swisseph/swedownload_e.htm"
        exit 1
    fi
fi

# Compile with gcc
echo "Compiling with GCC..."
gcc --version

# Compile to object files
gcc -c -fPIC -O2 -o swemoon.o "$SWEMOON"
gcc -c -fPIC -O2 -o sweplan.o "$SWEPLAN"
gcc -c -fPIC -O2 -o swephemd.o "$SWEPHEMD"
gcc -c -fPIC -O2 -o swetrepid.o "$SWETREPID"
gcc -c -fPIC -O2 -o swenut2000a.o "$SWENUT2000A"
gcc -c -fPIC -O2 -o swenut2000b.o "$SWENUT2000B"
gcc -c -fPIC -O2 -o swenutall.o "$SWENUTALL"
gcc -c -fPIC -O2 -o swejpl.o "$SWEJPL"
gcc -c -fPIC -O2 -o swejpleph.o "$SWEJPLEPH"
gcc -c -fPIC -O2 -o swedate.o "$SWEDATE"
gcc -c -fPIC -O2 -o swetid.o "$SWETID"
gcc -c -fPIC -O2 -o swehouse.o "$SWEHOUSE"
gcc -c -fPIC -O2 -o swelunar.o "$SWELUNAR"
gcc -c -fPIC -O2 -o swecoar.o "$SWECOAR"

# Create shared library
echo "Creating shared library..."
gcc -shared -o libswe.so \
    swemoon.o sweplan.o swephemd.o swetrepid.o \
    swenut2000a.o swenut2000b.o swenutall.o \
    swejpl.o swejpleph.o swedate.o swetid.o \
    swehouse.o swelunar.o swecoar.o \
    -lm

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
echo "Next steps:"
echo "1. Copy ephemeris files (se1*.eph) to: $BUILD_DIR/ephe/"
echo "2. Set permissions: chmod 755 $BUILD_DIR/libswe.so"
echo "3. Configure your PHP application to use the library"
echo ""
echo "To download ephemeris files:"
echo "  wget -P $BUILD_DIR/ephe/ https://www.astro.com/swisseph/ae/2000-2099/se1_2000-2099.zip"
echo "  unzip -d $BUILD_DIR/ephe/ $BUILD_DIR/ephe/se1_2000-2099.zip"
echo ""

chmod 755 "$BUILD_DIR/libswe.so"

echo "Done!"
