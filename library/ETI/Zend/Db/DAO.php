<?php
/**
 * The DAO class is an implementation of the Active Record Pattern.
 * Each instance of a DAO represents a particular row in a particular
 * table. However, any one subclass can be instantiated with different
 * types of Zend_db_Table instances. In other words, one subclass can
 * be used to execute its methods against different tables. The DAO
 * figures out which of its properties can be mapped to the columns of
 * the table that gets assigned to it.
 * 
 * @author Ayco Holleman 
 *
 */
abstract class ETI_Zend_Db_DAO
{
    
    /**
     * @var Zend_Log
     */
    protected static $_logger;
    
    /**
     * An array containing metadata shared by all instances of the same subclass.
     * Actually, because one particular subclass can be instantiated with
     * different types of Zend_db_Table instances, metadata is assembled once
     * for each combination of subtypes of ETI_Zend_Db_DAO and Zend_Db_Table_Astract.
     * 
     * @var array
     */
    private static $_meta = array();
    
    /**
     * @var Zend_Db_Table_Abstract
     */
    protected $_table;
    
    /**
     * The metadata for this particular instance - held in a private variable
     * so that we don't need to look it up every time in the static $_meta array.
     * 
     * @var array
     */
    private $_myMeta;
    
    private $_myProperties = null;

    /**
     * 
     * @param Zend_Db_Table_Abstract $table
     */
    public function __construct (Zend_Db_Table_Abstract $table = null)
    {
        if ($table === null) {
            $tableClass = $this->_getTableClass();
            if ($tableClass === null) {
                throw new ETI_Zend_Db_DAOException('You must instantiate a DAO with a Zend_Db_Table_Abstract instance or implement the _getTableClass method');
            }
            if (is_string($tableClass)) {
                $tableClass = new ReflectionClass($tableClass);
            }
            $table = $tableClass->newInstance();
        }
        $this->_table = $table;
        $metaKey = md5(get_class($this) . '@' . get_class($table));
        if (array_key_exists($metaKey, self::$_meta)) {
            $this->_myMeta = self::$_meta[$metaKey];
        }
        else {
            $this->_setupMetadata($metaKey);
        }
    }

    /**
     * Check if a combination of property values in this object exists in one or more rows.
     * 
     * You can pass an arbitrary number of arguments; each argument
     * is assumed to be the <b>name</b> of a property of this object. The
     * combination of values for these properties is looked up in the
     * table. If a record with the same combination of values is
     * found, its primary key is returned, otherwise false.
     * 
     */
    public function exists ()
    {
        if (func_num_args() === 0) {
            $rows = $this->_simpleSelect($this->_myMeta['PK'], $this->_myMeta['PK']);
        }
        else {
            $rows = $this->_simpleSelect(func_get_args(), $this->_myMeta['PK']);
        }
        if (count($rows) > 0) {
            return array_values($rows[0]);
        }
        return false;
    }

    /**
     * Populate this object using the record identified by $id.
     * 
     * If you don't pass an $id, the current value of the primary key property
     * is used to retrieve the record. If you only want to retrieve a few properties,
     * you can pass them as additional arguments to this method. You can also pass
     * them in a single array. N.B. you should specify property names, NOT column
     * names.
     * 
     * @param mixed $id The primary key of the record used to populate this object.
     * @return bool true if a record with the specified id esists, false otherwise
     */
    public function load ($id = null)
    {
        if ($id !== null) {
            $this->setPrimaryKey($id);
        }
        $select = null;
        if (func_num_args() > 1) {
            $select = is_array(func_get_arg(1)) ? func_get_arg(1) : array_slice(func_get_args(), 1);
        }
        $rows = $this->_simpleSelect($this->_myMeta['PK'], $select);
        if (count($rows) === 0) {
            return false;
        }
        $this->populate($rows[0]);
        return true;
    }

    /**
     * Populate this object using an arbitrary key.
     * 
     * Use this method to populate this object using a UNIQUE key
     * (rather than PRIMARY key).  You must pass one or more arguments.
     * Each argument must be the name of a property that is part of the
     * key. If you do not specify a UNIQUE key, this object will be
     * populated with the first record found to have that key.
     * 
     * @return bool TRUE if a record was found, otherwise FALSE.
     */
    public function loadUsingKey ()
    {
        $rows = $this->_simpleSelect(func_get_args());
        if (count($rows) === 0) {
            return false;
        }
        $this->populate($rows[0]);
        return true;
    }

    /**
     * Return an array of DAO instances using a raw WHERE clause.
     * 
     * @param string $where A WHERE clause
     * @param string $orderBy An ORDER BY clause
     * @param mixed $bind The bind variable(s)
     * 
     * @return array An array of DAO instances
     */
    public function loadMultiple1 ($where = null, $orderBy = null, $maxRecords = null, $offset = null, $bind = array())
    {
        $sql = 'SELECT * FROM ' . $this->_myMeta['FROM'];
        if ($where !== null) {
            $sql .= ' WHERE ' . $where;
        }
        if ($orderBy !== null) {
            $sql .= ' ORDER BY ' . $orderBy;
        }
        if ($maxRecords !== null) {
            $sql .= ' LIMIT ';
            if ($offset !== null) {
                $sql .= $offset . ',' . $maxRecords;
            }
            else {
                $sql .= $maxRecords;
            }
        }
        return $this->loadMultiple($sql, $bind);
    }

    /**
     * Return an array of DAO instances using this object as a query template.
     * 
     * This method uses the current instance ($this) as a template to construct a
     * query. For each non-null property a WHERE condition is generated.
     * 
     * @return array An array of DAO instances
     */
    public function loadMultiple2 ($orderBy = null, $maxRecords = null, $offset = null)
    {
        $sql = 'SELECT * FROM ' . $this->_myMeta['FROM'];
        $props = $this->_myMeta['PTC'];
        $where = '';
        $bind = array();
        foreach ($props as $prop => $col) {
            if ($this->$prop !== null) {
                $bind[] = $this->$prop;
                $col = $this->_quoteColumn($col);
                $where .= strlen($where) === 0 ? ' WHERE ' : ' AND ';
                $where .= "$col = ?";
            }
        }
        $sql .= $where;
        if ($orderBy !== null) {
            $sql .= ' ORDER BY ' . $orderBy;
        }
        if ($maxRecords !== null) {
            $sql .= ' LIMIT ';
            if ($offset !== null) {
                $sql .= $offset . ',' . $maxRecords;
            }
            else {
                $sql .= $maxRecords;
            }
        }
        return $this->loadMultiple($sql, $bind);
    }

    /**
     * Return an array of DAO instances using whatever raw SQL you like
     * 
     * This is the most basic of the "loadMultiple" methods. You can pass
     * any SQL you like, even selecting from other tables. From the rows
     * returned by the query only the mappable elements are used to
     * populate an instance of this object's class.
     * 
     * @param string $sql Some arbitrary SQL 
     * @param mixed $bind The bind variable(s)
     * 
     * @return array An array of DAO instances
     */
    public function loadMultiple ($sql, $bind = array())
    {
        if($bind === null) {
            $bind = array();
        }
        self::$_logger->log($sql, Zend_Log::DEBUG);
        self::$_logger->debug('bound values: ' . implode(', ', $bind));
        $rows = $this->_table->getAdapter()->fetchAll($sql, $bind, Zend_Db::FETCH_ASSOC);
        $daos = array();
        $class = new ReflectionClass(get_class($this));
        $populateMethod = $class->getMethod('populate');
        foreach ($rows as $row) {
            $dao = $class->newInstance($this->_table);
            $populateMethod->invoke($dao, $row);
            $daos[] = $dao;
        }
        return $daos;
    }

    /**
     * Load and return the relational parent of this object. You pass an instance of
     * the relational parent, which is then populated and returned.
     * 
     * @param ETI_Zend_Db_DAO $parent An instance of the relational parent of this object.
     * @return ETI_Zend_Db_DAO The relational parent of this object
     */
    public function loadParent (ETI_Zend_Db_DAO $parent)
    {
        $foreignKey = $this->_getForeignKey($parent);
        $primaryKey = array();
        foreach ($foreignKey as $prop) {
            $primaryKey[] = $this->$prop;
        }
        if (!$parent->load($primaryKey)) {
            return false;
        }
        return $parent;
    }

    /**
     * Load and return relational children of the specified type.
     * 
     * @param ETI_Zend_Db_DAO $child An instance of the type of children you want to load.
     * @param string $orderBy A raw ORDER BY clause.
     * @param string $where A raw WHERE clause filtering the children.
     * @param int $maxRecords The maximum number of children you want to retrieve.
     * @param int $offset The offset in the result set from where you want to retrieve children.
     * @param array $bind The bind array.
     * 
     * @return array An array of children of the specified type
     */
    public function loadChildren (ETI_Zend_Db_DAO $child, $orderBy = null, $where = null, $maxRecords = null, $offset = null, $bind = array())
    {
        if ($where === null) {
            $where = self::_setForeignKeyColumns($this, $child, $bind);
        }
        else {
            $where .= ' AND ' . self::_setForeignKeyColumns($this, $child, $bind);
        }
        return $child->loadMultiple1($where, $orderBy, $maxRecords, $offset, $bind);
    }

    /**
     * Loads and returns the records connected via an intersection table to this instance.
     * 
     * @param $intersection An instance of the DAO representing the intersection table.
     * @param $requested An instance of the requested type of DAO.
     * @param string $orderBy An ORDER BY clause for the table at the other side of the many-to-many relationship.
     * 
     * @return array An array of DAO instances at the other side of the many-to-many relationship.
     */
    public function loadManyToMany (ETI_Zend_Db_DAO $intersection, ETI_Zend_Db_DAO $requested, $orderBy = null)
    {
        $bind = array();
        $sql = array();
        $sql[] = 'SELECT a.* FROM ' . $requested->_myMeta['FROM'] . ' AS a';
        $sql[] = self::_joinChild($requested, $intersection, 'a', 'b');
        $sql[] = ' WHERE ' . self::_setForeignKeyColumns($this, $intersection, $bind, 'b');
        if ($orderBy !== null) {
            $sql[] = ' ORDER BY ' . $orderBy;
        }
        return $requested->loadMultiple(implode(' ', $sql), $bind);
    }

    /**
     * Returns a record count using a raw WHERE clause
     * 
     * This method uses the current instance ($this) as a template to construct a
     * query. For each non-null property a WHERE condition is generated.
     * 
     * @return int The record count
     */
    public function count1 ($where = null, $bind = array())
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->_myMeta['FROM'];
        if ($where !== null) {
            $sql .= ' WHERE ' . $where;
        }
        return (int) $this->getDbAdapter()->fetchOne($sql, $bind);
    }

    /**
     * Returns a record count using this object as a query template.
     * 
     * This method uses the current instance ($this) as a template to construct a
     * query. For each non-null property a WHERE condition is generated.
     * 
     * @return int The record count
     */
    public function count2 ()
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->_myMeta['FROM'];
        $where = '';
        $props = $this->_myMeta['PTC'];
        $bind = array();
        foreach ($props as $prop => $col) {
            if ($this->$prop !== null) {
                $bind[] = $this->$prop;
                $col = $this->_quoteColumn($col);
                $where .= strlen($where) === 0 ? ' WHERE ' : ' AND ';
                $where .= "$col = ?";
            }
        }
        $sql .= $where;
        return (int) $this->getDbAdapter()->fetchOne($sql, $bind);
    }

    /**
     * Counts the number of relational children of this object
     * 
     * @param ETI_Zend_Db_DAO $child An instance of the type of children you want to count
     * @param string $where A raw WHERE clause filtering the children
     * @param unknown_type $bind The bind array
     * 
     * @return int The child count
     */
    public function countChildren (ETI_Zend_Db_DAO $child, $where = null, $bind = array())
    {
        if ($where === null) {
            $where = self::_setForeignKeyColumns($this, $child, $bind);
        }
        else {
            $where .= ' AND ' . self::_setForeignKeyColumns($this, $child, $bind);
        }
        return $child->count1($where, $bind);
    }

    /**
     * Save this object to the database.
     * 
     * On success, the primary key property will be set.
     * 
     */
    public function save ()
    {
        $this->_table->insert($this->data(true));
        $sequence = $this->_table->info(Zend_Db_Table_Abstract::SEQUENCE);
        if ($sequence !== false) {
            $pkProps = $this->_myMeta['PK'];
            $prop = $pkProps[0];
            if (!isset($this->$prop)) {
                if (is_string($sequence)) {
                    $this->$prop = $this->_table->getAdapter()->lastSequenceId($sequence);
                }
                else {
                    $this->$prop = $this->_table->getAdapter()->lastInsertId();
                }
            }
        }
    }

    /**
     * Save this object to the database.
     */
    public function update ()
    {
        $where = $this->_where($this->_myMeta['PK'], false);
        $this->_table->update($this->data(true), $where);
    }

    /**
     * Save a single property of this object to the database.
     * 
     * @param string $property The name of the property
     * @param mixed $value The value that the property will
     * be set to. If omitted, the current value of
     * the property will be used.
     */
    public function updateProperty ($property, $value = null)
    {
        $map = $this->_myMeta['PTC'];
        if (func_num_args() > 1) {
            $this->$property = $value;
        }
        $data = array(
            $this->_column($property) => $this->$property
        );
        $where = $this->_where($this->_myMeta['PK'], false);
        $this->_table->update($data, $where);
    }

    /**
     * Save one or more properties of this object to the database.
     * 
     * You must pass one or more arguments; each argument is
     * assumed to be the name of a property.
     */
    public function updateProperties ()
    {
        $map = $this->_myMeta['PTC'];
        $properties = func_get_args();
        $data = array();
        foreach ($properties as $property) {
            $data[$this->_column($property)] = $this->$property;
        }
        $where = $this->_where($this->_myMeta['PK'], false);
        $this->_table->update($data, $where);
    }

    /**
     * Save this object to the database.
     * 
     * If any of the properties constituting the primary key is null,
     * save() is called, otherwise update().
     * 
     * @return bool TRUE when save() was called, FALSE when update() was called.
     */
    public function saveOrUpdate ()
    {
        $pkProps = $this->_myMeta['PK'];
        foreach ($pkProps as $prop) {
            $value = $this->$prop;
            if (empty($value)) {
                $this->save();
                return true;
            }
        }
        $this->update();
        return false;
    }

    /**
     * Increment the value of a column.
     * 
     * The update will be done directly on the table. The SQL
     * generated looks like this:
     * <pre>
     * UPDATE TBL SET COL=COL+1 WHERE PRIMARY=<value-of-pk-property>
     * </pre>
     * So this method is faster than
     * <pre>
     * $employee->loadProperty('age');
     * $employee->setAge($employee->getAge() + 1);
     * $employee->updateProperty('age');
     * </pre>
     * 
     * The property itself is ignored and will be out-of-sync
     * with the database after a call to this method. The
     * properties representing the primary key must be set.
     * 
     * @param string $property The property corresponding to the
     * column you want to update.
     * @param mixed $delta The amount by which to increment the
     * column.
     */
    public function increment ($property, $delta = 1)
    {
        $col = $this->_quoteColumn($this->_column($property));
        $where = $this->_where($this->_myMeta['PK']);
        $sql = "UPDATE " . $this->_myMeta['FROM'] . " SET $col = $col + $delta WHERE $where";
        $stmt = $this->_table->getAdapter()->query($sql, $this->getPrimaryKey());
        $result = $stmt->rowCount();
        return $result;
    }

    /**
     * Delete this object from the database.
     * 
     * @param mixed $id The primary of the record to be deleted.
     * If omitted, the value of the property
     * corresponding to the primary key will be used.
     *
     * @return bool TRUE if a record was deleted, FALSE otherwise.
     */
    public function delete ($id = null)
    {
        if ($id !== null) {
            $this->setPrimaryKey($id);
        }
        $where = $this->_where($this->_myMeta['PK'], false);
        return $this->_table->delete($where);
    }

    public function deleteChildren (ETI_Zend_Db_DAO $child, $where = null, $bind = array())
    {
        $sql = array();
        $sql[] = 'DELETE FROM ' . $child->_myMeta['FROM'] . ' WHERE ';
        // NB we must first append the user-defined WHERE clause before appending
        // the WHERE clause that sets the childs foreign key columns to the value
        // of this instance's primary key. The user-defined WHERE clause may have
        // bind variables and _setForeignKeyColumns() appends (rather than prepends)
        // its own parameter bindings to the bind array.
        if ($where !==
         null) {
            $sql[] = $where . ' AND ';
        }
        $sql[] = self::_setForeignKeyColumns($this, $child, $bind);
        $result = $this->getDbAdapter()->query(implode(' ', $sql), $bind);
        return $result->rowCount();
    }

    /**
     * Delete this object from the database using an arbitrary key.
     * 
     * You must pass one or more arguments; each argument
     * is assumed to be the name of a property that is part
     * of the key. You should specify a UNIQUE key, otherwise
     * multiple records will be deleted, but this method will
     * not prevent you from specifying some non-unique
     * combination of properties.
     * 
     * @return int The number of records that were
     */
    public function deleteByKey ()
    {
        $where = $this->_where(func_get_args(), false);
        return $this->_table->delete($where);
    }

    /**
     * Get the persistent data of this instance. So only properties that
     * are mapped to columns are returned. The data may be returned as an
     * associative array or as an object and the keys of the array c.q.
     * the properties of the object may be either property names or column
     * names
     * 
     * @param bool $useColumnNames Use column names as keys rather than property names
     * @param bool $asObject Return an instance of stdClass rather than an associative array.
     */
    public function data ($useColumnNames = false, $asObject = false)
    {
        $data = null;
        $data = array();
        $map = $this->_myMeta['PTC'];
        if ($asObject) {
            $data = new stdClass();
            if ($useColumnNames) {
                foreach ($map as $property => $column) {
                    $data->$column = $this->$property;
                }
            }
            else {
                foreach ($map as $property => $column) {
                    $data->$property = $this->$property;
                }
            }
        }
        else {
            $data = array();
            if ($useColumnNames) {
                foreach ($map as $property => $column) {
                    $data[$column] = $this->$property;
                }
            }
            else {
                foreach ($map as $property => $column) {
                    $data[$property] = $this->$property;
                }
            }
        }
        return $data;
    }

    /**
     * Get the current state of the object as an associative array. Both persisten
     * and non-persisten properties are returned.
     * 
     * @param bool $asObject Return an instance of stdClass rather than an associative array.
     */
    public function state ($asObject = false)
    {
        $data = null;
        $properties = $this->_getPublicProperties();
        if ($asObject) {
            $data = new stdClass();
            foreach ($properties as $p) {
                $data->$p = $this->$p;
            }
        }
        else {
            $data = array();
            foreach ($properties as $p) {
                $data[$p] = $this->$p;
            }
        }
        return $data;
    }

    /**
     * Set the persistent properties of this object.
     * 
     * You must pass an associative array. Each key of the array
     * must correspond to a COLUMN name (other keys will be ignored).
     * 
     * @param array $row An associative array
     */
    public function populate ($row)
    {
        $map = $this->_myMeta['PTC'];
        foreach ($map as $property => $column) {
            if (array_key_exists($column, $row)) {
                $this->$property = $row[$column];
            }
        }
    }

    /**
     * Set the persistent properties of this object.
     * 
     * You must pass an associative array. Each key of the array
     * must correspond to a PROPERTY name (other keys will be ignored).
     * You could use this method to initialize this object from the
     * $_POST or $_GET array.<br/><br/>
     * 
     * By default this method treats empty strings as null values
     * (since the $_POST/$_GET array will never contain null values,
     * only empty strings).<br/><br/>
     * 
     * You can optionally pass the name of a function to be applied to
     * the values (e.g. 'strip_tags', or 'trim'). You can then also
     * specify extra arguments for the function using the $args
     * parameter.
     * 
     * @param array $row An associative array whose keys correspond to this object's properties.
     * @param bool $emptyIsNull Whether to treat empty strings as null.
     * @param string $function A function to be applied to the values in the array.
     * @param array|mixed $args Extra (2nd, 3rd, 4th, etc.) arguments to the function.
     */
    public function initialize ($row, $emptyIsNull = true, $function = null, $args = null)
    {
        $map = $this->_myMeta['PTC'];
        foreach ($map as $property => $column) {
            if (array_key_exists($property, $row)) {
                $value = $row[$property];
                if ($function === null) {
                    $this->$property = strlen($value) === 0 ? null : $value;
                }
                else {
                    if ($args === null) {
                        $this->$property = call_user_func($function, $value);
                    }
                    else {
                        if (!is_array($args)) {
                            $args = (array) $args;
                        }
                        array_unshift($args, $value);
                        call_user_func_array($function, $args);
                    }
                }
            }
        }
    }

    /**
     * Get the value of the property representing the primary key.
     * 
     * If the primary key consists of multiple columns, an array is returned,
     * else a single value.
     * 
     * @return mixed The value of the property representing the primary key.
     * 
     */
    public function getPrimaryKey ()
    {
        $props = $this->_myMeta['PK'];
        if (count($props) > 1) {
            $values = array();
            foreach ($props as $prop) {
                $values[] = $this->$prop;
            }
            return $values;
        }
        else {
            $prop = $props[0];
            return $this->$prop;
        }
    }

    /**
     * Set the value of the property representing the primary key.
     * 
     * If the primary key consists of multiple columns, pass an array.
     * 
     * @param mixed $value
     */
    public function setPrimaryKey ($value)
    {
        if (!is_array($value)) {
            $value = (array) $value;
        }
        $props = $this->_myMeta['PK'];
        if (count($props) !== count($value)) {
            throw new ETI_Zend_Db_DAOException('Number of values must match number of columns in primary key');
        }
        foreach ($props as $i => $prop) {
            $this->$prop = $value[$i];
        }
    }

    /**
     * Get the name of the property representing the primary key.
     *
     * If the primary key consists of multiple columns, an array is returned.
     */
    public function getPrimaryKeyProperty ()
    {
        $props = $this->_myMeta['PK'];
        return count($props) === 1 ? $props[0] : $props;
    }

    public function __set ($property, $value)
    {
        if (($method = $this->getSetter($property)) !== false) {
            $method->invoke($this, $value);
        }
        else {
            $myClass = new ReflectionClass($this);
            if (!$myClass->hasProperty($property)) {
                throw new ETI_Zend_Db_DAOException('No such property: ' . $property);
            }
            $rp = $myClass->getProperty($property);
            if (!$rp->isPublic()) {
                throw new ETI_Zend_Db_DAOException('Cannot access property: ' . $property);
            }
            $rp->setValue($this, $value);
        }
    }

    public function __get ($property)
    {
        if (($method = $this->getGetter($property)) !== false) {
            return $method->invoke($this);
        }
        $myClass = new ReflectionClass($this);
        if (!$myClass->hasProperty($property)) {
            throw new ETI_Zend_Db_DAOException('No such property: ' . $property);
        }
        $rp = $myClass->getProperty($property);
        if (!$rp->isPublic()) {
            throw new ETI_Zend_Db_DAOException('Cannot access property: ' . $property);
        }
        return $rp->getValue($this);
    }

    /**
     * Returns the Zend_Db_Table_Abstract instance
     * 
     * @return Zend_Db_Table_Abstract
     */
    public function getTable ()
    {
        return $this->_table;
    }

    /**
     * Returns the adapter used to interact with the database
     * 
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbAdapter ()
    {
        return $this->_table->getAdapter();
    }

    /**
     * 
     * This method is called if you do not pass a Zend_Db_Table_Abstract
     * instance to the constructor. It gives you a hook for automatically
     * inferring the name of your Zend_Db_Table_Abstract subclass from the
     * name of your DAO subclass
     * 
     * @return string|ReflectionClass 
     */
    protected function _getTableClass ()
    {
        return null;
    }

    /**
     * Generate a property-to-column map.
     * 
     * If you have a completely inconsistent property-to-column mapping,
     * override this method. Otherwise override @link _mapPropertyToColumn
     * and/or @link _getMappableProperties.
     * 
     * @return array An associative array with property names as keys and
     * column names as values.
     */
    protected function _mapPropertiesToColumns ()
    {
        $map = array();
        $properties = $this->_getMappableProperties();
        $columns = $this->_table->info(Zend_Db_Table_Abstract::COLS);
        foreach ($properties as $property) {
            $column = $this->_mapPropertyToColumn($property);
            if ($column !== null && in_array($column, $columns)) {
                $map[$property] = $column;
            }
        }
        return $map;
    }

    /**
     * Determine how to translate a property name to a column name.
     * 
     * The default behaviour is to assume the column name is the same as
     * the property name. Override if required. If this method remains null
     * the property will not be mapped to a column.
     * 
     * @param string $property
     * 
     * @return string the name of the column that the property is supposed to map to. 
     */
    protected function _mapPropertyToColumn ($property)
    {
        return $property;
    }

    /**
     * Determine which properties are allowed to be mapped to columns.
     * 
     * By default all public properties and getters are mappable to
     * columns. Override if required. For example, you could decide
     * that properties whose name start with two underscores must never
     * be mapped to a column.
     * 
     * @return array An array of mappable properties 
     */
    protected function _getMappableProperties ()
    {
        return $this->_getPublicProperties();
    }

    /**
     * Determine which properties function as the foreign key to some other subtype of ETI_Zend_Db_DAO.
     * 
     * The default behaviour is that you can construct the name of the foreign
     * key propery by appending 'Id' to the simple name of the parent's class and
     * then camelcase the result. e.g. Some_Prefix_To_Employee -> employeeId. If
     * you override this method, you must return an array containing the properties
     * that constitute the foreign key.
     * 
     * @param $forParent The DAO that represents the relational parent of this instance
     * @return array the names of the properties constituting the primary key.
     */
    protected function _getForeignKey (ETI_Zend_Db_DAO $forParent)
    {
        $name = get_class($forParent);
        if (($i = strrpos($name, '_')) !== false) {
            $name = substr($name, ($i + 1));
        }
        $name[0] = strtolower($name[0]);
        return (array) ($name . 'Id');
    }

    /**
     * Create a WHERE clause for $child in which each foreign key column is
     * compared to the corresponding primary column in $parent.
     * 
     * @param $parent
     * @param $child
     * @param $bind
     * @param $childAlias
     */
    private static function _setForeignKeyColumns (ETI_Zend_Db_DAO $parent, ETI_Zend_Db_DAO $child, array &$bind = array(), $childAlias = null)
    {
        $foreignKey = $child->_getForeignKey($parent);
        $pk = (array) $parent->getPrimaryKey();
        $conditions = '';
        foreach ($foreignKey as $i => $prop) {
            if ($i !== 0) {
                $conditions .= ' AND ';
            }
            $column = $child->_column($prop);
            if ($childAlias !== null) {
                $conditions .= $childAlias . '.';
            }
            $conditions = $child->_quoteColumn($column) . ' = ?';
            $bind[] = $pk[$i];
        }
        return $conditions;
    }

    private static function _joinChild (ETI_Zend_Db_DAO $parent, ETI_Zend_Db_DAO $child, $parentAlias, $childAlias)
    {
        $foreignKey = $child->_getForeignKey($parent);
        $sql = array();
        $sql[] = 'JOIN ' . $child->_myMeta['FROM'] . ' AS ' . $childAlias . ' ON(';
        $pkCols = $parent->_getPrimaryKeyColumns();
        foreach ($foreignKey as $i => $prop) {
            $pkCol = $parent->_quoteColumn($pkCols[$i]);
            $fkCol = $child->_quoteColumn($child->_column($prop));
            $sql[] = "$parentAlias.$pkCol = $childAlias.$fkCol";
        }
        $sql[] = ')';
        return implode(' ', $sql);
    }

    private function _setupMetadata ($metaKey)
    {
        if (self::$_logger === null) {
            self::$_logger = new Zend_Log(new Zend_Log_Writer_Firebug());
        }
        $this->_myMeta = array();
        $this->_myMeta['SCHEMA'] = $this->_schema();
        $this->_myMeta['FROM'] = $this->_from();
        // NB. The property-to-column map MUST be generated before
        // figuring out what the primary key properties are!
        $this->_myMeta['PTC'] = $this->_mapPropertiesToColumns();
        $this->_myMeta['PK'] = $this->_pk();
        $this->_mapPropertiesToColumns();
        self::$_meta[$metaKey] = $this->_myMeta;
    }

    private function _quoteColumn ($name)
    {
        return $this->_table->getAdapter()->quoteIdentifier($name);
    }

    /*
	 * This method generates a basic SQL query. For each of the
	 * properties specified in the 1st argument a WHERE clause is
	 * generated where the column corresponding to the property
	 * must equal the value of the property. You can also specify
	 * the properties (NOT column names) you want in the SELECT
	 * clause, either in a single array, or as extra arguments to
	 * this method.
	 */
    private function _simpleSelect ($properties, $select = null)
    {
        $sql = 'SELECT ';
        if ($select === null) {
            $select = array(
                '*'
            );
        }
        else {
            if (!is_array($select)) {
                $select = array_slice(func_get_args(), 1);
            }
            foreach ($select as $i => $property) {
                $x = $property[0];
                $y = $property[strlen($property) - 1];
                if (($x == '"' && $y == '"') || ($x == "'" && $y == "'")) {
                    $literal = substr($property, 1, $y - 1);
                    $quoted = $this->getDbAdapter()->quote($literal);
                }
                else {
                    $column = $this->_column($property);
                    $quoted = $this->_quoteColumn($column);
                }
                $select[$i] = $quoted;
            }
        }
        $sql .= implode(',', $select);
        $sql .= ' FROM ' . $this->_myMeta['FROM'] . ' WHERE ';
        $sql .= $this->_where($properties);
        if (!is_array($properties)) {
            $properties = array(
                $properties
            );
        }
        $values = array();
        foreach ($properties as $property) {
            $values[] = $this->$property;
        }
        self::$_logger->debug($sql);
        self::$_logger->debug("bound values: " . implode(', ', $values));
        $this->_table->getAdapter()->setFetchMode(Zend_Db::FETCH_ASSOC);
        return $this->_table->getAdapter()->fetchAll($sql, $values);
    }

    /**
     * Generate a WHERE clause for the specified properties.
     * 
     * The WHERE clause will be in the form:
     * prop1_name = 'prop1_value' AND 'prop2_name = 'prop2_value' (etc.)
     * 
     * @param $properties array An array of properties for which to create a WHERE clause
     * @param $prepareOnly Whether to use bind parameters (?) or actual property values.
     */
    private function _where ($properties, $prepareOnly = true)
    {
        if (!is_array($properties)) {
            $properties = array(
                $properties
            );
        }
        $where = '';
        foreach ($properties as $i => $property) {
            if ($i > 0) {
                $where .= ' AND ';
            }
            $column = $this->_column($property);
            $condition = $this->_quoteColumn($column) . ' = ?';
            if ($prepareOnly) {
                $where .= $condition;
            }
            else {
                $where .= $this->_table->getAdapter()->quoteInto($condition, $this->$property);
            }
        }
        return $where;
    }

    private function _column ($property)
    {
        if (!array_key_exists($property, $this->_myMeta['PTC'])) {
            throw new ETI_Zend_Db_DAOException('Property "' . $property . '" does not exist in ' . get_class($this) . ' or is not mapped to a column');
        }
        return $this->_myMeta['PTC'][$property];
    }

    private function getGetter ($property)
    {
        $myClass = new ReflectionClass($this);
        $methodName = 'get' . ucfirst($property);
        if ($myClass->hasMethod($methodName)) {
            $method = new ReflectionMethod($this, $methodName);
            if ($method->isPublic() && count($method->getParameters()) === 0) {
                return $method;
            }
        }
        $methodName = 'is' . ucfirst($property);
        if ($myClass->hasMethod($methodName)) {
            $method = new ReflectionMethod($this, $methodName);
            if ($method->isPublic() && count($method->getParameters()) === 0) {
                return $method;
            }
        }
        return false;
    }

    private function getSetter ($property)
    {
        $myClass = new ReflectionClass($this);
        $methodName = 'set' . ucfirst($property);
        if ($myClass->hasMethod($methodName)) {
            $method = new ReflectionMethod($this, $methodName);
            if ($method->isPublic() && count($method->getParameters()) === 1) {
                return $method;
            }
        }
        return false;
    }

    private function _getPublicProperties ()
    {
        if ($this->_myProperties !== null) {
            return $this->_myProperties;
        }
        $myClass = new ReflectionClass(get_class($this));
        $this->_myProperties = array();
        $methods = $myClass->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if ($method->getDeclaringClass() != $myClass) {
                continue;
            }
            if (count($method->getParameters()) !== 0) {
                continue;
            }
            $name = $method->getName();
            if (substr($name, 0, 3) == 'get' && ctype_upper($name[3])) {
                $this->_myProperties[] = strtolower($name[3]) . substr($name, 4);
            }
        }
        $fields = $myClass->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($fields as $field) {
            if ($field->getDeclaringClass() != $myClass) {
                continue;
            }
            if (in_array($field->getName(), $this->_myProperties)) {
                continue;
            }
            $this->_myProperties[] = $field->getName();
        }
        return $this->_myProperties;
    }

    private function _schema ()
    {
        $schema = $this->_table->info(Zend_Db_Table_Abstract::SCHEMA);
        if (empty($schema)) {
            $config = $this->_table->getAdapter()->getConfig();
            return $config['dbname'];
        }
        return $schema;
    }

    private function _from ()
    {
        $tbl = $this->_table->info(Zend_Db_Table_Abstract::NAME);
        $tblQuoted = $this->_table->getAdapter()->quoteTableAs($tbl, null, true);
        $db = $this->_myMeta['SCHEMA'];
        $dbQuoted = $this->_table->getAdapter()->quoteIdentifier($db);
        return $dbQuoted . '.' . $tblQuoted;
    }

    private function _pk ()
    {
        $myProperties = $this->_myMeta['PTC'];
        return array_keys(array_intersect($myProperties, $this->_getPrimaryKeyColumns()));
    }

    private function _getPrimaryKeyColumns ()
    {
        return array_values((array) $this->_table->info(Zend_Db_Table_Abstract::PRIMARY));
    }

}