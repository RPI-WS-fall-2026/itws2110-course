FROM mysql:8.4

# Anything in this directory runs the first time the server initializes an
# empty data directory -- and only then. Files run in alphabetical order.
COPY init.sql /docker-entrypoint-initdb.d/
