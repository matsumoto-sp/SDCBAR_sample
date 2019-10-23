INSERT INTO auth_map (role, auth_target, auth_type) VALUES
  ('test_role_procedure', 'test_procdure', 'PROCEDURE'),
  ('test_role_function', 'test_function', 'FUNCTION');

DELIMITER //
CREATE PROCEDURE test_procdure()
BEGIN
END;
//
DELIMITER ;

DELIMITER //
CREATE FUNCTION test_function() RETURNS INT DETERMINISTIC
BEGIN
    RETURN 1;
END;
//
DELIMITER ;

