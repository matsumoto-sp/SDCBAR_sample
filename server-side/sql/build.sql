SET @admin_default_password = 'ahThip9ivoh0';
SET @admin_email = 'admin@example.com';

CREATE TABLE users( 
  user_id INT PRIMARY KEY AUTO_INCREMENT
  , login_name VARCHAR (32) NOT NULL
  , profile VARCHAR (200)
  , email VARCHAR (40) NOT NULL
  , profile_open ENUM('none', 'friends', 'members', 'open') NOT NULL DEFAULT 'none'
  , is_admin BOOLEAN NOT NULL DEFAULT FALSE,
  INDEX(login_name)
);

CREATE TABLE users_friend_users( 
  user_id INT NOT NULL
  , friend_user_id INT NOT NULL
  , PRIMARY KEY (user_id, friend_user_id)
  , FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
  , FOREIGN KEY (friend_user_id) REFERENCES users(user_id) ON DELETE CASCADE
  , INDEX (user_id)
  , INDEX (friend_user_id)
);


CREATE TABLE lock_ctl(lock_id VARCHAR (12) PRIMARY KEY);

CREATE TABLE auth_map(
  auth_map_id INT PRIMARY KEY AUTO_INCREMENT
  , role VARCHAR(20) NOT NULL
  , auth_target VARCHAR(40) NOT NULL
  , auth_type VARCHAR(20) NOT NULL
);

INSERT INTO auth_map (role, auth_target, auth_type) VALUES
  ('admin', 'users', 'SELECT'),
  ('admin', 'users_view', 'SELECT'),
  ('admin', 'users_friend_users', 'SELECT'),
  ('admin', 'add_user', 'PROCEDURE'),
  ('admin', 'update_user', 'PROCEDURE'),
  ('admin', 'update_me', 'PROCEDURE'),
  ('admin', 'remove_user', 'PROCEDURE'),
  ('admin', 'add_admin', 'PROCEDURE'),
  ('admin', 'remove_admin', 'PROCEDURE'),
  ('admin', 'add_friend', 'PROCEDURE'),
  ('admin', 'remove_friend', 'PROCEDURE'),
  ('admin', 'my_info',  'PROCEDURE'),
  ('admin', 'user_id', 'FUNCTION'),
  ('admin', 'my_login_name', 'FUNCTION'),
  ('admin', 'my_role', 'FUNCTION'),

  ('member', 'users_view', 'SELECT'),
  ('member', 'users_friend_users', 'SELECT'),
  ('member', 'update_me', 'PROCEDURE'),
  ('member', 'remove_me', 'PROCEDURE'),
  ('member', 'add_friend', 'PROCEDURE'),
  ('member', 'remove_friend', 'PROCEDURE'),
  ('member', 'my_info', 'PROCEDURE'),
  ('member', 'user_id', 'FUNCTION'),
  ('member', 'my_login_name', 'FUNCTION'),
  ('member', 'my_role', 'FUNCTION'),
 
  ('anonymous', 'users_view', 'SELECT'),
  ('anonymous', 'add_user', 'PROCEDURE'),
  ('anonymous', 'user_id', 'FUNCTION'),
  ('anonymous', 'my_login_name', 'FUNCTION'),
  ('anonymous', 'my_role', 'FUNCTION');

DELIMITER //
CREATE FUNCTION my_login_name() RETURNS VARCHAR(260) NOT DETERMINISTIC
BEGIN
    DECLARE _full_login_name VARCHAR(260);
    DECLARE _host_len VARCHAR(260);
    SET _full_login_name = USER();
    SET _host_len = CHAR_LENGTH(SUBSTRING_INDEX(_full_login_name, '@', -1));
    RETURN SUBSTRING(_full_login_name, 1, CHAR_LENGTH(_full_login_name) - _host_len - 1);
END;
//
DELIMITER ;

DELIMITER //
CREATE FUNCTION my_role() RETURNS ENUM('anonymous', 'member', 'admin') NOT DETERMINISTIC
BEGIN
    DECLARE _login_name VARCHAR(40);
    DECLARE _is_admin BOOLEAN;
    SET _login_name = my_login_name();
    IF _login_name = 'anonymous' THEN
        RETURN 'anonymous';
    ELSE
        SET _is_admin = (SELECT is_admin FROM users WHERE login_name = _login_name);
        IF _is_admin IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User not exists.', MYSQL_ERRNO = 1001;
        ELSE
            IF _is_admin <> 0 THEN
                RETURN 'admin';
            ELSE
                RETURN 'member';
            END IF;
        END IF;
    END IF;
END;
//
DELIMITER ;

DELIMITER //
CREATE FUNCTION safe_profile(
    _my_login_name VARCHAR(12),
    _target_login_name VARCHAR(12),
    _profile VARCHAR(200), 
    _profile_open ENUM('none', 'friends', 'members', 'open'),
    _is_friend INT
) RETURNS VARCHAR(200) DETERMINISTIC
BEGIN
    IF _profile_open <> 'none' 
       AND _profile_open <> 'friends'
       AND _profile_open <> 'members'
       AND _profile_open <> 'open'
    THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'The value of argument _profile_open is invalid.', MYSQL_ERRNO = 1001;
    END IF;
    RETURN
    	CASE WHEN 
               (_my_login_name = _target_login_name)
            OR (_profile_open = 'friends' AND _is_friend IS NOT NULL)
            OR (_profile_open = 'members' AND _my_login_name <> 'anonymous')
            OR (_profile_open = 'open')
        THEN
            _profile
        ELSE
            NULL
        END;
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE setup_tables_priv(_role VARCHAR(20), _host VARCHAR(20), _login_name VARCHAR(12))
BEGIN
    INSERT 
    INTO mysql.tables_priv(Host, Db, User, Table_priv, Table_name) 
    SELECT
      _host
      , DATABASE()
      , _login_name
      , auth_type
      , auth_target 
    FROM
      auth_map 
    WHERE
      role = _role 
      AND auth_type NOT IN ('FUNCTION', 'PROCEDURE');
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE my_info()
BEGIN
    SELECT
      user_id
      , login_name
      , profile
      , email
      , profile_open
      , is_admin 
      , my_role() AS role
    FROM
      users 
    WHERE
      login_name = my_login_name();
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE setup_procs_priv(_role VARCHAR(20), _host VARCHAR(20), _login_name VARCHAR(12))
BEGIN
    INSERT 
    INTO mysql.procs_priv( 
      Host
      , Db
      , User
      , Routine_name
      , Routine_type
      , Grantor
      , Proc_priv
    ) 
    SELECT
      _host
      , DATABASE()
      , _login_name
      , auth_target
      , auth_type
      , USER ()
      , 'Execute' 
    FROM
      auth_map 
    WHERE
      role = _role 
      AND auth_type IN ('FUNCTION', 'PROCEDURE');
END;
//
DELIMITER ;


DELIMITER //
CREATE PROCEDURE setup_role(_role VARCHAR(20), _login_name VARCHAR(12))
BEGIN
    CALL setup_tables_priv(_role, '%', _login_name);
    CALL setup_tables_priv(_role, 'localhost', _login_name);
    CALL setup_procs_priv(_role, '%', _login_name);
    CALL setup_procs_priv(_role, 'localhost', _login_name);
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE remove_role(_login_name VARCHAR(12))
BEGIN
    DELETE FROM mysql.tables_priv WHERE User=_login_name AND (Host='%' OR Host='localhost') AND Db=DATABASE();
    DELETE FROM mysql.procs_priv WHERE User=_login_name AND (Host='%' OR Host='localhost') AND Db=DATABASE();
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE add_user_core(_login_name VARCHAR(12), _password VARCHAR(20), _profile VARCHAR(200), _email VARCHAR(40))
BEGIN
    INSERT INTO users (login_name, profile, email, profile_open, is_admin)
        VALUES(_login_name, _profile, _email, 'none', FALSE);
    INSERT INTO mysql.user (User, Host, authentication_string, ssl_cipher, x509_issuer, x509_subject) VALUES 
        (_login_name, '%', password(_password), '', '', ''),
        (_login_name, 'localhost', password(_password), '', '', '');
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE add_user(_login_name VARCHAR(12), _password VARCHAR(20), _profile VARCHAR(200), _email VARCHAR(40))
BEGIN
    DECLARE _lock INT;
    DECLARE _exists VARCHAR(40);
    SET _lock = (SELECT 1 FROM lock_ctl WHERE lock_id = 'create-user' FOR UPDATE);
    SET _exists = (SELECT user FROM mysql.user WHERE user=_login_name AND host='%');
    IF _exists IS NOT NULL OR _login_name = 'anonymous' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'User is already exists.', MYSQL_ERRNO = 1001;
    ELSE
        CALL add_user_core(_login_name, _password, _profile, _email);
        CALL setup_role('member', _login_name);
        FLUSH PRIVILEGES;
    END IF;
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE remove_user(_login_name VARCHAR(12))
BEGIN
    DECLARE _lock INT;
    DECLARE _exists VARCHAR(40);
    SET _lock = (SELECT 1 FROM lock_ctl WHERE lock_id = 'create-user' FOR UPDATE);
    SET _exists = (SELECT user FROM mysql.user WHERE user=_login_name AND host='%');
    IF _exists IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'User not exists.', MYSQL_ERRNO = 1001;
    ELSE
        DELETE FROM users WHERE login_name=_login_name;
        DELETE FROM mysql.user WHERE User=_login_name AND (Host='%' OR Host='localhost');
        CALL remove_role(_login_name);
        FLUSH PRIVILEGES;
    END IF;
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE update_user_core(
    _login_name VARCHAR(12), 
    _profile VARCHAR(200), 
    _email VARCHAR(40),
    _profile_open ENUM('none', 'friends', 'members', 'open')
)
BEGIN
    IF _profile IS NOT NULL THEN
        UPDATE users SET profile = _profile WHERE login_name = _login_name;
    END IF;
    IF _email IS NOT NULL AND _email <> '' THEN
        UPDATE users SET email = _email WHERE login_name = _login_name;
    END IF;
    IF _profile_open IS NOT NULL THEN
        UPDATE users SET profile_open = _profile_open WHERE login_name = _login_name;
    END IF;
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE update_password(_login_name VARCHAR(12), _password VARCHAR(20))
BEGIN
    IF _password IS NOT NULL THEN
        UPDATE mysql.user 
        Set
          authentication_string = password(_password) 
        WHERE
          User = _login_name 
          AND Host IN ('%', 'localhost');
        FLUSH PRIVILEGES;
    END IF;
END;
//
DELIMITER ;


DELIMITER //
CREATE PROCEDURE update_user(
    _login_name VARCHAR(12),
    _password VARCHAR(20),
    _profile VARCHAR(200),
    _email VARCHAR(40),
    _profile_open ENUM('none', 'friends', 'members', 'open')
)
BEGIN
    DECLARE _lock INT;
    DECLARE _exists VARCHAR(40);
    SET _lock = (SELECT 1 FROM lock_ctl WHERE lock_id = 'create-user' FOR UPDATE);
    SET _exists = (SELECT user FROM mysql.user WHERE user=_login_name AND host='%');
    IF _exists IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'User not exists.', MYSQL_ERRNO = 1001;
    ELSE
        CALL update_user_core(_login_name, _profile, _email, _profile_open);
        CALL update_password(_login_name, _password);
    END IF;
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE update_me(
    _password VARCHAR(20),
    _profile VARCHAR(200),
    _email VARCHAR(40),
    _profile_open ENUM('none', 'friends', 'members', 'open')
)
BEGIN
    CALL update_user(my_login_name(), _password, _profile, _email, _profile_open);
END;
//
DELIMITER ;


DELIMITER //
CREATE PROCEDURE remove_me()
BEGIN
    CALL remove_user(my_login_name());
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE add_admin(_login_name VARCHAR(12))
BEGIN
    UPDATE users SET is_admin = TRUE WHERE login_name = _login_name;
    CALL remove_role(_login_name);
    CALL setup_role('admin', _login_name);
    FLUSH PRIVILEGES;
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE remove_admin(_login_name VARCHAR(12))
BEGIN
    UPDATE users SET is_admin = FALSE WHERE login_name = _login_name;
    CALL remove_role(_login_name);
    CALL setup_role('member', _login_name);
    FLUSH PRIVILEGES;
END;
//
DELIMITER ;


DELIMITER //
CREATE FUNCTION user_id(_login_name VARCHAR(12)) RETURNS INT NOT DETERMINISTIC
BEGIN
    DECLARE _user_id INT;
    SET _user_id = (SELECT user_id FROM users WHERE login_name = _login_name);
    IF _user_id IS NULL AND _login_name <> 'anonymous' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'User not exists.', MYSQL_ERRNO = 1001;
    END IF;
    RETURN _user_id;
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE add_friend(_friend_login_name VARCHAR(12))
BEGIN
    DECLARE _user_id INT;
    DECLARE _friend_user_id INT;
    DECLARE _exists INT;
    SET _user_id = (SELECT user_id(my_login_name()));
    SET _friend_user_id = (SELECT user_id(_friend_login_name));
    IF _user_id IS NOT NULL AND _friend_user_id IS NOT NULL THEN
        SET _exists = (SELECT friend_user_id FROM users_friend_users WHERE user_id = _user_id AND friend_user_id = _friend_user_id);
        IF _exists IS NULL THEN
            INSERT INTO users_friend_users (user_id, friend_user_id) VALUES (_user_id, _friend_user_id);
        END IF;
    END IF;
END;
//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE remove_friend(_friend_login_name VARCHAR(12))
BEGIN
    DECLARE _user_id INT;
    DECLARE _friend_user_id INT;
    SET _user_id = (SELECT user_id(my_login_name()));
    SET _friend_user_id = (SELECT user_id(_friend_login_name));
    IF _user_id IS NOT NULL AND _friend_user_id IS NOT NULL THEN
        DELETE 
        FROM
          users_friend_users
        WHERE
             ( user_id = _user_id AND friend_user_id = _friend_user_id );
    END IF;
END;
//
DELIMITER ;

CREATE VIEW users_view AS 
SELECT
  u.user_id
  , u.login_name
  , safe_profile( 
    my_login_name()
    , u.login_name
    , u.profile
    , u.profile_open
    , f.user_id
  ) AS profile 
FROM
  users u 
  LEFT JOIN users_friend_users f 
    ON f.user_id = u.user_id 
    AND f.friend_user_id = user_id(my_login_name())
ORDER BY
  u.user_id DESC;

INSERT INTO lock_ctl VALUES('create-user');

CALL add_user('admin', @admin_default_password, 'admin account', @admin_email);
CALL add_admin('admin');

CREATE USER anonymous@'localhost';
CREATE USER anonymous@'%';
CALL setup_role('anonymous', 'anonymous');
FLUSH PRIVILEGES;
