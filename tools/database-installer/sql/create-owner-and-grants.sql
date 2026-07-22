-- The installer must set @dhdc4_owner_password in memory before sourcing this file.
-- No password is stored in this file or in the generated package.
SET @dhdc4_create_user_sql = CONCAT(
  'CREATE USER IF NOT EXISTS ''dhdc4''@''localhost'' IDENTIFIED BY ',
  QUOTE(@dhdc4_owner_password)
);
PREPARE dhdc4_create_user_stmt FROM @dhdc4_create_user_sql;
EXECUTE dhdc4_create_user_stmt;
DEALLOCATE PREPARE dhdc4_create_user_stmt;

SET @dhdc4_alter_user_sql = CONCAT(
  'ALTER USER ''dhdc4''@''localhost'' IDENTIFIED BY ',
  QUOTE(@dhdc4_owner_password)
);
PREPARE dhdc4_alter_user_stmt FROM @dhdc4_alter_user_sql;
EXECUTE dhdc4_alter_user_stmt;
DEALLOCATE PREPARE dhdc4_alter_user_stmt;
SET @dhdc4_owner_password = NULL;
SET @dhdc4_create_user_sql = NULL;
SET @dhdc4_alter_user_sql = NULL;

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, REFERENCES, INDEX, ALTER,
  CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE, CREATE VIEW, SHOW VIEW,
  CREATE ROUTINE, ALTER ROUTINE, EVENT, TRIGGER, SHOW CREATE ROUTINE
  ON `dhdc4`.* TO 'dhdc4'@'localhost';
