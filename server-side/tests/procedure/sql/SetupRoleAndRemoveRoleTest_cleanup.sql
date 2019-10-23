DROP USER IF EXISTS setup_role_1@'%';
DROP USER IF EXISTS setup_role_1@localhost;

DELETE FROM auth_map WHERE role = 'test_role';
DELETE FROM mysql.procs_priv WHERE Db = DATABASE() AND (Routine_name = 'test_procdure' OR Routine_name = 'test_function');
FLUSH PRIVILEGES;
DROP PROCEDURE IF EXISTS  test_procedure_1;
DROP FUNCTION IF EXISTS test_function_1;

DROP TABLE IF EXISTS test_table_1;
DROP TABLE IF EXISTS test_table_2;
