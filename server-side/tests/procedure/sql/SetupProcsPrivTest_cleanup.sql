DELETE FROM auth_map WHERE auth_target = 'test_procdure' OR auth_target = 'test_function';
DELETE FROM mysql.procs_priv WHERE Db = DATABASE() AND (Routine_name = 'test_procdure' OR Routine_name = 'test_function');
FLUSH PRIVILEGES;
DROP PROCEDURE IF EXISTS  test_procdure;
DROP FUNCTION IF EXISTS test_function;
