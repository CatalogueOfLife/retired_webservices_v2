<?php
/**
 * An adapter for the Zend_Paginator component that facilitates paginating
 * using the ETI_Zend_Db persistency layer.
 * 
 * @author ayco
 *
 */
class ETI_Zend_Paginator_DAOAdapter implements Zend_Paginator_Adapter_Interface
{
    /**
     * @var ETI_Zend_Db_DAO
     */
    private $_dao;
    /**
     * @var ETI_Zend_Db_DAO
     */
    private $_childDao;
    /**
     * @var ETI_Zend_Db_DAO
     */
    private $_manyToManyDao;
    private $_where;
    private $_orderBy;
    private $_bind;
    private $_methodName  = 'loadMultiple1';
    private $_itemCount   = -1;

    /**
     * @param ETI_Zend_Db_DAO $dao The main DAO object that will produce the
     * paginator's "items" by executing a user-defined method (e.g. loadMultiple1,
     * loadMultiple2, loadChildren, loadManyToMany).
     */
    public function __construct (ETI_Zend_Db_DAO $dao)
    {
        $this->_dao = $dao;
    }

    /**
     * Set the total number of rows returned by the query (without the LIMIT clause).
     * 
     * If you set this to a non-negative number, the count() method (called by
     * Zend_Paginator to determine the number of pages) simply returns this number
     * in stead of issueing a SELECT COUNT(*) query. This allows you to cache the
     * total number of rows (e.g. in the session or a hidden input field).
     * 
     * @param $i
     */
    public function setItemCount ($i)
    {
        $this->_itemCount = $i;
        return $this;
    }

    public function setWhere ($where)
    {
        $this->_where = $where;
        return $this;
    }

    public function setOrderBy ($orderBy)
    {
        $this->_orderBy = $orderBy;
        return $this;
    }

    public function setBind ($bind)
    {
        $this->_bind = $bind;
        return $this;
    }

    public function setMethodName ($name)
    {
        $this->_methodName = $name;
        return $this;
    }

    /**
     * Set an instance of the type of DAO you want to load using the main dao's
     * loadChildren method. A side effect of calling this method is that the
     * method called on the main dao will be 'loadChildren'.
     * 
     * @param ETI_Zend_Db_DAO $dao An instance of the child dao.
     */
    public function setChildDAO (ETI_Zend_Db_DAO $dao)
    {
        $this->_childDao = $dao;
        $this->_methodName = 'loadChildren';
        return $this;
    }

    /**
     * Set an instance of the type of DAO you want to load using the main dao's
     * loadManyToMany method. A side effect of calling this method is that the
     * method called on the main dao will be 'loadManyToMany'. If you want to
     * the main dao's loadManyToMany method to be invoked, you must also call
     * the setChildDAO method, to let the main dao know what the intersection
     * table is. You should first call setChildDAO and then setManyToManyDAO;
     * otherwise you have to explicitly call setMethodName to force the
     * adapter to invoke the setManyToManyDAO method on the main dao.
     * 
     * @param ETI_Zend_Db_DAO $dao An instance of the dao at the other side of the many-to-many
     * relationship.
     */
    public function setManyToManyDAO (ETI_Zend_Db_DAO $dao)
    {
        $this->_manyToManyDao = $dao;
        $this->_methodName = 'loadManyToMany';
        return $this;
    }

    public function getItems ($offset, $itemCountPerPage)
    {
        switch ($this->_methodName) {
            case 'loadMultiple1':
                return $this->_dao->loadMultiple1($this->_where, $this->_orderBy, $itemCountPerPage, $offset, $this->_bind);
            case 'loadMultiple2':
                return $this->_dao->loadMultiple2($this->_orderBy, $itemCountPerPage, $offset);
            case 'loadChildren':
                return $this->_dao->loadChildren($this->_childDao, $this->_orderBy, $this->_where, $itemCountPerPage, $offset, $this->_bind);
            case 'loadManyToMany':
                return $this->_dao->loadManyToMany($this->_childDao, $this->_manyToManyDao, $this->_orderBy);
            default:
                throw new Exception('Invalid method name: ' . $this->_methodName);
        }
    }

    public function count ()
    {
        if ($this->_itemCount >= 0) {
            return $this->_itemCount;
        }
        return $this->_countItems();
    }

    private function _countItems ()
    {
        switch ($this->_methodName) {
            case 'loadMultiple1':
                return $this->_dao->count1($this->_where, $this->_bind);
            case 'loadMultiple2':
                return $this->_dao->count2();
            case 'loadChildren':
            case 'loadManyToMany':
                return $this->_dao->countChildren($this->_childDao, $this->_where, $this->_bind);
            default:
                throw new Exception('Invalid method name: ' . $this->_methodName);
        }
    }

}