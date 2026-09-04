-- TASK 1 — schema and seed data.
--
-- This runs once, when MySQL initializes an empty data directory.
--
-- (a) Create one table named `survey` that can hold a completed form:
--
-- Column            Type              Notes
-- ----------------  ----------------  ---------------------------------------
-- id                INT               primary key, auto increment
-- first_name        VARCHAR(100)      required
-- last_name         VARCHAR(100)      required
-- email             VARCHAR(255)      required
-- lamp_comfort      TINYINT           required, 1-5
-- html_css          TINYINT           0-3, default 0
-- javascript        TINYINT           0-3, default 0
-- php               TINYINT           0-3, default 0
-- sql_mysql         TINYINT           0-3, default 0
-- git_github        TINYINT           0-3, default 0
-- docker            TINYINT           0-3, default 0
-- node_express      TINYINT           0-3, default 0
-- react_frontend    TINYINT           0-3, default 0
-- rest_apis         TINYINT           0-3, default 0
-- cloud             TINYINT           0-3, default 0
-- agentic_ai        TINYINT           0-3, default 0
-- ai_assistants     TINYINT           0-3, default 0
-- goals             TEXT              required
-- created_at        TIMESTAMP         defaults to the time of insert
--
-- (b) SEED it: insert at least three sample rows, so the confirmation page has
--     something to average before any real person submits. Make them obviously
--     fake -- this table will hold classmates' real answers.

USE app;

-- YOUR CREATE TABLE HERE

-- YOUR INSERTs HERE
