-- Migration: add delete_at column to MOVIE table
USE TICKETIX;

-- Add a nullable date column to store movie delete/archival date
ALTER TABLE MOVIE
ADD COLUMN delete_at DATE DEFAULT NULL;
