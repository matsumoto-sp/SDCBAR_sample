DROP TABLE IF EXISTS test_table_1;
DELETE FROM auth_map WHERE auth_target = 'test_table_1';
DELETE FROM mysql.tables_priv WHERE Db = DATABASE() AND Table_name = 'test_table_1';
FLUSH PRIVILEGES;
