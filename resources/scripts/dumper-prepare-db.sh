#!/bin/bash

# This script receives two parameters:
# 1 - The database name to use.
# 2 - The folder where the dump is located.

usage() {
    echo "Usage:"
    echo "  ./resources/scripts/dumper-prepare-db digit-qa /tmp/dump"
    exit
}

echo "Database name: $1"
echo "Directory: $2"
if [ -z "$1" ]; then
    echo "The database argument(1) is missing."
    usage
fi
if [ -z "$2" ]; then
    echo "The directory argument(2) is missing."
    usage
fi

# Get the schema-create file.
SCHEMA_FILE=$(ls $2 | grep schema-create)
SCHEMA_FILE=$2/$SCHEMA_FILE
echo "Found schema file at: $SCHEMA_FILE"

# Get the database name used in the dump.
ORIGINAL_DATABASE=$(zcat $SCHEMA_FILE | sed -e 's/.*`\(.*\)`.*/\1/')
echo "The dump was generated using database: $ORIGINAL_DATABASE"

# Ignore if the dump was generated using the same database name.
if [ "$ORIGINAL_DATABASE" = "$1" ]; then
    echo "The dump was generated using the same database name, skipping."
    exit 0;
fi

# Replace defined schema in the schema-create file.
cp "$SCHEMA_FILE" "$SCHEMA_FILE~" &&
zcat "$SCHEMA_FILE~" | sed 's/\(.*`\)\(.*\)\(`.*\)/\1'"$1"'\3/' | gzip > "$SCHEMA_FILE"

# Rename the schema file, has a different name.
mv $SCHEMA_FILE "${SCHEMA_FILE/$ORIGINAL_DATABASE\-/$1\-}"

# Rename all files to match given database.
for file in $2/$ORIGINAL_DATABASE.*
do
    mv $file "${file/$ORIGINAL_DATABASE./$1.}"
done
