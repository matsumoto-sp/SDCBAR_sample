CREATE USER setup_role_1@localhost;
CREATE USER setup_role_1@'%';

INSERT INTO auth_map (role, auth_target, auth_type) VALUES
  ('test_role', 'test_table_1', 'SELECT'),
  ('test_role', 'test_procedure_1', 'PROCEDURE'),
  ('test_role', 'test_function_1', 'FUNCTION');

CREATE TABLE test_table_1 (
  id INT PRIMARY KEY
);

CREATE TABLE test_table_2 (
  id INT PRIMARY KEY
);

INSERT INTO test_table_1 (id) VALUES (1), (2), (3), (4), (5);

DELIMITER //
CREATE PROCEDURE test_procedure_1()
BEGIN
END;
//
DELIMITER ;

DELIMITER //
CREATE FUNCTION test_function_1() RETURNS INT DETERMINISTIC
BEGIN
    RETURN 1;
END;
//
DELIMITER ;

GRANT SELECT ON test_table_2 TO setup_role_1@'%';
