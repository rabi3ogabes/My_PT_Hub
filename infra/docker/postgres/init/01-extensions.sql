-- Runs once when the local Postgres volume is first created.
CREATE EXTENSION IF NOT EXISTS pg_stat_statements;
CREATE EXTENSION IF NOT EXISTS pg_trgm;      -- trigram search fallback for foods/exercises
CREATE EXTENSION IF NOT EXISTS unaccent;     -- accent-insensitive search (fr/es)
CREATE EXTENSION IF NOT EXISTS citext;

-- Separate database for the Pest suite so `make test` never touches dev data.
SELECT 'CREATE DATABASE aurum_testing OWNER aurum'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'aurum_testing')\gexec
