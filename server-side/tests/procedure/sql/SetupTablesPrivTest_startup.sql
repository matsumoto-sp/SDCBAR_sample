INSERT INTO auth_map (role, auth_target, auth_type) VALUES
  ('test_role_select', 'test_table_1', 'SELECT'),
  ('test_role_insert', 'test_table_1', 'INSERT'),
  ('test_role_update', 'test_table_1', 'SELECT,UPDATE'),
  ('test_role_delete', 'test_table_1', 'SELECT,DELETE');

CREATE TABLE test_table_1 (
  id INT PRIMARY KEY
);

INSERT INTO test_table_1 (id) VALUES (1), (2), (3), (4), (5);
